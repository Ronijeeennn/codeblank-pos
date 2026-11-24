<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class SaleModel extends Model
{
    protected $table         = 'sales';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['product_id', 'customer_id', 'sold_by', 'quantity', 'total_price'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $dateFormat    = 'datetime';

    /**
     * Sales joined with product, customer (optional) and staff names, newest first.
     * Returns the model so the caller can ->paginate() or ->findAll().
     */
    public function withDetails(): self
    {
        return $this->select('sales.*, products.name AS product_name, customers.full_name AS customer_name, users.full_name AS staff_name, products.image AS product_image, users.avatar AS staff_avatar')
            ->join('products', 'products.id = sales.product_id')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.sold_by')
            ->orderBy('sales.created_at', 'DESC')
            ->orderBy('sales.id', 'DESC');
    }

    /**
     * One checkout = every `sales` row sharing the same created_at, staff member and customer
     * (recordMany() writes them together). These helpers show a checkout as ONE entry with its products inside.
     */
    private function groupedQuery(): self
    {
        return $this->select('created_at, sold_by, customer_id, MIN(id) AS first_id, MAX(id) AS last_id, COUNT(*) AS line_count, SUM(quantity) AS units, SUM(total_price) AS total', false)
            ->groupBy('created_at, sold_by, customer_id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('first_id', 'DESC');
    }

    /** Add each checkout's product lines (and staff/customer names) to the grouped rows. */
    private function attachLines(array $groups): array
    {
        if ($groups === []) {
            return [];
        }

        $lines = $this->withDetails()
            ->where('sales.created_at >=', min(array_column($groups, 'created_at')))
            ->where('sales.created_at <=', max(array_column($groups, 'created_at')))
            ->findAll();

        usort($lines, static fn (array $a, array $b): int => (int) $a['id'] <=> (int) $b['id']);

        $key = static fn (array $r): string => $r['created_at'] . '|' . $r['sold_by'] . '|' . ($r['customer_id'] ?? '');
        $by  = [];

        foreach ($lines as $line) {
            $by[$key($line)][] = $line;
        }

        foreach ($groups as &$group) {
            $group['lines']         = $by[$key($group)] ?? [];
            $group['customer_name'] = $group['lines'][0]['customer_name'] ?? null;
            $group['staff_name']    = $group['lines'][0]['staff_name'] ?? '';
            $group['staff_avatar']  = $group['lines'][0]['staff_avatar'] ?? null;
        }
        unset($group);

        return $groups;
    }

    /** One page of checkouts, newest first. Use $model->pager afterwards for the page links. */
    public function checkoutsPage(int $perPage = 15): array
    {
        return $this->attachLines($this->groupedQuery()->paginate($perPage));
    }

    public function latestCheckouts(int $limit = 5): array
    {
        return $this->attachLines($this->groupedQuery()->findAll($limit));
    }

    public function countCheckouts(?string $since = null): int
    {
        $sql = 'SELECT COUNT(*) AS n FROM (SELECT 1 FROM sales' . ($since !== null ? ' WHERE created_at >= ?' : '')
            . ' GROUP BY created_at, sold_by, customer_id) AS checkouts';

        return (int) $this->db->query($sql, $since !== null ? [$since] : [])->getRow()->n;
    }

    /** Revenue and number of sales since a datetime (dashboard "today"). */
    public function totalsSince(string $since): array
    {
        $row = $this->builder()->select('COALESCE(SUM(total_price), 0) AS total', false)
            ->where('created_at >=', $since)->get()->getRow();

        return ['total' => (float) $row->total, 'count' => $this->countCheckouts($since)];
    }

    /** Revenue per day for the last $days days (oldest first), zero-filled for quiet days. */
    public function dailyTotals(int $days = 7): array
    {
        $since = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $rows  = $this->builder()->select('DATE(created_at) AS day, SUM(total_price) AS total', false)
            ->where('created_at >=', $since)->groupBy('DATE(created_at)')->get()->getResult();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row->day] = (float) $row->total;
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = date('Y-m-d', strtotime("-$i days"));
            $series[] = ['date' => $date, 'total' => $byDay[$date] ?? 0.0];
        }

        return $series;
    }

    /** Best sellers by units sold. */
    public function topProducts(int $limit = 5): array
    {
        return $this->builder()
            ->select('products.id, products.name, products.image, SUM(sales.quantity) AS units, SUM(sales.total_price) AS revenue', false)
            ->join('products', 'products.id = sales.product_id')
            ->groupBy('products.id, products.name, products.image')
            ->orderBy('units', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /** Sum of all sales. Uses the builder directly: Model::first() would add an ORDER BY that MySQL rejects on aggregates. */
    public function revenue(): float
    {
        return (float) ($this->builder()->selectSum('total_price', 'revenue')->get()->getRow()->revenue ?? 0);
    }

    /** True if any sale references this product/customer/staff id (used to guard deletes). */
    public function referencedBy(string $column, int $id): bool
    {
        return $this->builder()->where($column, $id)->countAllResults() > 0;
    }

    /**
     * Record one checkout with several products, reducing stock, in a single transaction.
     * Each product becomes its own row in `sales` (that is how the schema stores a sale), and all rows
     * of one checkout share the same created_at. If ANY product is short on stock, nothing is recorded.
     *
     * Stock is reduced with a conditional UPDATE (... AND stock_quantity >= ?), so two cashiers selling
     * the last units at the same moment can never push stock below zero.
     *
     * @param array<int,int> $items product id => quantity (all quantities >= 1)
     *
     * @return array{error: string}|array{lines: list<array>, total: float}
     */
    public function recordMany(array $items, ?int $customerId, int $staffId): array
    {
        if ($items === []) {
            return ['error' => 'Add at least one product to the sale.'];
        }

        ksort($items); // same lock order for everyone

        $products = model(ProductModel::class);
        $byId     = array_column($products->whereIn('id', array_keys($items))->findAll(), null, 'id');

        // 1) Check everything first so the message can list every problem at once.
        $problems = [];
        foreach ($items as $productId => $quantity) {
            if (! isset($byId[$productId])) {
                return ['error' => 'A product in this sale no longer exists. Reload the page and try again.'];
            }

            $stock = (int) $byId[$productId]['stock_quantity'];
            if ($stock < $quantity) {
                $name       = $byId[$productId]['name'];
                $problems[] = $stock === 0
                    ? "{$name} is out of stock"
                    : "you asked for {$quantity} but only {$stock} of {$name} " . ($stock === 1 ? 'is' : 'are') . ' in stock';
            }
        }

        if ($problems !== []) {
            return ['error' => 'Sale rejected: ' . implode('; ', $problems) . (count($items) > 1 ? '. Nothing was recorded.' : '.')];
        }

        // 2) Record it, all or nothing.
        $now   = date('Y-m-d H:i:s');
        $lines = [];

        $this->db->transBegin();

        try {
            foreach ($items as $productId => $quantity) {
                $product = $byId[$productId];

                $this->db->query(
                    'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?',
                    [$quantity, $productId, $quantity],
                );

                if ($this->db->affectedRows() !== 1) { // someone else sold it between the check and now
                    $this->db->transRollback();

                    return ['error' => "Sale rejected: stock for {$product['name']} just changed. Nothing was recorded, please try again."];
                }

                $total = round((float) $product['price'] * $quantity, 2);

                $this->builder()->insert([
                    'product_id'  => $productId,
                    'customer_id' => $customerId,
                    'sold_by'     => $staffId,
                    'quantity'    => $quantity,
                    'total_price' => $total,
                    'created_at'  => $now,
                ]);

                $lines[] = ['sale_id' => (int) $this->db->insertID(), 'product_id' => $productId, 'product' => $product['name'], 'quantity' => $quantity, 'total' => $total];
            }

            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Sale failed: ' . $e->getMessage());

            return ['error' => 'The sale could not be saved. Stock was not changed.'];
        }

        $fresh = array_column($products->whereIn('id', array_keys($items))->findAll(), null, 'id');

        foreach ($lines as &$line) {
            $line['remaining'] = (int) $fresh[$line['product_id']]['stock_quantity'];
        }
        unset($line);

        return ['lines' => $lines, 'total' => round(array_sum(array_column($lines, 'total')), 2)];
    }

    /**
     * Single-product convenience wrapper around recordMany().
     *
     * @return array{error: string}|array{sale_id: int, total: float, product: string, remaining: int}
     */
    public function record(int $productId, ?int $customerId, int $staffId, int $quantity): array
    {
        $result = $this->recordMany([$productId => $quantity], $customerId, $staffId);

        if (isset($result['error'])) {
            return $result;
        }

        $line = $result['lines'][0];

        return ['sale_id' => $line['sale_id'], 'total' => $line['total'], 'product' => $line['product'], 'remaining' => $line['remaining']];
    }
}

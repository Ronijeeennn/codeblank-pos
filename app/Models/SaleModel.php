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

    /** Revenue and number of sales since a datetime (dashboard "today"). */
    public function totalsSince(string $since): array
    {
        $row = $this->builder()->select('COALESCE(SUM(total_price), 0) AS total, COUNT(*) AS n', false)
            ->where('created_at >=', $since)->get()->getRow();

        return ['total' => (float) $row->total, 'count' => (int) $row->n];
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
     * Record a sale and reduce stock in one transaction.
     *
     * The stock check and the decrement are a single conditional UPDATE, so two cashiers
     * selling the last units at the same moment can never push stock below zero.
     *
     * @return array{error: string}|array{sale_id: int, total: float, product: string, remaining: int}
     */
    public function record(int $productId, ?int $customerId, int $staffId, int $quantity): array
    {
        $products = model(ProductModel::class);
        $product  = $products->find($productId);

        if ($product === null) {
            return ['error' => 'That product no longer exists.'];
        }

        $this->db->transBegin();

        try {
            $this->db->query(
                'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?',
                [$quantity, $productId, $quantity],
            );

            if ($this->db->affectedRows() !== 1) {
                $this->db->transRollback();
                $left = (int) ($products->find($productId)['stock_quantity'] ?? 0);

                return ['error' => $left === 0
                    ? "Sale rejected: {$product['name']} is out of stock."
                    : "Sale rejected: you asked for {$quantity} but only {$left} of {$product['name']} "
                        . ($left === 1 ? 'is' : 'are') . ' in stock.'];
            }

            $total  = round((float) $product['price'] * $quantity, 2);
            $saleId = $this->insert([
                'product_id'  => $productId,
                'customer_id' => $customerId,
                'sold_by'     => $staffId,
                'quantity'    => $quantity,
                'total_price' => $total,
            ]);

            if ($saleId === false) {
                $this->db->transRollback();

                return ['error' => 'The sale could not be saved. Stock was not changed.'];
            }

            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Sale failed: ' . $e->getMessage());

            return ['error' => 'The sale could not be saved. Stock was not changed.'];
        }

        return [
            'sale_id'   => (int) $saleId,
            'total'     => $total,
            'product'   => $product['name'],
            'remaining' => (int) $products->find($productId)['stock_quantity'],
        ];
    }
}

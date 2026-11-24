<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;

class Sales extends BaseController
{
    /** Record Sale page. */
    public function new()
    {
        return view('sales/new', [
            'products'  => model(ProductModel::class)->orderBy('name')->findAll(),
            'customers' => model(CustomerModel::class)->orderBy('full_name')->findAll(),
        ]);
    }

    public function create()
    {
        $rules = [
            'customer_id' => ['rules' => 'permit_empty|is_natural_no_zero|is_not_unique[customers.id]', 'errors' => ['is_not_unique' => 'Choose a customer from the list.']],
        ];

        if (! $this->validate($rules)) {
            return $this->backWithErrors('/sales/new');
        }

        // The cart arrives as qty[productId] = quantity. Zero or blank means "not in the cart".
        $items = [];
        $raw   = $this->request->getPost('qty');

        foreach (is_array($raw) ? $raw : [] as $productId => $quantity) {
            if (! is_string($quantity) || ! ctype_digit((string) $productId)) {
                return $this->failSale('The cart has an invalid entry. Reload the page and try again.');
            }

            $quantity = trim($quantity);

            if ($quantity === '' || $quantity === '0') {
                continue;
            }

            if (! ctype_digit($quantity) || (int) $quantity > 100000) {
                return $this->failSale('Quantities must be whole numbers of 1 or more.');
            }

            $items[(int) $productId] = (int) $quantity;
        }

        if ($items === []) {
            return $this->failSale('Add at least one product to the sale.');
        }

        if (count($items) > 50) {
            return $this->failSale('A sale can have at most 50 different products.');
        }

        $customerId = $this->request->getPost('customer_id');
        $result     = model(SaleModel::class)->recordMany(
            $items,
            $customerId === '' || $customerId === null ? null : (int) $customerId,
            (int) session()->get('user_id'),
        );

        if (isset($result['error'])) {
            return $this->failSale($result['error']);
        }

        $lines = $result['lines'];

        if (count($lines) === 1) {
            $message = sprintf('Sale #%d recorded: %s total for %s. %d left in stock.', $lines[0]['sale_id'], money($result['total']), $lines[0]['product'], $lines[0]['remaining']);
        } else {
            $parts   = array_map(static fn (array $l): string => $l['quantity'] . ' × ' . $l['product'], $lines);
            $message = sprintf('Sale recorded: %d products, %s total. %s.', count($lines), money($result['total']), implode(', ', $parts));
        }

        return redirect()->to('/sales/new')->with('success', $message);
    }

    /** Back to the form with the cart still filled in and a clear message. */
    private function failSale(string $message)
    {
        return $this->backWithErrors('/sales/new')->with('error', $message);
    }

    /** Sales History page. */
    public function index()
    {
        $sales   = model(SaleModel::class);
        $revenue = $sales->revenue();
        $count   = $sales->countCheckouts();

        return view('sales/index', [
            'checkouts' => $sales->checkoutsPage(15),
            'pager'   => $sales->pager,
            'revenue' => $revenue,
            'count'   => $count,
            'average' => $count > 0 ? $revenue / $count : 0,
        ]);
    }
}

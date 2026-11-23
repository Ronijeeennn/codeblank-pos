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
            'product_id'  => ['rules' => 'required|is_natural_no_zero|is_not_unique[products.id]', 'errors' => ['required' => 'Choose a product.', 'is_not_unique' => 'Choose a product from the list.']],
            'customer_id' => ['rules' => 'permit_empty|is_natural_no_zero|is_not_unique[customers.id]', 'errors' => ['is_not_unique' => 'Choose a customer from the list.']],
            'quantity'    => ['rules' => 'required|is_natural_no_zero|less_than_equal_to[100000]', 'errors' => ['required' => 'Enter a quantity.', 'is_natural_no_zero' => 'Quantity must be a whole number of 1 or more.']],
        ];

        if (! $this->validate($rules)) {
            return $this->backWithErrors('/sales/new');
        }

        $customerId = $this->request->getPost('customer_id');
        $result     = model(SaleModel::class)->record(
            (int) $this->request->getPost('product_id'),
            $customerId === '' || $customerId === null ? null : (int) $customerId,
            (int) session()->get('user_id'),
            (int) $this->request->getPost('quantity'),
        );

        if (isset($result['error'])) {
            return $this->backWithErrors('/sales/new')->with('error', $result['error']);
        }

        return redirect()->to('/sales/new')->with(
            'success',
            sprintf('Sale #%d recorded: %s total for %s. %d left in stock.', $result['sale_id'], money($result['total']), $result['product'], $result['remaining']),
        );
    }

    /** Sales History page. */
    public function index()
    {
        $sales   = model(SaleModel::class);
        $revenue = $sales->revenue();
        $count   = $sales->countAllResults();

        return view('sales/index', [
            'sales'   => $sales->withDetails()->paginate(15),
            'pager'   => $sales->pager,
            'revenue' => $revenue,
            'count'   => $count,
            'average' => $count > 0 ? $revenue / $count : 0,
        ]);
    }
}

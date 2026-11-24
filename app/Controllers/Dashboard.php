<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $sales = model(SaleModel::class);

        $daily = $sales->dailyTotals(7);

        return view('dashboard/index', [
            'today'         => $sales->totalsSince(date('Y-m-d 00:00:00')),
            'daily'         => $daily,
            'weekTotal'     => array_sum(array_column($daily, 'total')),
            'topProducts'   => $sales->topProducts(5),
            'productCount'  => model(ProductModel::class)->countAllResults(),
            'customerCount' => model(CustomerModel::class)->countAllResults(),
            'staffCount'    => model(UserModel::class)->countAllResults(),
            'salesCount'    => $sales->countCheckouts(),
            'revenue'       => $sales->revenue(),
            'lowStock'      => model(ProductModel::class)->lowStock(),
            'recentSales'   => $sales->latestCheckouts(5),
        ]);
    }
}

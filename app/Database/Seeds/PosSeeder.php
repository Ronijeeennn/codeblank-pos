<?php

namespace App\Database\Seeds;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

/**
 * Demo data. Run with:  php spark db:seed PosSeeder
 * First login:  admin / admin12345   (change this password after logging in)
 */
class PosSeeder extends Seeder
{
    public function run(): void
    {
        $users     = new UserModel();
        $products  = new ProductModel();
        $customers = new CustomerModel();

        if ($users->where('username', 'admin')->first()) {
            echo "Seed data already present, skipping.\n";

            return;
        }

        // Passwords are plain here; UserModel hashes them on insert.
        $users->insert(['username' => 'admin', 'full_name' => 'Store Admin', 'password' => 'admin12345']);
        $users->insert(['username' => 'cashier', 'full_name' => 'Maria Santos', 'password' => 'cashier123']);

        foreach ([
            ['Brewed Coffee (12oz)', 85.00, 40],
            ['Iced Latte', 120.00, 25],
            ['Ensaymada', 65.00, 18],
            ['Chicken Sandwich', 145.50, 12],
            ['Bottled Water 500ml', 25.00, 100],
            ['Chocolate Chip Cookie', 55.00, 3],
            ['Tote Bag', 350.00, 8],
            ['Reusable Tumbler', 499.00, 0],
        ] as [$name, $price, $stock]) {
            $products->insert(['name' => $name, 'price' => $price, 'stock_quantity' => $stock]);
        }

        foreach ([
            ['Juan Dela Cruz', 'juan@example.com', '0917 123 4567'],
            ['Ana Reyes', 'ana.reyes@example.com', '0928 765 4321'],
            ['Carlo Mendoza', 'carlo@example.com', null],
        ] as [$name, $email, $phone]) {
            $customers->insert(['full_name' => $name, 'email' => $email, 'phone' => $phone]);
        }

        // Sales go through the real workflow (so stock and history agree), then get backdated over the past week.
        $sales = new SaleModel();
        foreach ([
            // product, customer, staff, qty, days ago
            [1, 1, 2, 2, 6], [5, null, 2, 4, 6], [2, 2, 1, 1, 5], [3, null, 2, 3, 5], [1, null, 2, 3, 4],
            [4, 3, 1, 1, 4], [5, null, 2, 6, 3], [2, 1, 2, 2, 3], [1, 2, 2, 4, 2], [3, null, 1, 2, 2],
            [7, 3, 1, 1, 2], [1, null, 2, 5, 1], [2, null, 2, 3, 1], [4, 1, 2, 2, 1], [6, 2, 2, 2, 0],
            [1, 1, 1, 2, 0], [5, null, 2, 3, 0],
        ] as $i => [$product, $customer, $staff, $qty, $daysAgo]) {
            $result = $sales->record($product, $customer, $staff, $qty);

            if ($daysAgo > 0 && isset($result['sale_id'])) {
                $when = strtotime("today -{$daysAgo} days 09:00") + $i * 1500;
                $this->db->table('sales')->where('id', $result['sale_id'])->update(['created_at' => date('Y-m-d H:i:s', $when)]);
            }
        }

        // One checkout with several products (each product is its own row in `sales`).
        $sales->recordMany([1 => 1, 3 => 2, 5 => 2], 1, 2);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSales extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'auto_increment' => true],
            'product_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => false],
            'customer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'sold_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => false],
            'quantity'    => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'created_at'  => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('sold_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('sales');
    }

    public function down(): void
    {
        $this->forge->dropTable('sales', true);
    }
}

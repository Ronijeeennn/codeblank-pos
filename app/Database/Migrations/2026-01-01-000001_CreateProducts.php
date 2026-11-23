<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'stock_quantity' => ['type' => 'INT', 'constraint' => 11, 'null' => false, 'default' => 0],
            'image'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('products');
    }

    public function down(): void
    {
        $this->forge->dropTable('products', true);
    }
}

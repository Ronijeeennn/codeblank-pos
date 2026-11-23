<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'price', 'stock_quantity', 'image'];

    // created_at is filled automatically; the schema has no updated_at column.
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $dateFormat    = 'datetime';

    /** Products running low, for the dashboard. */
    public function lowStock(int $threshold = 5, int $limit = 5): array
    {
        return $this->where('stock_quantity <=', $threshold)->orderBy('stock_quantity')->findAll($limit);
    }
}

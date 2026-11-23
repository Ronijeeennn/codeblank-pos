<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['full_name', 'email', 'phone'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $dateFormat    = 'datetime';

    /**
     * Delete a customer but keep their purchases: past sales become anonymous
     * (sales.customer_id is nullable) so the sales history and revenue stay intact.
     */
    public function deleteKeepingSales(int $id): void
    {
        $this->db->transStart();
        $this->db->table('sales')->where('customer_id', $id)->update(['customer_id' => null]);
        $this->delete($id);
        $this->db->transComplete();
    }
}

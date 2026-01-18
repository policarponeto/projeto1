<?php

namespace App\Models;

use CodeIgniter\Model;

class TransacoesModel extends Model
{
    protected $table            = 'transacoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    public function criarTransacao(array $data): int
    {
        $data['status'] = self::STATUS_PENDING;
        $data['notification_sent'] = false;
        return $this->insert($data);
    }

    public function transferirSaldo($de, $para, $valor)
    {
        $contasModel = new \App\Models\ContasModel();
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();

            // REMOVE SALDO from source account
            $contasModel->where('id', $de)
                       ->set('saldo', 'saldo - ' . $valor, false)
                       ->update();
                       
            // ADD SALDO to destination account
            $contasModel->where('id', $para)
                       ->set('saldo', 'saldo + ' . $valor, false)
                       ->update();

            $db->transComplete();
    
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    public function markAsCompleted(int $idTransacao, ?string $authCode = null): bool
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($authCode) {
            $data['authorization_code'] = $authCode;
        }
        
        return $this->update($idTransacao, $data);
    }
}

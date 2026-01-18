<?php

namespace App\Models;

use CodeIgniter\Model;

class ContasModel extends Model
{
    protected $table            = 'contas';
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

    public function cadastrarConta(int $userId, float $saldo = 0.0): int
    {
        $data = [
            'idUsuario' => $userId,
            'saldo' => $saldo
        ];
        return $this->insert($data);
    }

    public function transfereValor(int $de, int $para, float $valor): bool
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // REMOVE VALOR de
            /// VERIFICA SE O USUARIO TEM SALDO SUFICIENTE
           // $saldo = $this->verificarSaldo($de);
           // if ($saldo < $valor) {
                throw new \Exception('Saldo insuficiente');
           //// }




           // if (!$this->removeValor($de, $valor)) {
           //     throw new \Exception('Erro ao enviar');
           // }
            //ADICIONA VALOR para
           // if (!$this->addFunds($para, $valor)) {
           //     throw new \Exception('Erro ao receber');
           // }
          //  $db->transComplete();    
          //  return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
    public function adicionaValor(int $para, float $valor): bool
    {
        if ($valor <= 0) {
            return false;
        }
        return $this->atualizaSaldo($para, $valor);
    }

    public function removeValor(int $de, float $valor): bool
    {
        if ($valor <= 0) {
            return false;
        }
        if (!$this->hasSufficientBalance($de, $valor)) {
            return false;
        }
        return $this->atualizaSaldo($de, -$valor);
    }

    public function temSaldoSuficiente(int $id, float $valor): bool
    {
        $saldo = $this->verificarSaldo($id);   
        return $saldo >= $valor;
    }


      



}

<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuariosModel extends Model
{
    protected $table            = 'usuarios';
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

    const TYPE_CLIENTE = 'cliente';
    const TYPE_LOJISTA = 'lojista';

    public function cadastrar(array $data): int
    {
        // Check if email already exists
        if ($this->where('email', $data['email'])->first()) {
            throw new \Exception('Email já existe');
        }

        // Check if CPF/CNPJ already exists
        if ($this->where('cpfCnpj', $data['cpfCnpj'])->first()) {
            throw new \Exception('CPF/CNPJ já existe');
        }

        // Hash password
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

        // Set initial balance
        $data['saldo'] = $data['saldo'] ?? 0.0;

        return $this->insert($data);
    }

    public function validarUsuario(array $data): array
    {
        $errors = [];

        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }

        if (empty($data['cpfCnpj'])) {
            $errors['cpfCnpj'] = 'CPF/CNPJ é obrigatorio';
        } 

        if (empty($data['email'])) {
            $errors['email'] = 'Email é obrigatorio';
        } 

        if (empty($data['senha'])) {
            $errors['senha'] = 'Senha é Obrigatória';
        } elseif (strlen($data['senha']) < 6) {
            $errors['senha'] = 'Senha tem que ser maior que 6';
        }

        if (!empty($data['tipoUsuario']) && !in_array($data['tipoUsuario'], [self::TYPE_CLIENTE, self::TYPE_LOJISTA])) {
            $errors['tipoUsuario'] = 'Tipo Usuário Invalido';
        }

        return $errors;
    }


      



}

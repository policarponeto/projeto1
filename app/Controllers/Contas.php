<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\ContasModel;
use CodeIgniter\API\ResponseTrait;

class Contas extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $contas = new ContasModel();   
        $contas = $contas->select('contas.*, usuarios.nome, usuarios.email, usuarios.tipoUsuario')
            ->join('usuarios', 'usuarios.id = contas.idUsuario')
            ->findAll();

        // Remove sensitive information
        foreach ($contas as &$conta) {
            unset($conta['senha']);
        }

        return $this->respond($contas);
    }

    public function getContaUsuario(int $userId)
    {
        $contas = new ContasModel();
        $conta = $contas->select('contas.id, usuarios.id as usuarioId, usuarios.nome, contas.saldo')
            ->join('usuarios', 'usuarios.id = contas.idUsuario')
            ->where('idUsuario', $userId)
            ->first();
        return $this->respond($conta);
    }

    

}

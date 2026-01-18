<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\ContasModel;
use CodeIgniter\API\ResponseTrait;

class Usuarios extends BaseController
{
    use ResponseTrait;
    public function index(){

        $usuarios = new UsuariosModel();   
        $usuarios = $usuarios->select('u.nome, u.cpfCnpj, u.email, u.tipoUsuario, c.saldo')
                              ->from('usuarios u')
                              ->join('contas c', 'u.id = c.idUsuario')
                              ->get()
                              ->getResultArray();
        return $this->response->setJSON($usuarios);
    }

    public function cadastrar()
    {
        $usuarios = new UsuariosModel();
        $contas = new ContasModel();

        try {
            $input = $this->request->getJSON();
            
            if (!$input) {
                return $this->fail('JSON inválido', 400);
            }

            // CAMPOS OBRIGATÓRIOS
            $requiredFields = ['nome', 'cpfCnpj', 'email', 'senha'];
            foreach ($requiredFields as $field) {
                if (!isset($input->$field) || empty($input->$field)) {
                    return $this->fail("Campo '{$field}' é obrigatório", 400);
                }
            }

            $userData = [
                'nome' => $input->nome,
                'cpfCnpj' => $input->cpfCnpj,
                'email' => $input->email,
                'senha' => $input->senha,
                'tipoUsuario' => $input->tipoUsuario ?? UserModel::TYPE_CLIENTE,
                'saldo' => $input->saldo ?? 0.0
            ];

            // VALIDAR
            $validationErrors = $usuarios->validarUsuario($userData);
            if (!empty($validationErrors)) {
                return $this->fail($validationErrors, 400);
            }

            //VERIFICAR SE USUARIO JA EXISTE
            $user = $usuarios->where('cpfCnpj', $userData['cpfCnpj'])->first();
            if ($user) {
                return $this->fail('CPF/CNPJ já existe', 400);
            }
            $user = $usuarios->where('email', $userData['email'])->first();
            if ($user) {
                return $this->fail('E-MAIL já existe', 400);
            }  

            // CADASTRAR
            $userId = $usuarios->cadastrar($userData);

            if (!$userId) {
                return $this->fail('Erro ao Criar Usuário', 500);
            }

            // CADASTRAR CONTA
            $contas->cadastrarConta($userId, $userData['saldo']);

            // Mostrar sem a senha
            $user = $usuarios->find($userId);
            unset($user['senha']);
            unset($user['updated_at']);

            return $this->respondCreated([
                'message' => 'Usuário Criado com Sucesso',
                'user' => $user
            ]);


        } catch (\Exception $e) {
               return $this->fail('Internal server error', 500);
        }
    }

    

}

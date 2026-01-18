<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\ContasModel;
use App\Models\TransacoesModel;
use CodeIgniter\API\ResponseTrait;

use App\Services\AuthorizationService;
use App\Services\NotificationService;

class Transacoes extends BaseController
{
    use ResponseTrait;
    
    public function criar()
    {
        $contas = new ContasModel();
        $transacoes = new TransacoesModel();
        $authorizationService = new AuthorizationService();
        $notificationService = new NotificationService();

        try {
            // Verificar se o método é POST
            if ($this->request->getMethod() !== 'POST') {
                return $this->fail('Método não permitido. Use POST para criar transações.', 405);
            }
            
            $input = $this->request->getJSON();
    
            
            if (!$input) {
                return $this->fail('JSON inválido ou não recebido. Verifique o Content-Type header.', 400);
            }

            $requiredFields = ['valor', 'de', 'para'];
            foreach ($requiredFields as $field) {
                if (!isset($input->$field) || empty($input->$field)) {
                    return $this->fail("Campo '{$field}' é obrigatorio", 400);
                }
            }
            $valor = (float) $input->valor;
            $de = (int) $input->de;
            $para = (int) $input->para;

            $transactionData = [
                'de' => $de,
                'para' => $para,
                'valor' => $valor
            ];

            // VALIDAR TRANSACOES COM AS REGRAS         
            $saldo = $contas->select('*, contas.saldo')
                            ->join('usuarios u', 'u.id = contas.idUsuario')
                            ->where('contas.id', $de)
                            ->first();
            $destino = $contas->where('id', $para)->first();

            //// VERIFICAR SE de EXISTE
            if (!$saldo) {
                return $this->fail('Conta de origem não encontrada', 404);
            }
            //// VERIFICAR SE para EXISTE
            if (!$destino) {
                return $this->fail('Conta de destino não encontrada', 404);
            }
            //// VERIFICAR SE de TEM SALDO
            if ((float)$saldo['saldo'] < $valor) {
                return $this->fail('Saldo insuficiente', 400);
            }
            //// VERIFICAR SE O valor E VALIDO
            if ($valor <= 0) {
                return $this->fail('Valor inválido', 400);
            }
            //// VERIFICAR SE de E para SÃO DIFERENTES
            if ($saldo['id'] === $destino['id']) {
                return $this->fail('Não é possível transferir para a mesma conta.', 400);
            }
            //// VERFICAR SE de E CLIENTE
            if ($saldo['tipoUsuario'] === UsuariosModel::TYPE_LOJISTA) {
                return $this->fail('Os lojistas não podem enviar dinheiro.', 403);
            }

            // INICIA TRANSACAO
            $db = \Config\Database::connect();
            $db->transStart();

            try{
                // CRIAR TRANSACAO
                $idTransacao = $transacoes->criarTransacao($transactionData);
                
                // TRANFERIR ENTRE CONTAS
                $tranferencia = $transacoes->transferirSaldo($de, $para, $valor);
                
                if (!$tranferencia) {
                    throw new \Exception('Falha na transferência');
                }

                $authResult = $authorizationService->authorize();

                if (!$authResult['authorized']) {
                    $db->transRollback();
                    return $this->fail('Transação não autorizada', 403);
                }
                
                // Usar código de autorização da API ou gerar um único
                $authCode = $authResult['authorization_code'] ?? uniqid();
                
                // Marcar transação como completada
                $transacoes->markAsCompleted($idTransacao, $authCode);

                // Completar transação no banco
                $db->transComplete();
                
                // Enviar notificação (opcional, não bloqueia)
                try {
                    $notificationService->sendTransferNotification([
                        'para_email' => $destino['email'] ?? null,
                        'ppara_nome' => $saldo['nome'] ?? 'Usuário',
                        'valor' => $valor
                    ]);
                } catch (\Exception $e) {
                    log_message('warning', 'Notification failed: ' . $e->getMessage());
                }
                return $this->respondCreated([
                    'message' => 'Transferência realizada com sucesso',
                    'idTransacao' => $idTransacao,
                    'valor' => $valor,
                    'de' => $de,
                    'para' => $para
                ]);
                   
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

            


            
        } catch (\Exception $e) {
            log_message('error', 'Transaction error: ' . $e->getMessage());
            return $this->fail('Erro na transação: ' . $e->getMessage(), 500);
        }
    }

    

    

}

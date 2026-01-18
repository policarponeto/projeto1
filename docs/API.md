# API Documentation - Projeto1

## Overview

Sistema de transações financeiras desenvolvido em CodeIgniter 4 para processar transferências entre usuários.

## Base URL

```
http://localhost:8080
```

## Endpoints

### 1. Criar Transação

**POST** `/transacoes/criar`

Cria uma nova transação de transferência entre contas.

#### Request Body

```json
{
    "valor": 100.50,
    "de": 1,
    "para": 2
}
```

#### Parameters

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| valor | float | Sim | Valor da transferência (deve ser maior que 0) |
| de | integer | Sim | ID da conta de origem |
| para | integer | Sim | ID da conta de destino |

#### Response

**Success (201)**
```json
{
    "message": "Transferência realizada com sucesso",
    "idTransacao": 123,
    "valor": 100.50,
    "de": 1,
    "para": 2
}
```

**Error Responses**

- **400 Bad Request** - Dados inválidos
```json
{
    "error": "Campo 'valor' é obrigatorio"
}
```

- **403 Forbidden** - Transação não autorizada
```json
{
    "error": "Os lojistas não podem enviar dinheiro."
}
```

- **404 Not Found** - Conta não encontrada
```json
{
    "error": "Conta de origem não encontrada"
}
```

- **500 Internal Server Error** - Erro no servidor
```json
{
    "error": "Erro na transação: [mensagem detalhada]"
}
```

## Regras de Negócio

### Validações

1. **Saldo Insuficiente**: Conta de origem deve ter saldo >= valor da transferência
2. **Valor Inválido**: Valor deve ser maior que 0
3. **Mesma Conta**: Não é permitido transferir para a mesma conta
4. **Tipo de Usuário**: Lojistas não podem enviar dinheiro
5. **Autorização**: Transação deve ser autorizada por serviço externo

### Fluxo da Transação

1. Receber dados da transação
2. Validar campos obrigatórios
3. Verificar existência das contas
4. Validar regras de negócio
5. Iniciar transação no banco
6. Processar transferência entre contas
7. Autorizar transação externamente
8. Marcar transação como completada
9. Enviar notificação (assíncrono)
10. Commit da transação

## Serviços Externos

### Autorização

- **URL**: `https://util.devi.tools/api/v2/authorize`
- **Método**: GET
- **Timeout**: 2 segundos
- **SSL**: Desabilitado (desenvolvimento)

### Notificação

- **URL**: `https://util.devi.tools/api/v2/notify`
- **Método**: POST
- **Timeout**: 5 segundos
- **SSL**: Desabilitado (desenvolvimento)

## Estrutura do Banco de Dados

### Tabela: usuarios

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | int | ID do usuário |
| nome | varchar | Nome do usuário |
| email | varchar | Email do usuário |
| tipoUsuario | enum | Tipo (cliente/lojista) |

### Tabela: contas

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | int | ID da conta |
| idUsuario | int | ID do usuário |
| saldo | decimal | Saldo da conta |

### Tabela: transacoes

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | int | ID da transação |
| de | int | ID da conta origem |
| para | int | ID da conta destino |
| valor | decimal | Valor transferido |
| status | enum | Status da transação |
| authorization_code | varchar | Código de autorização |

## Configuração

### Ambiente

- **PHP**: 8.4
- **Framework**: CodeIgniter 4
- **Banco**: MySQL 8.0
- **Servidor**: Apache

### Variáveis de Ambiente

```env
CI_ENVIRONMENT=development
```

## Deploy

### Docker

```bash
# Build e start
docker-compose up -d --build

# Verificar logs
docker-compose logs -f app

# Parar
docker-compose down
```

### Acesso

- **Aplicação**: http://localhost:8080
- **Banco Dados**: localhost:3306

## Testes

### Exemplo de Request

```bash
curl -X POST http://localhost:8080/transacoes/criar \
  -H "Content-Type: application/json" \
  -d '{
    "valor": 50.00,
    "de": 1,
    "para": 2
  }'
```

### Cenários de Teste

1. **Transferência válida**: Cliente com saldo suficiente para outro cliente
2. **Saldo insuficiente**: Cliente tenta transferir mais que o saldo
3. **Lojista envia**: Lojista tenta enviar dinheiro (deve falhar)
4. **Mesma conta**: Transferência para a mesma conta (deve falhar)
5. **Valor inválido**: Transferência com valor <= 0 (deve falhar)
6. **Conta inexistente**: Transferência para conta que não existe

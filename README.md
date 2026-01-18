# Projeto1

Sistema de transações financeiras desenvolvido em CodeIgniter 4.

## 📋 Sumário

- [Docker Setup](#docker-setup)
- [Documentação](#documentação)
- [API Endpoints](#api-endpoints)
- [Arquitetura](#arquitetura)
- [Deploy](#deploy)

## 📚 Documentação

### Documentação Completa

- **[API Documentation](./docs/API.md)** - Endpoints, requests/responses, regras de negócio
- **[Architecture Documentation](./docs/ARCHITECTURE.md)** - Arquitetura, padrões, fluxo de dados
- **[Deploy Documentation](./docs/DEPLOY.md)** - Setup, deploy, troubleshooting

## 🐳 Docker Setup

### Pré-requisitos
- Docker
- Docker Compose

### Como executar

1. **Construir e iniciar o container:**
```bash
docker-compose up -d --build
```

2. **Acessar a aplicação:**
- URL: http://localhost:8080

3. **Parar o container:**
```bash
docker-compose down
```

4. **Verificar logs:**
```bash
docker-compose logs -f app
```

### Configurações

- **Porta da aplicação**: 8080
- **Banco de dados**: Externo (configurado no Database.php)

### Desenvolvimento

Para desenvolvimento com hot reload, os arquivos são montados como volume. Alterações nos arquivos PHP serão refletidas automaticamente.

### Comandos úteis

```bash
# Entrar no container da aplicação
docker-compose exec app bash

# Reiniciar o container da aplicação
docker-compose restart app

# Verificar status do container
docker-compose ps

# Limpar tudo (remove container)
docker-compose down
```

## 🚀 API Endpoints

### Criar Transação

**POST** `/transacoes/criar`

```json
{
    "valor": 100.50,
    "de": 1,
    "para": 2
}
```

**Response (201):**
```json
{
    "message": "Transferência realizada com sucesso",
    "idTransacao": 123,
    "valor": 100.50,
    "de": 1,
    "para": 2
}
```

## 🏗️ Arquitetura

- **Framework**: CodeIgniter 4
- **PHP**: 8.4
- **Banco**: MySQL 8.0
- **Servidor**: Apache
- **Padrões**: MVC + Service Layer

### Estrutura

```
app/
├── Controllers/     # API Endpoints
├── Models/          # Business Logic & Data
├── Services/        # External APIs Integration
└── Config/          # Configuration
```

## 🔧 Tecnologias

- **Backend**: PHP 8.4 + CodeIgniter 4
- **Database**: MySQL 8.0
- **Web Server**: Apache
- **External APIs**: 
  - Autorização: `https://util.devi.tools/api/v2/authorize`
  - Notificação: `https://util.devi.tools/api/v2/notify`
- **Containerization**: Docker + Docker Compose

## 📝 Regras de Negócio

1. **Saldo Insuficiente**: Conta origem deve ter saldo >= valor
2. **Tipo Usuário**: Lojistas não podem enviar dinheiro
3. **Mesma Conta**: Não permitido transferir para si mesmo
4. **Autorização**: Transação deve ser autorizada externamente
5. **Valor**: Deve ser maior que 0

## 🧪 Testes

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

## 📞 Suporte

Para mais detalhes técnicos, consulte a documentação completa na pasta `docs/`.


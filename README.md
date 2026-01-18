# Projeto1

Sistema de transações financeiras desenvolvido em CodeIgniter 4.

## Docker Setup

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


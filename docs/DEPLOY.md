# Deploy Documentation - Projeto1

## Docker Setup

### Pré-requisitos

- Docker Desktop instalado
- Docker Compose
- Git

## Ambiente de Desenvolvimento

### 1. Clonar o Projeto

```bash
git clone <repository-url>
cd projeto1
```

### 2. Configurar Banco de Dados

Edite `app/Config/Database.php`:

```php
public array $default = [
    'DSN'      => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => 'sua_senha',
    'database' => 'projeto1',
    'DBDriver' => 'MySQLi',
    // ... outras configurações
];
```

### 3. Iniciar Container

```bash
docker-compose up -d --build
```

### 4. Instalar Dependências

```bash
docker-compose exec app composer install
```

### 5. Verificar Instalação

Acesse: http://localhost:8080

## Estrutura de Arquivos

```
projeto1/
├── app/
│   ├── Controllers/     # Controladores da API
│   ├── Models/          # Models do banco de dados
│   ├── Services/        # Serviços externos
│   └── Config/          # Configurações
├── public/              # Arquivos públicos
├── writable/            # Logs e cache
├── docs/                # Documentação
├── Dockerfile           # Configuração Docker
├── docker-compose.yml   # Orquestração
├── php.ini             # Configurações PHP
└── README.md           # Documentação geral
```

## Comandos Úteis

### Container Management

```bash
# Iniciar serviços
docker-compose up -d

# Parar serviços
docker-compose down

# Reconstruir imagem
docker-compose build

# Verificar logs
docker-compose logs -f app

# Entrar no container
docker-compose exec app bash

# Reiniciar container
docker-compose restart app
```

### PHP Commands

```bash
# Instalar dependências
docker-compose exec app composer install

# Atualizar dependências
docker-compose exec app composer update

# Limpar cache
docker-compose exec app php spark cache:clear

# Verificar versão PHP
docker-compose exec app php -v
```

### Database

```bash
# Conectar ao MySQL (se estiver rodando localmente)
mysql -u root -p projeto1

# Verificar status do banco
docker-compose exec app php spark db:status
```

## Configurações

### PHP

O arquivo `php.ini` personalizado inclui:

```ini
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
display_errors = On
error_reporting = E_ALL
```

### Apache

- DocumentRoot: `/var/www/html/public`
- Mod rewrite: habilitado
- AllowOverride: All

### CodeIgniter 4

- Environment: development
- Base URL: automático
- Timezone: America/Sao_Paulo

## Troubleshooting

### Problemas Comuns

#### 1. Porta já em uso

```bash
# Verificar processo na porta 8080
netstat -tulpn | grep :8080

# Mudar porta no docker-compose.yml
ports:
  - "8081:80"  # ou outra porta disponível
```

#### 2. Erro de permissão

```bash
# Corrigir permissões no host
chmod -R 755 writable/
chmod -R 777 writable/logs/
```

#### 3. Erro de conexão com banco

```bash
# Verificar se MySQL está rodando
docker-compose exec app php spark db:connect

# Testar configuração
docker-compose exec app php -r "new \Config\Database();"
```

#### 4. Build falhando

```bash
# Limpar cache Docker
docker system prune -a

# Reconstruir sem cache
docker-compose build --no-cache
```

### Logs

#### Logs da Aplicação

```bash
# Logs do container
docker-compose logs app

# Logs do Apache
docker-compose exec app tail -f /var/log/apache2/error.log

# Logs do PHP
docker-compose exec app tail -f /var/www/html/writable/logs/log-*.php
```

#### Logs de Erro

Os erros são registrados em:
- `writable/logs/log-*.php`
- Container logs: `docker-compose logs app`

## Performance

### Otimizações

1. **PHP OPcache**: Habilitado por padrão no Docker
2. **Composer**: Instalação com `--optimize-autoloader`
3. **Apache**: Configuração básica para desenvolvimento

### Monitoramento

```bash
# Status dos containers
docker-compose ps

# Uso de recursos
docker stats

# Espaço em disco
docker system df
```

## Produção

### Considerações

1. **Security**: Remover `display_errors = On` em produção
2. **SSL**: Configurar certificado SSL
3. **Database**: Usar credenciais seguras
4. **Backup**: Implementar estratégia de backup
5. **Monitoring**: Configurar monitoramento de saúde

### Variáveis de Ambiente Produção

```env
CI_ENVIRONMENT=production
```

### Docker Compose Produção

```yaml
version: '3.8'

services:
  app:
    build: .
    environment:
      - CI_ENVIRONMENT=production
    restart: unless-stopped
    # ... outras configurações
```

## Suporte

### Documentação

- [API Documentation](./API.md)
- [CodeIgniter 4 Docs](https://codeigniter.com/user_guide/)
- [Docker Docs](https://docs.docker.com/)

### Issues

Reportar problemas através do sistema de issues do repositório.

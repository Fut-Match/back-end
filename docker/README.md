# 🐳 Docker - FutMatch API

Este documento descreve como executar o projeto FutMatch API usando Docker.

## 📋 Pré-requisitos

- Docker Engine 20.10+
- Docker Compose 2.0+

## 🚀 Configuração Inicial

### 1. Configurar Variáveis de Ambiente

Copie o arquivo de configuração Docker:
```bash
cp .env.docker .env
```

Ou configure manualmente as seguintes variáveis no seu `.env`:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=futmatch
DB_USERNAME=futmatch_user
DB_PASSWORD=futmatch_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Mail (MailPit para desenvolvimento)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### 2. Iniciar os Serviços

```bash
# Construir e iniciar todos os containers
docker-compose up -d --build

# Aguardar os serviços estarem prontos (cerca de 30 segundos)
```

### 3. Configurar Laravel

```bash
# Instalar dependências
docker-compose exec app composer install

# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Executar migrations
docker-compose exec app php artisan migrate

# Executar seeders (opcional)
docker-compose exec app php artisan db:seed

# Gerar documentação da API
docker-compose exec app php artisan l5-swagger:generate
```

## 🌐 Serviços Disponíveis

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **API Laravel** | http://localhost:8000 | Aplicação principal |
| **API Docs** | http://localhost:8000/api/documentation | Documentação Swagger |
| **phpMyAdmin** | http://localhost:8080 | Interface web MySQL |
| **MailPit** | http://localhost:8025 | Captura de emails |
| **Redis Commander** | http://localhost:8081 | Interface web Redis |

### Credenciais dos Serviços

**MySQL (phpMyAdmin):**
- Host: `mysql`
- Usuário: `futmatch_user`
- Senha: `futmatch_password`
- Database: `futmatch`

## 🛠️ Comandos Úteis

### Gerenciamento de Containers

```bash
# Iniciar serviços
docker-compose up -d

# Parar serviços
docker-compose down

# Ver logs
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f app

# Acessar container da aplicação
docker-compose exec app bash
```

### Comandos Laravel

```bash
# Artisan
docker-compose exec app php artisan <comando>

# Composer
docker-compose exec app composer <comando>

# Executar testes
docker-compose exec app php artisan test

# Limpar cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Banco de Dados

```bash
# Executar migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Reset e re-executar migrations
docker-compose exec app php artisan migrate:fresh

# Executar seeders
docker-compose exec app php artisan db:seed
```

## 🏗️ Estrutura Docker

```
docker/
├── nginx/
│   ├── nginx.conf          # Configuração principal Nginx
│   └── conf.d/
│       └── default.conf    # Virtual host Laravel
├── php/
│   ├── Dockerfile          # Container PHP 8.3.6 + Laravel
│   └── php.ini            # Configurações PHP
├── mysql/
│   └── my.cnf             # Configurações MySQL
└── redis/
    └── redis.conf         # Configurações Redis
```

## 🔧 Volumes

- **Código fonte**: Montado em `/var/www/html`
- **MySQL data**: Volume persistente `mysql-data`
- **Redis data**: Volume persistente `redis-data`
- **Logs**: Acessíveis via `docker-compose logs`

## 🚨 Troubleshooting

### Container não inicia

```bash
# Verificar logs
docker-compose logs app

# Reconstruir containers
docker-compose down -v
docker-compose up -d --build
```

### Erro de permissão

```bash
# Ajustar permissões storage/bootstrap
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### MySQL não conecta

```bash
# Verificar se MySQL está rodando
docker-compose ps mysql

# Verificar logs do MySQL
docker-compose logs mysql

# Aguardar MySQL estar pronto
docker-compose exec mysql mysql -u futmatch_user -p -e "SELECT 1"
```

### Reset completo

```bash
# Parar e remover tudo
docker-compose down -v --remove-orphans

# Remover imagens (opcional)
docker-compose down --rmi all

# Reconstruir do zero
docker-compose up -d --build
```

## � Configurações Otimizadas

### MySQL Leve
- Imagem: `mysql:8.0-debian` (mais leve que a versão completa)
- Binary logging desabilitado (`--skip-log-bin`)
- Buffer pool reduzido para 128MB
- Máximo de 50 conexões
- Query cache de 32MB

### MailPit
- Interface moderna para captura de emails
- Mais leve e rápido que MailHog
- Suporte a autenticação flexível

## � Notas de Desenvolvimento

- **Hot reload**: O código é montado como volume, mudanças são refletidas automaticamente
- **Debugging**: Logs disponíveis via `docker-compose logs -f app`
- **Performance**: Configurações otimizadas para desenvolvimento local
- **Networking**: Todos os serviços estão na rede `futmatch-network`
- **MySQL leve**: Configurado para consumir menos recursos
- **MailPit**: Substitui MailHog com interface mais moderna

## �🔒 Produção

Para deploy em produção, considere:

1. Remover serviços de desenvolvimento (mailpit, phpmyadmin, redis-commander)
2. Configurar variáveis de ambiente adequadas
3. Usar volumes nomeados para dados persistentes
4. Configurar SSL/TLS no Nginx
5. Otimizar configurações PHP/MySQL para produção
6. Habilitar binary logging do MySQL se necessário
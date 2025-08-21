# ⚽ Fut Match API

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.0-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/PostgreSQL-15-blue?style=for-the-badge&logo=postgresql" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow?style=for-the-badge" alt="Status">
</p>

## 📋 Sobre o Projeto

**Fut Match** é uma API REST desenvolvida em Laravel para gerenciamento de jogadores de futebol e partidas. O sistema foca na experiência do jogador como entidade principal, permitindo o acompanhamento de estatísticas, organização de partidas e gestão de perfis de jogadores.

## 🚀 Funcionalidades Principais

### ✅ Implementadas
- **Autenticação JWT** com Laravel Sanctum
- **Gestão de Usuários** (registro, login, logout)
- **Perfil de Jogadores** automático para cada usuário
- **Estatísticas de Jogadores** (gols, assistências, desarmes, MVPs, etc.)
- **API RESTful** completa
- **Documentação Swagger** automática

### 🔄 Em Desenvolvimento
- Gestão de Partidas
- Sistema de Times
- Ranking de Jogadores
- Histórico de Partidas

## 🛠️ Stack Tecnológica

### Backend/API
- **PHP**: ^8.2
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (Autenticação via API tokens)
- **PostgreSQL**: 15 (Banco de dados principal)
- **L5-Swagger**: ^9.0 (Documentação automática da API)

### Build Tools
- **Vite**: ^7.0.4
- **TailwindCSS**: ^4.0.0
- **Laravel Vite Plugin**: ^2.0.0

### Ferramentas de Desenvolvimento
- **Laravel Pint**: Formatação de código PHP
- **Laravel Sail**: Ambiente Docker
- **PHPUnit**: ^11.5.3 (Testes)
- **Faker**: Geração de dados fake para testes

## 🗃️ Estrutura do Banco de Dados

### Users (Usuários)
- `id`, `name`, `email`, `password`
- `email_verified_at`, `remember_token`
- `created_at`, `updated_at`

### Players (Jogadores)
- `id`, `user_id` (FK)
- `name`, `image`, `nickname`
- `goals`, `assists`, `tackles`, `mvps`
- `wins`, `matches`, `average_rating`
- `created_at`, `updated_at`

## 🔗 Endpoints da API

### Autenticação
```
POST   /api/register        # Registrar novo usuário
POST   /api/login           # Login de usuário
GET    /api/auth/user        # Dados do usuário autenticado
POST   /api/auth/logout      # Logout
POST   /api/auth/logout-all  # Logout de todos os dispositivos
```

### Jogadores
```
GET    /api/players          # Listar todos os jogadores (público)
GET    /api/players/{id}     # Exibir jogador específico (público)
GET    /api/players/me       # Meu perfil de jogador (autenticado)
PUT    /api/players/{id}     # Atualizar jogador (apenas próprio)
```

## 🚀 Instalação e Configuração

### Pré-requisitos
- PHP 8.2+
- Composer
- PostgreSQL 15+
- Docker (opcional)

### 1. Clonar o repositório
```bash
git clone https://github.com/Fut-Match/back-end.git
cd back-end
```

### 2. Instalar dependências
```bash
composer install
```

### 3. Configurar ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar banco de dados
Edite o arquivo `.env` com suas credenciais do PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=futmatch
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

### 5. Executar migrations
```bash
php artisan migrate
```

### 6. Gerar documentação da API
```bash
php artisan l5-swagger:generate
```

### 7. Iniciar servidor
```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`

## 🐳 Docker (Opcional)

Se preferir usar Docker para o PostgreSQL:

```bash
# Iniciar container PostgreSQL
docker run --name futmatch-postgres \
  -e POSTGRES_DB=futmatch \
  -e POSTGRES_USER=postgres \
  -e POSTGRES_PASSWORD=postgres \
  -p 5432:5432 \
  -d postgres:15-alpine
```

## 📖 Documentação da API

Após gerar a documentação Swagger, acesse:
- **Documentação Swagger**: `http://localhost:8000/api/documentation`
- **JSON da API**: `http://localhost:8000/api/documentation.json`

## 🧪 Executar Testes

### Configurar banco de teste
```bash
# Criar banco de teste
docker exec futmatch-postgres createdb -U postgres futmatch_test

# Ou via psql
createdb -U postgres futmatch_test
```

### Executar testes
```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test --filter=PlayerTest

# Com coverage
php artisan test --coverage
```

## 🏗️ Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── AuthController.php
│   │   ├── PlayerController.php
│   │   └── HealthController.php
│   └── Requests/
├── Models/
│   ├── User.php
│   └── Player.php
└── Observers/
    └── UserObserver.php

database/
├── factories/
│   ├── UserFactory.php
│   └── PlayerFactory.php
├── migrations/
└── seeders/

tests/
├── Feature/
│   └── PlayerTest.php
└── Unit/
```

## 📝 Convenções de Desenvolvimento

### Nomenclatura
- **Variáveis**: Inglês (ex: `$playerName`, `$matchDate`)
- **Comentários**: Português
- **Métodos**: CamelCase em inglês
- **Classes**: PascalCase em inglês
- **Tabelas**: Plural em inglês
- **Colunas**: Snake_case em inglês

### Padrões
- Autenticação via Laravel Sanctum
- Documentação Swagger obrigatória
- Testes para funcionalidades críticas
- Validação com Form Requests
- Respostas JSON padronizadas

## 🔧 Comandos Úteis

```bash
# Gerar documentação Swagger
php artisan l5-swagger:generate

# Executar testes
php artisan test

# Formatação de código
./vendor/bin/pint

# Limpar cache
php artisan optimize:clear

# Verificar rotas
php artisan route:list

# Executar migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Diretrizes de Contribuição
- Siga as convenções de nomenclatura do projeto
- Escreva testes para novas funcionalidades
- Documente endpoints com Swagger
- Use Laravel Pint para formatação
- Comentários em português, código em inglês

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👥 Equipe

- **Desenvolvedor Principal**: [Seu Nome]
- **Repositório**: [Fut-Match/back-end](https://github.com/Fut-Match/back-end)

## 📞 Suporte

Para suporte, entre em contato através dos issues do GitHub ou envie um email para [seu-email@example.com].

---

<p align="center">
  Feito com ❤️ e ⚽ pela equipe Fut Match
</p>

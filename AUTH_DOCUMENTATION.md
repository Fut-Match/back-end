# Fut Match API - Sistema de Autenticação

Esta documentação descreve a implementação do sistema de autenticação usando Laravel Sanctum para a API do Fut Match.

## 🔐 Sistema de Autenticação

A API utiliza **Laravel Sanctum** para autenticação baseada em tokens, ideal para SPAs (Single Page Applications) como React.js.

### Funcionalidades Implementadas

- ✅ Registro de usuários
- ✅ Login com email e senha
- ✅ Logout (revoga token atual)
- ✅ Logout de todos os dispositivos
- ✅ Obtenção de dados do usuário autenticado
- ✅ Validação robusta de dados
- ✅ Documentação Swagger completa
- ✅ Configuração CORS para React.js

## 🚀 Endpoints da API

### Endpoints Públicos (sem autenticação)

#### POST /api/auth/register
Registra um novo usuário no sistema.

**Body:**
```json
{
  "name": "João Silva",
  "email": "joao@exemplo.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Usuário criado com sucesso",
  "data": {
    "user": {
      "id": 1,
      "name": "João Silva", 
      "email": "joao@exemplo.com",
      "created_at": "2025-08-13T23:00:00.000000Z",
      "updated_at": "2025-08-13T23:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

#### POST /api/auth/login
Autentica um usuário existente.

**Body:**
```json
{
  "email": "joao@exemplo.com",
  "password": "senha123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "joao@exemplo.com",
      "created_at": "2025-08-13T23:00:00.000000Z",
      "updated_at": "2025-08-13T23:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Endpoints Protegidos (requer autenticação)

Para acessar estes endpoints, inclua o header:
```
Authorization: Bearer {token}
```

#### GET /api/auth/user
Retorna dados do usuário autenticado.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "joao@exemplo.com",
      "created_at": "2025-08-13T23:00:00.000000Z",
      "updated_at": "2025-08-13T23:00:00.000000Z"
    }
  }
}
```

#### POST /api/auth/logout
Faz logout do dispositivo atual (revoga apenas o token usado).

**Response (200):**
```json
{
  "success": true,
  "message": "Logout realizado com sucesso"
}
```

#### POST /api/auth/logout-all
Faz logout de todos os dispositivos (revoga todos os tokens do usuário).

**Response (200):**
```json
{
  "success": true,
  "message": "Logout de todos os dispositivos realizado com sucesso"
}
```

## 📋 Códigos de Resposta

| Código | Descrição |
|--------|-----------|
| 200    | Sucesso |
| 201    | Criado com sucesso |
| 401    | Não autorizado (token inválido/expirado) |
| 422    | Dados de validação inválidos |

## 🛠️ Configuração para React.js

### 1. Fazendo Requisições

```javascript
// Configuração do axios para produção e desenvolvimento
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NODE_ENV === 'production' 
    ? 'https://back-end-production-c28b.up.railway.app/api'
    : 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Importante para CORS com Sanctum
});

// Interceptor para adicionar token automaticamente
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Exemplo de uso
const login = async (email, password) => {
  try {
    const response = await api.post('/auth/login', { email, password });
    const { token } = response.data.data;
    localStorage.setItem('token', token);
    return response.data;
  } catch (error) {
    console.error('Erro no login:', error.response.data);
    throw error;
  }
};
```

### 2. Configuração CORS

A API já está configurada para aceitar requisições do React.js rodando em:
- `localhost:3000`
- `127.0.0.1:3000`

## 🗄️ Configuração do Banco de Dados

### PostgreSQL

Atualize seu arquivo `.env` com as configurações do PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fut_match
DB_USERNAME=postgres
DB_PASSWORD=sua_senha

# Para Railway (produção)
# DB_HOST=containers-us-west-xxx.railway.app
# DB_PORT=6543
# DB_DATABASE=railway
# DB_USERNAME=postgres
# DB_PASSWORD=senha_do_railway
```

### Executar Migrações

```bash
php artisan migrate
```

## 🚀 Deploy no Railway

### 1. Variáveis de Ambiente no Railway

Configure estas variáveis no painel do Railway:

```env
APP_NAME="Fut Match API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://back-end-production-c28b.up.railway.app

DB_CONNECTION=pgsql
DB_HOST=${{PGHOST}}
DB_PORT=${{PGPORT}}
DB_DATABASE=${{PGDATABASE}}
DB_USERNAME=${{PGUSER}}
DB_PASSWORD=${{PGPASSWORD}}

SANCTUM_STATEFUL_DOMAINS=front-end-murex-nu.vercel.app,back-end-production-c28b.up.railway.app
```

### 2. URLs em Produção

- **API Base:** https://back-end-production-c28b.up.railway.app/api
- **Documentação:** https://back-end-production-c28b.up.railway.app/docs
- **Front-end:** https://front-end-murex-nu.vercel.app/

### 3. Comandos pós-deploy

```bash
php artisan config:cache
php artisan route:cache
php artisan migrate --force
php artisan l5-swagger:generate
```

## 📚 Documentação Swagger

Acesse a documentação interativa em:
- **Desenvolvimento:** http://localhost:8000/docs
- **Produção:** https://seu-projeto.up.railway.app/docs

## 🔧 Comandos Úteis

```bash
# Limpar todos os caches
php artisan optimize:clear

# Gerar documentação Swagger
php artisan l5-swagger:generate

# Listar rotas
php artisan route:list

# Executar migrações
php artisan migrate

# Executar testes
php artisan test
```

## 🛡️ Segurança

### Boas Práticas Implementadas

1. **Validação robusta** nos requests
2. **Hash seguro** das senhas (bcrypt)
3. **Tokens únicos** por dispositivo
4. **Revogação fácil** de tokens
5. **CORS configurado** adequadamente
6. **Rate limiting** (pode ser configurado)

### Configurações de Segurança Recomendadas

```env
# Produção
APP_DEBUG=false
SANCTUM_STATEFUL_DOMAINS=apenas-seus-dominios.com
SESSION_SECURE_COOKIE=true
```

## 🧪 Testando a API

### Exemplos com cURL

```bash
# Registro em produção
curl -X POST https://back-end-production-c28b.up.railway.app/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Teste User",
    "email": "teste@exemplo.com", 
    "password": "senha123",
    "password_confirmation": "senha123"
  }'

# Login em produção
curl -X POST https://back-end-production-c28b.up.railway.app/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "teste@exemplo.com",
    "password": "senha123"
  }'

# Acessar dados do usuário (substitua SEU_TOKEN)
curl -X GET https://back-end-production-c28b.up.railway.app/api/auth/user \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Accept: application/json"

# Desenvolvimento local
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste User",
    "email": "teste@exemplo.com", 
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
```

## 📝 Próximos Passos

Sugestões para expandir o sistema:

1. **Reset de senha** via email
2. **Verificação de email**
3. **Perfis de usuário** (jogador, organizador, etc.)
4. **Rate limiting** para prevenir ataques
5. **Logs de auditoria** de autenticação
6. **2FA (Two-Factor Authentication)**

---

**Implementado com:** Laravel 12 + Sanctum + PostgreSQL + Swagger
**Compatível com:** React.js, Vue.js, Angular e apps móveis

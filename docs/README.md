# Documentação da API Fut Match

## 📋 Índice da Documentação

Este diretório contém a documentação completa da API Fut Match. Aqui você encontrará guias detalhados sobre cada funcionalidade do sistema.

---

## 📚 Documentos Disponíveis

### 🏗️ **Arquitetura e Conceitos**
- [`user-player-relationship.md`](./user-player-relationship.md) - Relacionamento entre User e Player

### 🎮 **APIs e Funcionalidades**
- [`matches-api.md`](./matches-api.md) - API completa de Partidas de Futebol

---

## 🚀 Como Usar a Documentação

### 1. **Swagger/OpenAPI**
A documentação interativa da API está disponível em:
- **Desenvolvimento**: `http://localhost:8000/api/documentation`
- **Produção**: `https://back-end-production-c28b.up.railway.app/api/documentation`

### 2. **Postman Collection**
Você pode importar as rotas da API diretamente do arquivo Swagger:
```bash
# Baixar o arquivo JSON do Swagger
curl http://localhost:8000/api-docs.json -o fut-match-api.json
```

### 3. **Testando a API**
Para testar as rotas, você precisará:
1. **Registrar um usuário**: `POST /api/register`
2. **Fazer login**: `POST /api/login`
3. **Usar o token**: Incluir `Authorization: Bearer {token}` nos headers

---

## 🔐 Autenticação

Todas as rotas protegidas requerem autenticação via **Laravel Sanctum**. 

### Exemplo de Header
```
Authorization: Bearer 1|abcdef123456789...
```

### Obtendo o Token
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "password": "senha123"
  }'
```

---

## 📊 Estrutura das Respostas

### ✅ Sucesso
```json
{
  "success": true,
  "data": { ... },
  "message": "Operação realizada com sucesso"
}
```

### ❌ Erro
```json
{
  "success": false,
  "message": "Mensagem de erro em português",
  "errors": { ... }
}
```

---

## 🛠️ Ferramentas Úteis

### Comandos Laravel
```bash
# Gerar documentação Swagger
php artisan l5-swagger:generate

# Executar testes
php artisan test

# Limpar cache
php artisan optimize:clear

# Executar migrations
php artisan migrate
```

### Códigos de Status HTTP
| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autenticado |
| 403 | Não autorizado |
| 404 | Não encontrado |
| 422 | Erro de validação |
| 500 | Erro interno do servidor |

---

## 🔄 Atualizações

Esta documentação é atualizada automaticamente quando:
- Novas rotas são adicionadas
- Modelos são modificados
- Validações são alteradas

**Última atualização**: 10 de setembro de 2025

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte a documentação específica de cada funcionalidade
2. Verifique o Swagger interativo
3. Execute os testes para validar o comportamento esperado

---

**Próximas funcionalidades**: Sistema de estatísticas, rankings e histórico de partidas.

# Copilot Instructions - Fut Match API

## 🎯 Sobre o Projeto

**Fut Match** é uma **API REST Laravel** para gerenciamento de jogadores de futebol e partidas. Este projeto é focado **exclusivamente no backend/API**, com o front-end sendo desenvolvido separadamente em outra tecnologia. O sistema foca na experiência do jogador como entidade principal.

## 🛠️ Stack Tecnológica

### Backend/API
- **PHP**: ^8.2
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (Autenticação via API tokens)
- **PostgreSQL**: 15 (Banco de dados principal)
- **L5-Swagger**: ^9.0 (Documentação automática da API)

### Build Tools (Para assets mínimos da API)
- **Vite**: ^7.0.4 (Build tool para assets do Laravel)
- **TailwindCSS**: ^4.0.0 (Apenas para páginas de documentação/admin se necessário)
- **Laravel Vite Plugin**: ^2.0.0

### Ferramentas de Desenvolvimento
- **Laravel Pint**: Formatação de código PHP
- **Laravel Sail**: Ambiente Docker
- **PHPUnit**: ^11.5.3 (Testes)
- **Faker**: Geração de dados fake para testes

## 📋 Regras de Desenvolvimento

### 🔤 Convenções de Nomenclatura
- **Variáveis**: Sempre em inglês (ex: `$playerName`, `$matchDate`)
- **Comentários**: Sempre em português
- **Métodos**: CamelCase em inglês (ex: `getPlayerStats()`)
- **Classes**: PascalCase em inglês (ex: `PlayerController`)
- **Tabelas**: Plural em inglês (ex: `players`, `matches`)
- **Colunas**: Snake_case em inglês (ex: `player_name`, `created_at`)

### 🔐 Autenticação
- Utilizar **Laravel Sanctum** para autenticação via API tokens
- Todas as rotas protegidas devem usar o middleware `auth:sanctum`
- Cada usuário no sistema é um **jogador (player)**

### 📖 Documentação
- Documentar todas as rotas usando **Swagger/OpenAPI**
- Utilizar annotations do L5-Swagger nos controllers
- Manter documentação sempre atualizada

### 🏗️ Estrutura de Código

#### Controllers
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Players",
 *     description="Operações relacionadas aos jogadores"
 * )
 */
class PlayerController extends Controller
{
    /**
     * Lista todos os jogadores
     * 
     * @OA\Get(
     *     path="/api/players",
     *     tags={"Players"},
     *     summary="Lista jogadores",
     *     @OA\Response(response="200", description="Lista de jogadores")
     * )
     */
    public function index()
    {
        // Implementação aqui
    }
}
```

#### Models
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model representing a player in the system
 */
class Player extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'player_name',
        'email',
        'position',
    ];

    /**
     * Relacionamentos sempre com comentários em português
     */
    public function matches()
    {
        // Retorna as partidas do jogador
        return $this->belongsToMany(Match::class);
    }
}
```

#### Requests
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlayerRequest extends FormRequest
{
    /**
     * Regras de validação para criação de jogador
     */
    public function rules(): array
    {
        return [
            'player_name' => 'required|string|max:255',
            'email' => 'required|email|unique:players',
            'position' => 'required|string|in:goalkeeper,defender,midfielder,forward',
        ];
    }

    /**
     * Mensagens customizadas em português
     */
    public function messages(): array
    {
        return [
            'player_name.required' => 'O nome do jogador é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.unique' => 'Este email já está em uso',
        ];
    }
}
```

### 🛣️ Padrões de Rotas
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Rotas protegidas aqui
    Route::apiResource('players', PlayerController::class);
});

// Rotas públicas (login, registro)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
```

### 🧪 Testes
- Escrever testes para todas as funcionalidades críticas
- Usar factories para geração de dados de teste
- Nomenclatura de testes em inglês, descrições em português

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class PlayerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_player()
    {
        // Testa se consegue criar um jogador
        $playerData = [
            'player_name' => 'João Silva',
            'email' => 'joao@example.com',
            'position' => 'midfielder'
        ];

        $response = $this->postJson('/api/players', $playerData);
        
        $response->assertStatus(201);
    }
}
```

### 📄 Estrutura de Resposta da API
```php
// Sucesso
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operação realizada com sucesso'
], 200);

// Erro
return response()->json([
    'success' => false,
    'message' => 'Mensagem de erro em português',
    'errors' => $validationErrors // quando aplicável
], 400);
```

## 🎮 Contexto do Domínio

- **Player (Jogador)**: Entidade principal do sistema
- Cada usuário autenticado é um jogador
- Sistema focado em funcionalidades relacionadas ao futebol
- **API REST**: Foco exclusivo em endpoints para comunicação com front-end externo
- Futuras entidades podem incluir: partidas, times, estatísticas, etc.
- Front-end será desenvolvido em tecnologia separada (não Laravel)

## 🚀 Comandos Úteis

```bash
# Gerar documentação Swagger
php artisan l5-swagger:generate

# Executar testes
php artisan test

# Formatação de código
./vendor/bin/pint

# Limpar cache
php artisan optimize:clear
```

## 📝 Observações Importantes

1. **Sempre validar dados** usando Form Requests
2. **Documentar endpoints** com Swagger annotations
3. **Usar migrations** para mudanças no banco de dados
4. **Implementar middleware** de autenticação em rotas protegidas
5. **Seguir princípios RESTful** para design da API
6. **Comentários explicativos** em português para lógica complexa
7. **Variáveis e código** sempre em inglês para manter padrão internacional

---

*Este arquivo será atualizado conforme o projeto evolui e novas funcionalidades são implementadas.*

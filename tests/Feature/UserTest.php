<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessário para funcionamento dos testes
    }

    /** @test */
    public function it_can_list_all_users()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria alguns usuários para teste
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'per_page',
                    'total',
                    'data' => [
                        '*' => ['id', 'name', 'email', 'created_at', 'updated_at']
                    ]
                ]
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_show_a_specific_user()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria usuário para mostrar
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário encontrado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => 'João Silva',
                    'email' => 'joao@example.com'
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_new_user()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário criado com sucesso'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);
    }

    /** @test */
    public function it_can_update_a_user()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria usuário para atualizar
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);

        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@example.com'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => [
                    'name' => 'João Silva Santos',
                    'email' => 'joao.santos@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@example.com'
        ]);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria usuário para deletar
        $user = User::factory()->create();
        
        // Cria player vinculado ao usuário
        $player = Player::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário removido com sucesso'
            ]);

        // Verifica se usuário foi deletado
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        
        // Verifica se player foi deletado automaticamente (cascade)
        $this->assertDatabaseMissing('players', ['id' => $player->id]);
    }

    /** @test */
    public function it_can_get_authenticated_user_data()
    {
        // Cria usuário autenticado
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Dados do usuário autenticado',
                'data' => [
                    'id' => $user->id,
                    'name' => 'João Silva',
                    'email' => 'joao@example.com'
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->postJson('/api/users', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/users/me');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_user_creation_data()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Testa com dados inválidos
        $response = $this->postJson('/api/users', [
            'name' => '', // Nome vazio
            'email' => 'email-invalido', // Email inválido
            'password' => '123' // Senha muito simples
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_validates_unique_email_on_creation()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria usuário existente
        $existingUser = User::factory()->create(['email' => 'joao@example.com']);

        // Tenta criar usuário com email duplicado
        $response = $this->postJson('/api/users', [
            'name' => 'Outro João',
            'email' => 'joao@example.com', // Email já existe
            'password' => 'Password123!'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_unique_email_on_update_ignoring_own_email()
    {
        // Cria usuário autenticado
        $authUser = User::factory()->create();
        Sanctum::actingAs($authUser);

        // Cria usuários
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Tenta atualizar user1 com email do user2 (deve falhar)
        $response = $this->putJson("/api/users/{$user1->id}", [
            'email' => 'user2@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Atualiza user1 mantendo seu próprio email (deve passar)
        $response = $this->putJson("/api/users/{$user1->id}", [
            'name' => 'Nome Atualizado',
            'email' => 'user1@example.com'
        ]);

        $response->assertStatus(200);
    }
}

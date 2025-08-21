<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Testa se um player é criado automaticamente quando um usuário é criado
     */
    public function test_player_is_created_when_user_is_created(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('players', [
            'user_id' => $user->id,
            'name' => $user->name,
            'goals' => 0,
            'assists' => 0,
            'tackles' => 0,
            'mvps' => 0,
            'wins' => 0,
            'matches' => 0,
            'average_rating' => 0.00,
        ]);

        $this->assertNotNull($user->player);
    }

    /**
     * Testa a listagem de jogadores
     */
    public function test_can_list_players(): void
    {
        Player::factory()->count(5)->create();

        $response = $this->getJson('/api/players');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'user_id',
                                'name',
                                'nickname',
                                'goals',
                                'assists',
                                'tackles',
                                'mvps',
                                'wins',
                                'matches',
                                'average_rating',
                                'user'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /**
     * Testa a visualização de um jogador específico
     */
    public function test_can_show_specific_player(): void
    {
        $player = Player::factory()->create();

        $response = $this->getJson("/api/players/{$player->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Jogador encontrado com sucesso'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'user_id',
                        'name',
                        'nickname',
                        'goals',
                        'assists',
                        'tackles',
                        'mvps',
                        'wins',
                        'matches',
                        'average_rating',
                        'user'
                    ]
                ]);
    }

    /**
     * Testa se usuário autenticado pode ver suas informações de jogador
     */
    public function test_authenticated_user_can_see_own_player_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/players/me');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Dados do jogador retornados com sucesso'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'user_id',
                        'name',
                        'nickname',
                        'goals',
                        'assists',
                        'tackles',
                        'mvps',
                        'wins',
                        'matches',
                        'average_rating'
                    ]
                ]);
    }

    /**
     * Testa se usuário pode atualizar seu próprio player
     */
    public function test_user_can_update_own_player(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'Nome Atualizado',
            'nickname' => 'NovoApelido',
            'image' => 'https://example.com/nova-imagem.jpg'
        ];

        $response = $this->putJson("/api/players/{$user->player->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Jogador atualizado com sucesso'
                ]);

        $this->assertDatabaseHas('players', [
            'id' => $user->player->id,
            'name' => 'Nome Atualizado',
            'nickname' => 'NovoApelido',
            'image' => 'https://example.com/nova-imagem.jpg'
        ]);
    }

    /**
     * Testa se usuário não pode atualizar player de outro usuário
     */
    public function test_user_cannot_update_other_users_player(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Sanctum::actingAs($user1);

        $updateData = [
            'name' => 'Tentativa de hack',
            'nickname' => 'Hacker'
        ];

        $response = $this->putJson("/api/players/{$user2->player->id}", $updateData);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Você não tem permissão para editar este jogador'
                ]);
    }

    /**
     * Testa se método getWinPercentageAttribute funciona corretamente
     */
    public function test_win_percentage_calculation(): void
    {
        $player = Player::factory()->create([
            'wins' => 7,
            'matches' => 10
        ]);

        $this->assertEquals(70.0, $player->win_percentage);

        // Teste com zero partidas
        $newPlayer = Player::factory()->newbie()->create();
        $this->assertEquals(0.0, $newPlayer->win_percentage);
    }

    /**
     * Testa se método hasStats funciona corretamente
     */
    public function test_has_stats_method(): void
    {
        $playerWithStats = Player::factory()->create(['matches' => 5]);
        $playerWithoutStats = Player::factory()->newbie()->create();

        $this->assertTrue($playerWithStats->hasStats());
        $this->assertFalse($playerWithoutStats->hasStats());
    }

    /**
     * Testa se o nome do player é atualizado quando o nome do usuário é alterado
     */
    public function test_player_name_updates_when_user_name_changes(): void
    {
        $user = User::factory()->create(['name' => 'Nome Original']);
        $originalPlayerName = $user->player->name;

        $this->assertEquals('Nome Original', $originalPlayerName);

        $user->update(['name' => 'Nome Atualizado']);

        $this->assertEquals('Nome Atualizado', $user->player->fresh()->name);
    }
}

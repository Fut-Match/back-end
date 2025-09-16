<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se consegue registrar um usuário e envia email de verificação
     */
    public function test_user_can_register_and_receives_verification_email()
    {
        Event::fake();

        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => '123456789',
            'password_confirmation' => '123456789'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário registrado com sucesso. Verifique seu email para ativar a conta.',
                'data' => [
                    'email_verified' => false
                ]
            ]);

        // Verifica se o usuário foi criado no banco
        $this->assertDatabaseHas('users', [
            'email' => 'joao@exemplo.com',
            'email_verified_at' => null
        ]);

        // Verifica se o evento Registered foi disparado (que envia o email)
        Event::assertDispatched(Registered::class);
    }

    /**
     * Testa se usuário não pode fazer login sem verificar email
     */
    public function test_user_cannot_login_without_email_verification()
    {
        // Criar usuário sem verificar email
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => bcrypt('123456789'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@exemplo.com',
            'password' => '123456789'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Email não verificado. Verifique seu email antes de fazer login.',
                'email_verified' => false
            ]);
    }

    /**
     * Testa se usuário pode fazer login após verificar email
     */
    public function test_user_can_login_after_email_verification()
    {
        // Criar usuário e marcar email como verificado
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => bcrypt('123456789'),
        ]);
        $user->markEmailAsVerified();

        $response = $this->postJson('/api/login', [
            'email' => 'joao@exemplo.com',
            'password' => '123456789'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'email_verified' => true
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                    'user'
                ]
            ]);
    }

    /**
     * Testa se pode reenviar email de verificação
     */
    public function test_can_resend_verification_email()
    {
        // Criar usuário sem verificar email
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => bcrypt('123456789'),
        ]);

        $response = $this->postJson('/api/email/verification-notification', [
            'email' => 'joao@exemplo.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email de verificação reenviado com sucesso'
            ]);
    }

    /**
     * Testa se não pode reenviar email para usuário já verificado
     */
    public function test_cannot_resend_verification_email_for_verified_user()
    {
        // Criar usuário e marcar email como verificado
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => bcrypt('123456789'),
        ]);
        $user->markEmailAsVerified();

        $response = $this->postJson('/api/email/verification-notification', [
            'email' => 'joao@exemplo.com'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email já foi verificado'
            ]);
    }

    /**
     * Testa logout
     */
    public function test_user_can_logout()
    {
        // Criar usuário verificado
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        // Gerar token
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ]);
    }
}

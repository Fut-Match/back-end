<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailPtBr;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Envia a notificação de verificação de e-mail em português
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailPtBr());
    }

    /**
     * Relacionamento com jogador
     * Cada usuário tem um jogador
     */
    public function player(): HasOne
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Boot method para configurar eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Quando um usuário for deletado, o player será automaticamente
        // deletado devido ao cascade na foreign key da migration
        static::deleting(function ($user) {
            // Log ou outras ações podem ser adicionadas aqui se necessário
        });
    }
    
}

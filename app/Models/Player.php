<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model representing a player in the system
 */
class Player extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'image',
        'nickname',
        'goals',
        'assists',
        'tackles',
        'mvps',
        'wins',
        'matches',
        'average_rating',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'goals' => 'integer',
        'assists' => 'integer',
        'tackles' => 'integer',
        'mvps' => 'integer',
        'wins' => 'integer',
        'matches' => 'integer',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Relacionamento com usuário
     * Cada jogador pertence a um usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula a porcentagem de vitórias do jogador
     */
    public function getWinPercentageAttribute(): float
    {
        if ($this->matches === 0) {
            return 0;
        }

        return round(($this->wins / $this->matches) * 100, 2);
    }

    /**
     * Verifica se o jogador tem estatísticas registradas
     */
    public function hasStats(): bool
    {
        return $this->matches > 0;
    }
}

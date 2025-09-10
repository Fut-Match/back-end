<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Model representing a football match in the system
 */
class FootballMatch extends Model
{
    use HasFactory;

    /**
     * Nome da tabela no banco de dados
     */
    protected $table = 'matches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'admin_id',
        'match_date',
        'match_time',
        'location',
        'players_count',
        'end_mode',
        'goal_limit',
        'time_limit',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime:H:i',
        'goal_limit' => 'integer',
        'time_limit' => 'integer',
    ];

    /**
     * Boot method para gerar código automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($match) {
            if (empty($match->code)) {
                $match->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Relacionamento com o administrador da partida
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'admin_id');
    }

    /**
     * Relacionamento com os participantes da partida
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'match_participants', 'match_id', 'player_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /**
     * Verifica se a partida está cheia
     */
    public function isFull(): bool
    {
        $maxPlayers = match ($this->players_count) {
            '3vs3' => 6,
            '5vs5' => 10,
            '6vs6' => 12,
            default => 0,
        };

        return $this->participants()->count() >= $maxPlayers;
    }

    /**
     * Adiciona um jogador à partida
     */
    public function addParticipant(Player $player): bool
    {
        if ($this->isFull() || $this->participants()->where('player_id', $player->id)->exists()) {
            return false;
        }

        $this->participants()->attach($player->id, ['joined_at' => now()]);
        return true;
    }

    /**
     * Remove um jogador da partida
     */
    public function removeParticipant(Player $player): bool
    {
        return $this->participants()->detach($player->id) > 0;
    }

    /**
     * Gera um código único para a partida
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Busca uma partida pelo código
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * Verifica se o jogador pode participar da partida
     */
    public function canJoin(Player $player): bool
    {
        return !$this->isFull() 
               && $this->status === 'waiting' 
               && !$this->participants()->where('player_id', $player->id)->exists();
    }
}

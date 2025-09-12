<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model representing an event in a football match
 */
class MatchEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'player_id',
        'team_id',
        'event_type',
        'minute',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'minute' => 'integer',
    ];

    /**
     * Relacionamento com a partida
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    /**
     * Relacionamento com o jogador
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Relacionamento com o time
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(MatchTeam::class, 'team_id');
    }

    /**
     * Scope para filtrar por tipo de evento
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope para filtrar eventos de gol
     */
    public function scopeGoals($query)
    {
        return $query->where('event_type', 'goal');
    }

    /**
     * Scope para filtrar eventos de assistência
     */
    public function scopeAssists($query)
    {
        return $query->where('event_type', 'assist');
    }

    /**
     * Scope para filtrar eventos de desarme
     */
    public function scopeTackles($query)
    {
        return $query->where('event_type', 'tackle');
    }

    /**
     * Scope para filtrar eventos de defesa
     */
    public function scopeDefenses($query)
    {
        return $query->where('event_type', 'defense');
    }
}

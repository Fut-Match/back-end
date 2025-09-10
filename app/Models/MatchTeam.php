<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model representing a team in a football match
 */
class MatchTeam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'team_name',
        'team_color',
        'score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Relacionamento com a partida
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    /**
     * Relacionamento com os jogadores do time
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'match_participants', 'team_id', 'player_id')
                    ->withPivot('joined_at', 'goals_scored', 'assists_made', 'tackles_made', 'defenses_made')
                    ->withTimestamps();
    }

    /**
     * Relacionamento com os eventos do time
     */
    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'team_id');
    }

    /**
     * Adiciona um gol ao placar do time
     */
    public function addGoal(): void
    {
        $this->increment('score');
    }

    /**
     * Remove um gol do placar do time
     */
    public function removeGoal(): void
    {
        if ($this->score > 0) {
            $this->decrement('score');
        }
    }

    /**
     * Verifica se este time é o vencedor
     */
    public function isWinner(): bool
    {
        $match = $this->match;
        $oppositeTeam = $match->teams()->where('id', '!=', $this->id)->first();
        
        return $this->score > ($oppositeTeam ? $oppositeTeam->score : 0);
    }
}

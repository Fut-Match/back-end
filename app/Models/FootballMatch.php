<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'started_at',
        'finished_at',
        'current_minute',
        'is_paused',
        'winning_team_id',
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
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'current_minute' => 'integer',
        'is_paused' => 'boolean',
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
                    ->withPivot('joined_at', 'team_id', 'goals_scored', 'assists_made', 'tackles_made', 'defenses_made')
                    ->withTimestamps();
    }

    /**
     * Relacionamento com os times da partida
     */
    public function teams(): HasMany
    {
        return $this->hasMany(MatchTeam::class, 'match_id');
    }

    /**
     * Relacionamento com o time vencedor
     */
    public function winningTeam(): BelongsTo
    {
        return $this->belongsTo(MatchTeam::class, 'winning_team_id');
    }

    /**
     * Relacionamento com os eventos da partida
     */
    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
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

    /**
     * Cria os times para a partida
     */
    public function createTeams(): void
    {
        if ($this->teams()->count() === 0) {
            $this->teams()->create([
                'team_name' => 'team_a',
                'team_color' => '#FF6B6B',
                'score' => 0,
            ]);

            $this->teams()->create([
                'team_name' => 'team_b', 
                'team_color' => '#4ECDC4',
                'score' => 0,
            ]);
        }
    }

    /**
     * Sorteia os times automaticamente
     */
    public function shuffleTeams(): array
    {
        $this->createTeams();
        
        $participants = $this->participants()->get();
        $shuffled = $participants->shuffle();
        
        $teamA = $this->teams()->where('team_name', 'team_a')->first();
        $teamB = $this->teams()->where('team_name', 'team_b')->first();
        
        $maxPerTeam = match ($this->players_count) {
            '3vs3' => 3,
            '5vs5' => 5,
            '6vs6' => 6,
            default => 5,
        };

        // Limpa times existentes
        $this->participants()->wherePivot('team_id', '!=', null)->detach();

        // Distribui jogadores
        foreach ($shuffled as $index => $player) {
            $teamId = ($index % 2 === 0) ? $teamA->id : $teamB->id;
            
            $this->participants()->updateExistingPivot($player->id, [
                'team_id' => $teamId,
                'goals_scored' => 0,
                'assists_made' => 0,
                'tackles_made' => 0,
                'defenses_made' => 0,
            ]);
        }

        return [
            'team_a' => $teamA->load('players.user'),
            'team_b' => $teamB->load('players.user'),
        ];
    }

    /**
     * Inicia a partida
     */
    public function startMatch(): bool
    {
        if ($this->status !== 'waiting') {
            return false;
        }

        $this->createTeams();
        
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'current_minute' => 0,
            'is_paused' => false,
        ]);

        return true;
    }

    /**
     * Pausa/resume a partida
     */
    public function togglePause(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        $this->update(['is_paused' => !$this->is_paused]);
        return true;
    }

    /**
     * Finaliza a partida
     */
    public function finishMatch(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        $teamA = $this->teams()->where('team_name', 'team_a')->first();
        $teamB = $this->teams()->where('team_name', 'team_b')->first();

        $winningTeamId = null;
        if ($teamA && $teamB) {
            if ($teamA->score > $teamB->score) {
                $winningTeamId = $teamA->id;
                $this->updatePlayerStats($teamA, true); // Time vencedor
                $this->updatePlayerStats($teamB, false); // Time perdedor
            } elseif ($teamB->score > $teamA->score) {
                $winningTeamId = $teamB->id;
                $this->updatePlayerStats($teamB, true); // Time vencedor
                $this->updatePlayerStats($teamA, false); // Time perdedor
            }
        }

        $this->update([
            'status' => 'finished',
            'finished_at' => now(),
            'winning_team_id' => $winningTeamId,
        ]);

        return true;
    }

    /**
     * Atualiza as estatísticas dos jogadores após a partida
     */
    private function updatePlayerStats(MatchTeam $team, bool $isWinner): void
    {
        $team->players()->each(function ($player) use ($isWinner) {
            $pivot = $player->pivot;
            
            // Atualiza estatísticas do jogador
            $player->increment('goals', $pivot->goals_scored);
            $player->increment('assists', $pivot->assists_made);
            $player->increment('tackles', $pivot->tackles_made);
            $player->increment('matches');
            
            if ($isWinner) {
                $player->increment('wins');
            }

            // Recalcula a média de rating (exemplo simples)
            $newRating = $this->calculatePlayerRating($pivot);
            $currentRating = $player->average_rating ?? 0;
            $totalMatches = $player->matches;
            
            $updatedRating = (($currentRating * ($totalMatches - 1)) + $newRating) / $totalMatches;
            $player->update(['average_rating' => round($updatedRating, 2)]);
        });
    }

    /**
     * Calcula o rating do jogador baseado na performance
     */
    private function calculatePlayerRating($pivot): float
    {
        $baseRating = 5.0;
        $goalPoints = $pivot->goals_scored * 1.5;
        $assistPoints = $pivot->assists_made * 1.0;
        $tacklePoints = $pivot->tackles_made * 0.5;
        $defensePoints = $pivot->defenses_made * 0.3;
        
        return min(10.0, $baseRating + $goalPoints + $assistPoints + $tacklePoints + $defensePoints);
    }

    /**
     * Adiciona um evento à partida
     */
    public function addEvent(Player $player, string $eventType, ?int $minute = null, ?string $description = null): MatchEvent
    {
        $playerTeam = $this->participants()
                          ->where('player_id', $player->id)
                          ->wherePivot('team_id', '!=', null)
                          ->first();

        $event = $this->events()->create([
            'player_id' => $player->id,
            'team_id' => $playerTeam ? $playerTeam->pivot->team_id : null,
            'event_type' => $eventType,
            'minute' => $minute ?? $this->current_minute,
            'description' => $description,
        ]);

        // Atualiza estatísticas em tempo real
        if ($playerTeam) {
            $this->updatePlayerMatchStats($player, $eventType);
            
            if ($eventType === 'goal') {
                $team = MatchTeam::find($playerTeam->pivot->team_id);
                $team?->addGoal();
            }
        }

        return $event;
    }

    /**
     * Atualiza estatísticas do jogador durante a partida
     */
    private function updatePlayerMatchStats(Player $player, string $eventType): void
    {
        $pivotColumn = match ($eventType) {
            'goal' => 'goals_scored',
            'assist' => 'assists_made',
            'tackle' => 'tackles_made',
            'defense' => 'defenses_made',
            default => null,
        };

        if ($pivotColumn) {
            $currentValue = $this->participants()
                                ->where('player_id', $player->id)
                                ->first()
                                ->pivot
                                ->{$pivotColumn} ?? 0;

            $this->participants()->updateExistingPivot($player->id, [
                $pivotColumn => $currentValue + 1
            ]);
        }
    }
}

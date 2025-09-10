<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FootballMatch;
use App\Models\MatchTeam;
use App\Models\MatchEvent;
use App\Models\Player;

class MatchEventSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buscar partida em andamento
        $inProgressMatch = FootballMatch::where('code', 'PROG01')->first();
        
        if (!$inProgressMatch) {
            $this->command->error('❌ Partida em andamento não encontrada.');
            return;
        }

        $teams = $inProgressMatch->teams;
        $players = Player::all();
        
        if ($teams->count() < 2 || $players->count() < 6) {
            $this->command->error('❌ Times ou players insuficientes.');
            return;
        }

        $teamA = $teams->where('team_name', 'team_a')->first();
        $teamB = $teams->where('team_name', 'team_b')->first();

        // Eventos da partida em andamento
        $events = [
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[0]->id,
                'team_id' => $teamA->id,
                'event_type' => 'goal',
                'minute' => 8,
                'description' => 'Gol de pé esquerdo após cruzamento'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[2]->id,
                'team_id' => $teamA->id,
                'event_type' => 'assist',
                'minute' => 8,
                'description' => 'Cruzamento perfeito da direita'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[1]->id,
                'team_id' => $teamB->id,
                'event_type' => 'goal',
                'minute' => 15,
                'description' => 'Gol de cabeça no segundo pau'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[0]->id,
                'team_id' => $teamA->id,
                'event_type' => 'goal',
                'minute' => 22,
                'description' => 'Gol de pênalti batido no canto'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[3]->id,
                'team_id' => $teamB->id,
                'event_type' => 'tackle',
                'minute' => 18,
                'description' => 'Desarme limpo na entrada da área'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[4]->id,
                'team_id' => $teamA->id,
                'event_type' => 'defense',
                'minute' => 23,
                'description' => 'Defesa espetacular do goleiro'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[5]->id,
                'team_id' => $teamB->id,
                'event_type' => 'tackle',
                'minute' => 20,
                'description' => 'Interceptação no meio de campo'
            ],
            [
                'match_id' => $inProgressMatch->id,
                'player_id' => $players[2]->id,
                'team_id' => $teamA->id,
                'event_type' => 'tackle',
                'minute' => 12,
                'description' => 'Desarme na defesa'
            ],
        ];

        foreach ($events as $eventData) {
            MatchEvent::firstOrCreate(
                [
                    'match_id' => $eventData['match_id'],
                    'player_id' => $eventData['player_id'],
                    'event_type' => $eventData['event_type'],
                    'minute' => $eventData['minute']
                ],
                $eventData
            );
        }

        // Eventos para partida finalizada
        $finishedMatch = FootballMatch::where('code', 'FINI01')->first();
        
        if ($finishedMatch && $finishedMatch->teams->count() >= 2) {
            $finishedTeamA = $finishedMatch->teams->where('team_name', 'team_a')->first();
            $finishedTeamB = $finishedMatch->teams->where('team_name', 'team_b')->first();

            $finishedEvents = [
                // Gols do Time A (4 gols)
                ['player_id' => $players[0]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'goal', 'minute' => 5, 'description' => 'Abertura do placar com chute de fora da área'],
                ['player_id' => $players[2]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'goal', 'minute' => 18, 'description' => 'Gol em contra-ataque rápido'],
                ['player_id' => $players[0]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'goal', 'minute' => 35, 'description' => 'Segundo gol do artilheiro'],
                ['player_id' => $players[4]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'goal', 'minute' => 52, 'description' => 'Gol que definiu a partida'],
                
                // Gols do Time B (2 gols)
                ['player_id' => $players[1]->id, 'team_id' => $finishedTeamB->id, 'event_type' => 'goal', 'minute' => 25, 'description' => 'Gol de empate momentâneo'],
                ['player_id' => $players[3]->id, 'team_id' => $finishedTeamB->id, 'event_type' => 'goal', 'minute' => 45, 'description' => 'Gol nos acréscimos do primeiro tempo'],
                
                // Assistências
                ['player_id' => $players[2]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'assist', 'minute' => 5, 'description' => 'Passe açucarado para o primeiro gol'],
                ['player_id' => $players[4]->id, 'team_id' => $finishedTeamA->id, 'event_type' => 'assist', 'minute' => 35, 'description' => 'Assistência de calcanhar'],
                ['player_id' => $players[1]->id, 'team_id' => $finishedTeamB->id, 'event_type' => 'assist', 'minute' => 45, 'description' => 'Cruzamento milimétrico'],
            ];

            foreach ($finishedEvents as $eventData) {
                MatchEvent::firstOrCreate(
                    [
                        'match_id' => $finishedMatch->id,
                        'player_id' => $eventData['player_id'],
                        'event_type' => $eventData['event_type'],
                        'minute' => $eventData['minute']
                    ],
                    array_merge($eventData, ['match_id' => $finishedMatch->id])
                );
            }
        }

        $eventCount = MatchEvent::count();
        $this->command->info('✅ Eventos criados: ' . $eventCount);
        $this->command->info('   - Partida em andamento: ' . MatchEvent::where('match_id', $inProgressMatch->id)->count() . ' eventos');
        
        if ($finishedMatch) {
            $this->command->info('   - Partida finalizada: ' . MatchEvent::where('match_id', $finishedMatch->id)->count() . ' eventos');
        }
    }
}

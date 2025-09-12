<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FootballMatch;
use App\Models\MatchTeam;
use App\Models\Player;

class MatchTeamSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buscar partida em andamento
        $inProgressMatch = FootballMatch::where('code', 'PROG01')->first();
        
        if (!$inProgressMatch) {
            $this->command->error('❌ Partida em andamento não encontrada. Execute MatchSeeder primeiro.');
            return;
        }

        $players = Player::all();
        
        if ($players->count() < 6) {
            $this->command->error('❌ Precisa de pelo menos 6 players.');
            return;
        }

        // Criar times para a partida em andamento
        $teamA = MatchTeam::firstOrCreate(
            [
                'match_id' => $inProgressMatch->id,
                'team_name' => 'team_a'
            ],
            [
                'team_color' => '#FF6B6B',
                'score' => 2,
            ]
        );

        $teamB = MatchTeam::firstOrCreate(
            [
                'match_id' => $inProgressMatch->id,
                'team_name' => 'team_b'
            ],
            [
                'team_color' => '#4ECDC4',
                'score' => 1,
            ]
        );

        // Distribuir jogadores nos times com estatísticas
        // Primeiro, verificar se os jogadores já estão na partida e apenas atualizar o team_id
        $teamAPlayerIds = [$players[0]->id, $players[2]->id, $players[4]->id];
        $teamBPlayerIds = [$players[1]->id, $players[3]->id, $players[5]->id];

        // Atualizar os participantes existentes com team_id e estatísticas
        foreach ($teamAPlayerIds as $index => $playerId) {
            $inProgressMatch->participants()->updateExistingPivot($playerId, [
                'team_id' => $teamA->id,
                'goals_scored' => [2, 0, 0][$index],
                'assists_made' => [0, 1, 0][$index],
                'tackles_made' => [1, 2, 1][$index],
                'defenses_made' => [0, 0, 1][$index],
            ]);
        }

        foreach ($teamBPlayerIds as $index => $playerId) {
            $inProgressMatch->participants()->updateExistingPivot($playerId, [
                'team_id' => $teamB->id,
                'goals_scored' => [1, 0, 0][$index],
                'assists_made' => [0, 0, 0][$index],
                'tackles_made' => [0, 1, 2][$index],
                'defenses_made' => [0, 1, 0][$index],
            ]);
        }

        // Criar times para partida finalizada
        $finishedMatch = FootballMatch::where('code', 'FINI01')->first();
        
        if ($finishedMatch) {
            $finishedTeamA = MatchTeam::firstOrCreate(
                [
                    'match_id' => $finishedMatch->id,
                    'team_name' => 'team_a'
                ],
                [
                    'team_color' => '#FF6B6B',
                    'score' => 4,
                ]
            );

            $finishedTeamB = MatchTeam::firstOrCreate(
                [
                    'match_id' => $finishedMatch->id,
                    'team_name' => 'team_b'
                ],
                [
                    'team_color' => '#4ECDC4',
                    'score' => 2,
                ]
            );

            // Definir time vencedor
            $finishedMatch->update(['winning_team_id' => $finishedTeamA->id]);

            // Atualizar participantes existentes com team_id e estatísticas
            $finishedTeamAPlayerIds = [$players[0]->id, $players[2]->id, $players[4]->id, $players[6]->id];
            $finishedTeamBPlayerIds = [$players[1]->id, $players[3]->id, $players[5]->id, $players[7]->id];

            foreach ($finishedTeamAPlayerIds as $index => $playerId) {
                $finishedMatch->participants()->updateExistingPivot($playerId, [
                    'team_id' => $finishedTeamA->id,
                    'goals_scored' => [2, 1, 1, 0][$index],
                    'assists_made' => [1, 2, 0, 1][$index],
                    'tackles_made' => [2, 1, 3, 1][$index],
                    'defenses_made' => [0, 0, 2, 0][$index],
                ]);
            }

            foreach ($finishedTeamBPlayerIds as $index => $playerId) {
                $finishedMatch->participants()->updateExistingPivot($playerId, [
                    'team_id' => $finishedTeamB->id,
                    'goals_scored' => [1, 1, 0, 0][$index],
                    'assists_made' => [1, 0, 0, 1][$index],
                    'tackles_made' => [2, 4, 2, 0][$index],
                    'defenses_made' => [0, 1, 3, 5][$index],
                ]);
            }
        }

        $this->command->info('✅ Times criados:');
        $this->command->info('   - Time A (Vermelhos): ' . $teamA->players->count() . ' jogadores');
        $this->command->info('   - Time B (Azuis): ' . $teamB->players->count() . ' jogadores');
        
        if ($finishedMatch) {
            $this->command->info('   - Times da partida finalizada configurados');
        }
    }
}

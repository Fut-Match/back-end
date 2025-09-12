<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\FootballMatch;

class MatchSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $players = Player::all();
        
        if ($players->count() < 6) {
            $this->command->error('❌ Precisa de pelo menos 6 players. Execute PlayerSeeder primeiro.');
            return;
        }

        // Partida 1: Aguardando jogadores
        $waitingMatch = FootballMatch::firstOrCreate(
            ['code' => 'WAIT01'],
            [
                'admin_id' => $players[0]->id,
                'match_date' => now()->addDays(2)->format('Y-m-d'),
                'match_time' => '18:00:00',
                'location' => 'Campo Central do Clube',
                'players_count' => '5vs5',
                'end_mode' => 'both',
                'goal_limit' => 5,
                'time_limit' => 90,
                'status' => 'waiting',
            ]
        );

        // Adicionar participantes à partida de espera
        $waitingMatch->participants()->syncWithoutDetaching($players->take(4)->pluck('id'));

        // Partida 2: Em andamento
        $inProgressMatch = FootballMatch::firstOrCreate(
            ['code' => 'PROG01'],
            [
                'admin_id' => $players[1]->id,
                'match_date' => now()->format('Y-m-d'),
                'match_time' => '19:30:00',
                'location' => 'Quadra do Bairro Norte',
                'players_count' => '3vs3',
                'end_mode' => 'goals',
                'goal_limit' => 3,
                'status' => 'in_progress',
                'started_at' => now()->subMinutes(25),
                'current_minute' => 25,
                'is_paused' => false,
            ]
        );

        // Adicionar participantes à partida em andamento
        $inProgressMatch->participants()->syncWithoutDetaching($players->take(6)->pluck('id'));

        // Partida 3: Finalizada
        $finishedMatch = FootballMatch::firstOrCreate(
            ['code' => 'FINI01'],
            [
                'admin_id' => $players[2]->id,
                'match_date' => now()->subDays(1)->format('Y-m-d'),
                'match_time' => '20:00:00',
                'location' => 'Complexo Esportivo Sul',
                'players_count' => '5vs5',
                'end_mode' => 'time',
                'time_limit' => 60,
                'status' => 'finished',
                'started_at' => now()->subDays(1)->subHours(2),
                'finished_at' => now()->subDays(1)->subHour(),
                'current_minute' => 60,
                'is_paused' => false,
            ]
        );

        // Adicionar participantes à partida finalizada
        $finishedMatch->participants()->syncWithoutDetaching($players->pluck('id'));

        // Partida 4: Rápida (3vs3)
        $quickMatch = FootballMatch::firstOrCreate(
            ['code' => 'QUICK1'],
            [
                'admin_id' => $players[3]->id,
                'match_date' => now()->addDays(1)->format('Y-m-d'),
                'match_time' => '17:00:00',
                'location' => 'Quadra da Vila',
                'players_count' => '3vs3',
                'end_mode' => 'goals',
                'goal_limit' => 3,
                'status' => 'waiting',
            ]
        );

        $quickMatch->participants()->syncWithoutDetaching($players->take(3)->pluck('id'));

        // Partida 5: Torneio (6vs6)
        $tournamentMatch = FootballMatch::firstOrCreate(
            ['code' => 'TOUR01'],
            [
                'admin_id' => $players[4]->id,
                'match_date' => now()->addDays(3)->format('Y-m-d'),
                'match_time' => '14:00:00',
                'location' => 'Arena Esportiva Municipal',
                'players_count' => '6vs6',
                'end_mode' => 'time',
                'time_limit' => 120,
                'status' => 'waiting',
            ]
        );

        $tournamentMatch->participants()->syncWithoutDetaching($players->take(8)->pluck('id'));

        $this->command->info('✅ Partidas criadas:');
        $this->command->info('   - Aguardando jogadores: ' . $waitingMatch->code);
        $this->command->info('   - Em andamento: ' . $inProgressMatch->code);
        $this->command->info('   - Finalizada: ' . $finishedMatch->code);
        $this->command->info('   - Partida rápida: ' . $quickMatch->code);
        $this->command->info('   - Torneio: ' . $tournamentMatch->code);
    }
}

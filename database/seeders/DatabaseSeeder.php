<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Player;
use App\Models\FootballMatch;
use App\Models\MatchTeam;
use App\Models\MatchEvent;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando população do banco de dados...');
        $this->command->info('');

        // Executar seeders na ordem correta
        $this->call([
            UserSeeder::class,
            PlayerSeeder::class,
            MatchSeeder::class,
            MatchTeamSeeder::class,
            MatchEventSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Banco de dados populado com sucesso!');
        $this->command->info('📊 Resumo final:');
        $this->command->info('   - Usuários: ' . User::count());
        $this->command->info('   - Players: ' . Player::count());
        $this->command->info('   - Partidas: ' . FootballMatch::count());
        $this->command->info('   - Times: ' . MatchTeam::count());
        $this->command->info('   - Eventos: ' . MatchEvent::count());
        $this->command->info('');
        $this->command->info('🔑 Credenciais de teste:');
        $this->command->info('   📧 joao@exemplo.com | 🔐 12345678');
        $this->command->info('   📧 maria@exemplo.com | 🔐 12345678');
        $this->command->info('   📧 pedro@exemplo.com | 🔐 12345678');
        $this->command->info('');
        
        // Mostrar códigos das partidas
        $matches = FootballMatch::all();
        if ($matches->isNotEmpty()) {
            $this->command->info('🎮 Códigos das partidas:');
            foreach ($matches as $match) {
                $statusEmoji = [
                    'waiting' => '⏳',
                    'in_progress' => '⚽',
                    'finished' => '🏁',
                    'cancelled' => '❌'
                ];
                $emoji = $statusEmoji[$match->status] ?? '📋';
                $this->command->info("   {$emoji} {$match->code} - {$match->status} ({$match->location})");
            }
        }
    }
}

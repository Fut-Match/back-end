<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Player;
use App\Models\FootballMatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário principal de teste
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // Criar player para o usuário
        $player = Player::create([
            'user_id' => $user->id,
            'name' => 'João Silva',
            'nickname' => 'João10',
            'image' => null,
            'goals' => 25,
            'assists' => 15,
            'tackles' => 8,
            'mvps' => 3,
            'wins' => 12,
            'matches' => 20,
            'average_rating' => 8.5,
        ]);

        // Criar uma partida administrada pelo player
        $match = FootballMatch::create([
            'admin_id' => $player->id,
            'match_date' => now()->addDays(2)->format('Y-m-d'),
            'match_time' => '18:00',
            'location' => 'Campo do Botafogo - Vila Madalena',
            'players_count' => '5vs5',
            'end_mode' => 'both',
            'goal_limit' => 5,
            'time_limit' => 90,
            'status' => 'waiting',
        ]);

        // Criar usuários e players adicionais para demonstração
        $additionalUsers = User::factory(5)->create();
        
        foreach ($additionalUsers as $additionalUser) {
            $additionalPlayer = Player::create([
                'user_id' => $additionalUser->id,
                'name' => $additionalUser->name,
                'nickname' => fake()->userName(),
                'image' => null,
                'goals' => fake()->numberBetween(0, 50),
                'assists' => fake()->numberBetween(0, 30),
                'tackles' => fake()->numberBetween(0, 20),
                'mvps' => fake()->numberBetween(0, 10),
                'wins' => fake()->numberBetween(0, 25),
                'matches' => fake()->numberBetween(0, 40),
                'average_rating' => fake()->randomFloat(2, 5, 10),
            ]);

            // Alguns players participam da partida principal
            if (fake()->boolean(60)) { // 60% de chance
                $match->addParticipant($additionalPlayer);
            }
        }

        // Criar mais algumas partidas de exemplo
        for ($i = 0; $i < 3; $i++) {
            $randomPlayer = Player::inRandomOrder()->first();
            
            FootballMatch::create([
                'admin_id' => $randomPlayer->id,
                'match_date' => now()->addDays(fake()->numberBetween(1, 7))->format('Y-m-d'),
                'match_time' => fake()->time('H:i'),
                'location' => fake()->randomElement([
                    'Campo da Vila Olímpica',
                    'Quadra do Clube Esportivo',
                    'Arena Central',
                    'Campo do Parque Municipal',
                    'Complexo Esportivo Norte'
                ]),
                'players_count' => fake()->randomElement(['3vs3', '5vs5', '6vs6']),
                'end_mode' => fake()->randomElement(['goals', 'time', 'both']),
                'goal_limit' => fake()->numberBetween(3, 10),
                'time_limit' => fake()->randomElement([45, 60, 90, 120]),
                'status' => fake()->randomElement(['waiting', 'waiting', 'waiting', 'in_progress']), // Mais chances de estar waiting
            ]);
        }

        $this->command->info('Seeder executado com sucesso!');
        $this->command->info('Usuário de teste criado:');
        $this->command->info('Email: joao@exemplo.com');
        $this->command->info('Senha: 12345678');
        $this->command->info('Player ID: ' . $player->id);
        $this->command->info('Partida criada com código: ' . $match->code);
    }
}

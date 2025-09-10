<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Player;

class PlayerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->error('❌ Nenhum usuário encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $playerData = [
            ['nickname' => 'João10', 'position' => 'atacante', 'goals' => 25, 'assists' => 15, 'tackles' => 8, 'mvps' => 3, 'wins' => 12, 'matches' => 20, 'average_rating' => 8.5],
            ['nickname' => 'Mari7', 'position' => 'meio-campo', 'goals' => 18, 'assists' => 22, 'tackles' => 12, 'mvps' => 5, 'wins' => 15, 'matches' => 25, 'average_rating' => 9.1],
            ['nickname' => 'PedroCosta', 'position' => 'defensor', 'goals' => 5, 'assists' => 8, 'tackles' => 25, 'mvps' => 1, 'wins' => 8, 'matches' => 15, 'average_rating' => 7.8],
            ['nickname' => 'AninhA', 'position' => 'goleiro', 'goals' => 0, 'assists' => 1, 'tackles' => 15, 'mvps' => 2, 'wins' => 6, 'matches' => 12, 'average_rating' => 6.9],
            ['nickname' => 'Carlão', 'position' => 'atacante', 'goals' => 32, 'assists' => 10, 'tackles' => 6, 'mvps' => 4, 'wins' => 18, 'matches' => 28, 'average_rating' => 8.9],
            ['nickname' => 'Lu9', 'position' => 'meio-campo', 'goals' => 12, 'assists' => 25, 'tackles' => 18, 'mvps' => 3, 'wins' => 14, 'matches' => 22, 'average_rating' => 8.3],
            ['nickname' => 'Beto99', 'position' => 'defensor', 'goals' => 3, 'assists' => 5, 'tackles' => 30, 'mvps' => 1, 'wins' => 10, 'matches' => 18, 'average_rating' => 7.5],
            ['nickname' => 'Fer10', 'position' => 'atacante', 'goals' => 20, 'assists' => 12, 'tackles' => 8, 'mvps' => 2, 'wins' => 11, 'matches' => 19, 'average_rating' => 8.2],
        ];

        $users->each(function ($user, $index) use ($playerData) {
            $data = $playerData[$index] ?? $playerData[0]; // fallback para o primeiro se não houver dados suficientes
            
            Player::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'nickname' => $data['nickname'],
                    'image' => null,
                    'position' => $data['position'],
                    'goals' => $data['goals'],
                    'assists' => $data['assists'],
                    'tackles' => $data['tackles'],
                    'mvps' => $data['mvps'],
                    'wins' => $data['wins'],
                    'matches' => $data['matches'],
                    'average_rating' => $data['average_rating'],
                ]
            );
        });

        $this->command->info('✅ Players criados: ' . Player::count());
    }
}

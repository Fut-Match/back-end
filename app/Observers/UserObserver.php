<?php

namespace App\Observers;

use App\Models\Player;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Cria automaticamente um jogador quando um usuário é criado
     */
    public function created(User $user): void
    {
        Player::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'image' => null,
            'nickname' => null,
            'goals' => 0,
            'assists' => 0,
            'tackles' => 0,
            'mvps' => 0,
            'wins' => 0,
            'matches' => 0,
            'average_rating' => 0.00,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Atualiza o nome do jogador se o nome do usuário for alterado
        if ($user->wasChanged('name') && $user->player) {
            $user->player->update(['name' => $user->name]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // O jogador será deletado automaticamente devido ao cascade na foreign key
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}

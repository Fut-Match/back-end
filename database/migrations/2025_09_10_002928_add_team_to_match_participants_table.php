<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('match_teams')->onDelete('cascade');
            $table->integer('goals_scored')->default(0); // Gols marcados nesta partida
            $table->integer('assists_made')->default(0); // Assistências nesta partida
            $table->integer('tackles_made')->default(0); // Desarmes nesta partida
            $table->integer('defenses_made')->default(0); // Defesas nesta partida
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['team_id', 'goals_scored', 'assists_made', 'tackles_made', 'defenses_made']);
        });
    }
};

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
        Schema::table('matches', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable(); // Quando a partida iniciou
            $table->timestamp('finished_at')->nullable(); // Quando a partida terminou
            $table->integer('current_minute')->default(0); // Minuto atual da partida
            $table->boolean('is_paused')->default(false); // Se a partida está pausada
            $table->foreignId('winning_team_id')->nullable()->constrained('match_teams')->onDelete('set null'); // Time vencedor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['winning_team_id']);
            $table->dropColumn(['started_at', 'finished_at', 'current_minute', 'is_paused', 'winning_team_id']);
        });
    }
};

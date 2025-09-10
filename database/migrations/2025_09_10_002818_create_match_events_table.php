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
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('match_teams')->onDelete('cascade');
            $table->enum('event_type', ['goal', 'assist', 'tackle', 'defense']); // Tipos de eventos
            $table->integer('minute')->nullable(); // Minuto do evento (se houver cronômetro)
            $table->text('description')->nullable(); // Descrição adicional do evento
            $table->timestamps();

            // Índices para performance
            $table->index(['match_id', 'event_type']);
            $table->index(['player_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};

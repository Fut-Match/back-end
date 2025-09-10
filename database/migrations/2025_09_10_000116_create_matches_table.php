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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique(); // Código único para entrada na partida
            $table->foreignId('admin_id')->constrained('players')->onDelete('cascade'); // Administrador (Player)
            $table->date('match_date'); // Data da partida
            $table->time('match_time'); // Horário da partida
            $table->string('location'); // Localização
            $table->enum('players_count', ['3vs3', '5vs5', '6vs6']); // Quantidade de jogadores
            $table->enum('end_mode', ['goals', 'time', 'both']); // Modo de término
            $table->integer('goal_limit')->nullable(); // Limite de gols (se aplicável)
            $table->integer('time_limit')->nullable(); // Limite de tempo em minutos (se aplicável)
            $table->enum('status', ['waiting', 'in_progress', 'finished', 'cancelled'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

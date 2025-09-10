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
        Schema::create('match_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->enum('team_name', ['team_a', 'team_b']); // Times A e B
            $table->string('team_color', 50)->nullable(); // Cor do time (opcional)
            $table->integer('score')->default(0); // Placar do time
            $table->timestamps();

            // Um match pode ter apenas 2 teams (A e B)
            $table->unique(['match_id', 'team_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_teams');
    }
};

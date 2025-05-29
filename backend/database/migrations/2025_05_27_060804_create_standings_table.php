<?php
// 9. database/migrations/XXXX_XX_XX_XXXXXX_create_standings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('group_name', 10)->nullable();
            $table->integer('played')->default(0);
            $table->integer('won')->default(0);
            $table->integer('drawn')->default(0);
            $table->integer('lost')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->integer('points')->default(0);
            $table->integer('position')->nullable();
            $table->timestamp('updated_at');

            $table->unique(['tournament_id', 'team_id'], 'tournament_team_standings');
            $table->index('tournament_id');
            $table->index(['points', 'goal_difference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};

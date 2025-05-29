<?php

// 7. database/migrations/XXXX_XX_XX_XXXXXX_create_game_matches_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('home_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('referee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('round', 50)->nullable()->comment('group_stage, round_16, quarter_final, etc.');
            $table->datetime('match_date');
            $table->string('venue')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'postponed', 'cancelled'])
                ->default('scheduled');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('extra_time_home')->nullable();
            $table->integer('extra_time_away')->nullable();
            $table->integer('penalty_home')->nullable();
            $table->integer('penalty_away')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tournament_id');
            $table->index('match_date');
            $table->index(['home_team_id', 'away_team_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};

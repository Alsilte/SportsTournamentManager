<?php

// 6. database/migrations/XXXX_XX_XX_XXXXXX_create_team_players_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->integer('jersey_number');
            $table->string('position', 50)->nullable();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'jersey_number'], 'team_player_jersey');
            $table->unique(['team_id', 'player_id'], 'team_player_unique');
            $table->index('team_id');
            $table->index('player_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_players');
    }
};

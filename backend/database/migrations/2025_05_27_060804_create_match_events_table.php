<?php

// 8. database/migrations/XXXX_XX_XX_XXXXXX_create_match_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_match_id')->constrained('game_matches')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('player_id')->nullable()->constrained('players')->onDelete('cascade');
            $table->enum('event_type', ['goal', 'yellow_card', 'red_card', 'substitution', 'own_goal', 'assist', 'substitution_out', 'substitution_in']);
            $table->integer('minute');
            $table->integer('additional_time')->default(0);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('game_match_id');
            $table->index('event_type');
            $table->index('minute');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};

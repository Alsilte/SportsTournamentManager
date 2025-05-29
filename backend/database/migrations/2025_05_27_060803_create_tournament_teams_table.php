<?php

// 5. database/migrations/XXXX_XX_XX_XXXXXX_create_tournament_teams_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->datetime('registration_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('group_name', 10)->nullable()->comment('For group stage tournaments');
            $table->integer('seed')->nullable()->comment('Tournament seeding');
            $table->timestamps();

            $table->unique(['tournament_id', 'team_id'], 'tournament_team_unique');
            $table->index('tournament_id');
            $table->index('team_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_teams');
    }
};

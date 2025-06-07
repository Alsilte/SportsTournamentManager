<?php

// 2. database/migrations/XXXX_XX_XX_XXXXXX_create_tournaments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sport_type', 100);
            $table->enum('tournament_type', ['league', 'knockout', 'group_knockout', 'group_stage']);
            $table->enum('status', ['draft', 'registration_open', 'registration_closed', 'in_progress', 'completed', 'cancelled'])
                ->default('draft');
            $table->integer('max_teams');
            $table->datetime('registration_start');
            $table->datetime('registration_end');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->decimal('prize_pool', 10, 2)->nullable();
            $table->text('rules')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};

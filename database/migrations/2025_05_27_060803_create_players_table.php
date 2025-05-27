<?php
// 4. database/migrations/XXXX_XX_XX_XXXXXX_create_players_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->integer('jersey_number')->nullable();
            $table->string('position', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->decimal('height', 5, 2)->nullable()->comment('Height in cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->enum('preferred_foot', ['left', 'right', 'both'])->nullable();
            $table->text('biography')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};

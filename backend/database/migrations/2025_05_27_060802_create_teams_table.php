<?php

/**
 * Create Teams Table Migration
 * 
 * Creates the teams table with all necessary fields for team management
 * including contact information, manager assignment, and venue details.
 * 
 * Author: Alejandro Silla Tejero
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 10)->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('contact_email');
            $table->string('contact_phone', 20)->nullable();
            $table->integer('founded_year')->nullable();
            $table->string('home_venue')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};

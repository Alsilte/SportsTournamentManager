<?php

/**
 * Database Seeder
 * 
 * Main seeder class that orchestrates the execution of all database seeders
 * in the correct order to populate the database with sample data for development
 * and testing purposes.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlayerSeeder::class,
            TeamSeeder::class,
            TeamPlayerSeeder::class,
            TournamentSeeder::class,
            TournamentTeamSeeder::class,
            GameMatchSeeder::class,
            MatchEventSeeder::class,
            StandingSeeder::class,
        ]);
    }
}

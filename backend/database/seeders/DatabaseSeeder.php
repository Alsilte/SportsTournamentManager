<?php
// database/seeders/DatabaseSeeder.php

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
            PlayerSeeder::class,        // Después de User
            TeamSeeder::class,          // Después de User
            TeamPlayerSeeder::class,    // Después de Team y Player
            TournamentSeeder::class,    // Después de User
            TournamentTeamSeeder::class, // Después de Tournament y Team
            GameMatchSeeder::class,     // Después de Tournament y Team
            MatchEventSeeder::class,    // Después de GameMatch
            StandingSeeder::class,      // Al final para calcular standings
        ]);
    }
}

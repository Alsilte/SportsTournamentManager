<?php

/**
 * Tournament Team Seeder
 * 
 * Seeds the database with team registrations for tournaments.
 * Creates the relationship between teams and tournaments with registration
 * dates and status information for comprehensive testing.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\TournamentTeam;
use Carbon\Carbon;

class TournamentTeamSeeder extends Seeder
{
  public function run(): void
  {
    $tournaments = Tournament::all();
    $teams = Team::all();

    foreach ($tournaments as $tournament) {
      // Registrar todos los equipos en todos los torneos
      foreach ($teams as $team) {
        TournamentTeam::create([
          'tournament_id' => $tournament->id,
          'team_id' => $team->id,
          'registration_date' => Carbon::now()->subDays(rand(5, 30)),
          'status' => 'approved',
          'seed' => rand(1, 16),
        ]);
      }
    }
  }
}

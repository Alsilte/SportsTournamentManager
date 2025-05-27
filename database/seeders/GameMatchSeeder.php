<?php
// database/seeders/GameMatchSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class GameMatchSeeder extends Seeder
{
  public function run(): void
  {
    $tournament = Tournament::where('status', 'in_progress')->first();
    $teams = Team::all();
    $referee = User::where('role', 'referee')->first();

    // Crear partidos entre equipos (round robin parcial)
    $matches = [
      // Partidos completados
      [
        'home_team_id' => $teams[0]->id,
        'away_team_id' => $teams[1]->id,
        'match_date' => Carbon::now()->subDays(10),
        'venue' => 'Stadium A',
        'round' => 'group_stage',
        'status' => 'completed',
        'home_score' => 2,
        'away_score' => 1,
        'winner_team_id' => $teams[0]->id,
      ],
      [
        'home_team_id' => $teams[2]->id,
        'away_team_id' => $teams[3]->id,
        'match_date' => Carbon::now()->subDays(9),
        'venue' => 'Stadium B',
        'round' => 'group_stage',
        'status' => 'completed',
        'home_score' => 1,
        'away_score' => 3,
        'winner_team_id' => $teams[3]->id,
      ],
      [
        'home_team_id' => $teams[0]->id,
        'away_team_id' => $teams[2]->id,
        'match_date' => Carbon::now()->subDays(5),
        'venue' => 'Stadium C',
        'round' => 'group_stage',
        'status' => 'completed',
        'home_score' => 0,
        'away_score' => 2,
        'winner_team_id' => $teams[2]->id,
      ],
      // Partidos programados
      [
        'home_team_id' => $teams[1]->id,
        'away_team_id' => $teams[3]->id,
        'match_date' => Carbon::now()->addDays(5),
        'venue' => 'Stadium D',
        'round' => 'group_stage',
        'status' => 'scheduled',
      ],
      [
        'home_team_id' => $teams[0]->id,
        'away_team_id' => $teams[3]->id,
        'match_date' => Carbon::now()->addDays(10),
        'venue' => 'Stadium A',
        'round' => 'quarter_final',
        'status' => 'scheduled',
      ]
    ];

    foreach ($matches as $match) {
      GameMatch::create([
        'tournament_id' => $tournament->id,
        'referee_id' => $referee->id,
        ...$match
      ]);
    }
  }
}

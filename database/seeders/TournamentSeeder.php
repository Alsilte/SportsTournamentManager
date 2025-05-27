<?php

// database/seeders/TournamentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;

class TournamentSeeder extends Seeder
{
  public function run(): void
  {
    $admin = User::where('role', 'admin')->first();

    $tournaments = [
      [
        'name' => 'Champions League 2024',
        'description' => 'The ultimate European football competition',
        'sport_type' => 'Football',
        'tournament_type' => 'knockout',
        'status' => 'in_progress',
        'max_teams' => 16,
        'registration_start' => Carbon::now()->subDays(60),
        'registration_end' => Carbon::now()->subDays(30),
        'start_date' => Carbon::now()->subDays(15),
        'end_date' => Carbon::now()->addDays(45),
        'location' => 'Europe',
        'prize_pool' => 50000.00,
        'rules' => 'Standard FIFA rules apply',
      ],
      [
        'name' => 'Summer League 2024',
        'description' => 'Local summer football league',
        'sport_type' => 'Football',
        'tournament_type' => 'league',
        'status' => 'registration_open',
        'max_teams' => 8,
        'registration_start' => Carbon::now(),
        'registration_end' => Carbon::now()->addDays(20),
        'start_date' => Carbon::now()->addDays(35),
        'end_date' => Carbon::now()->addDays(120),
        'location' => 'Local Stadium',
        'prize_pool' => 10000.00,
        'rules' => 'Standard league format',
      ]
    ];

    foreach ($tournaments as $tournament) {
      Tournament::create([
        ...$tournament,
        'created_by' => $admin->id,
      ]);
    }
  }
}

<?php

// database/seeders/PlayerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\User;
use Carbon\Carbon;

class PlayerSeeder extends Seeder
{
  public function run(): void
  {
    $playerUsers = User::where('role', 'player')->get();

    $positions = ['Forward', 'Midfielder', 'Defender', 'Goalkeeper'];
    $nationalities = ['Spain', 'Argentina', 'Brazil', 'France', 'England', 'Portugal', 'Germany', 'Italy'];
    $preferredFeet = ['left', 'right', 'both'];

    foreach ($playerUsers as $user) {
      Player::create([
        'user_id' => $user->id,
        'jersey_number' => rand(1, 99),
        'position' => $positions[array_rand($positions)],
        'date_of_birth' => Carbon::now()->subYears(rand(18, 35))->subDays(rand(1, 365)),
        'nationality' => $nationalities[array_rand($nationalities)],
        'height' => rand(160, 195),
        'weight' => rand(60, 90),
        'preferred_foot' => $preferredFeet[array_rand($preferredFeet)],
        'biography' => 'Professional football player with extensive experience.',
      ]);
    }
  }
}

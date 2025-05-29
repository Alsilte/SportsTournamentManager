<?php
// database/seeders/TeamSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;

class TeamSeeder extends Seeder
{
  public function run(): void
  {
    $managers = User::where('role', 'team_manager')->get();

    $teams = [
      [
        'name' => 'Real Madrid',
        'short_name' => 'RMA',
        'description' => 'The most successful club in European football',
        'contact_email' => 'contact@realmadrid.com',
        'founded_year' => 1902,
        'home_venue' => 'Santiago Bernabéu',
      ],
      [
        'name' => 'FC Barcelona',
        'short_name' => 'BAR',
        'description' => 'More than a club',
        'contact_email' => 'contact@barcelona.com',
        'founded_year' => 1899,
        'home_venue' => 'Camp Nou',
      ],
      [
        'name' => 'Manchester United',
        'short_name' => 'MUN',
        'description' => 'The Red Devils',
        'contact_email' => 'contact@manchester.com',
        'founded_year' => 1878,
        'home_venue' => 'Old Trafford',
      ],
      [
        'name' => 'Arsenal FC',
        'short_name' => 'ARS',
        'description' => 'The Gunners',
        'contact_email' => 'contact@arsenal.com',
        'founded_year' => 1886,
        'home_venue' => 'Emirates Stadium',
      ]
    ];

    foreach ($teams as $index => $team) {
      Team::create([
        ...$team,
        'manager_id' => $managers[$index]->id,
        'contact_phone' => '+' . rand(1000000000, 9999999999),
        'is_active' => true,
      ]);
    }
  }
}

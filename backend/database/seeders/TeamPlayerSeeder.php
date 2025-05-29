<?php

// database/seeders/TeamPlayerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamPlayer;
use App\Models\Team;
use App\Models\Player;
use Carbon\Carbon;

class TeamPlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();
        $players = Player::with('user')->get();

        $positions = ['Forward', 'Midfielder', 'Defender', 'Goalkeeper'];

        // Asignar 6 jugadores por equipo
        foreach ($teams as $teamIndex => $team) {
            // Tomar 6 jugadores para este equipo
            $teamPlayers = $players->skip($teamIndex * 6)->take(6);

            foreach ($teamPlayers as $index => $player) {
                TeamPlayer::create([
                    'team_id' => $team->id,
                    'player_id' => $player->id,
                    'jersey_number' => $index + 1, // Números del 1 al 6
                    'position' => $positions[array_rand($positions)],
                    'is_captain' => $index === 0, // El primer jugador es capitán
                    'is_active' => true,
                    'joined_date' => Carbon::now()->subDays(rand(30, 365)),
                ]);
            }
        }
    }
}

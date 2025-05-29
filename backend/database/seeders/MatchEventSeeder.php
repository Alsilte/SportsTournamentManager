<?php

// database/seeders/MatchEventSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MatchEvent;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\TeamPlayer;

class MatchEventSeeder extends Seeder
{
    public function run(): void
    {
        $completedMatches = GameMatch::where('status', 'completed')->get();

        foreach ($completedMatches as $match) {
            // Obtener jugadores de ambos equipos
            $homePlayers = TeamPlayer::where('team_id', $match->home_team_id)
                ->where('is_active', true)
                ->with('player')
                ->get();

            $awayPlayers = TeamPlayer::where('team_id', $match->away_team_id)
                ->where('is_active', true)
                ->with('player')
                ->get();

            // Crear eventos de gol basados en el marcador
            $homeGoals = $match->home_score ?? 0;
            $awayGoals = $match->away_score ?? 0;

            // Goles del equipo local
            for ($i = 0; $i < $homeGoals; $i++) {
                $player = $homePlayers->random();
                MatchEvent::create([
                    'game_match_id' => $match->id,
                    'team_id' => $match->home_team_id,
                    'player_id' => $player->player->id,
                    'event_type' => 'goal',
                    'minute' => rand(5, 90),
                    'additional_time' => rand(0, 5),
                    'description' => 'Goal scored',
                ]);
            }

            // Goles del equipo visitante
            for ($i = 0; $i < $awayGoals; $i++) {
                $player = $awayPlayers->random();
                MatchEvent::create([
                    'game_match_id' => $match->id,
                    'team_id' => $match->away_team_id,
                    'player_id' => $player->player->id,
                    'event_type' => 'goal',
                    'minute' => rand(5, 90),
                    'additional_time' => rand(0, 5),
                    'description' => 'Goal scored',
                ]);
            }

            // Agregar algunas tarjetas aleatorias
            if (rand(1, 3) === 1) { // 33% de probabilidad
                $allPlayers = $homePlayers->merge($awayPlayers);
                $player = $allPlayers->random();

                MatchEvent::create([
                    'game_match_id' => $match->id,
                    'team_id' => $player->team_id,
                    'player_id' => $player->player->id,
                    'event_type' => 'yellow_card',
                    'minute' => rand(10, 85),
                    'additional_time' => 0,
                    'description' => 'Yellow card for unsporting behavior',
                ]);
            }
        }
    }
}

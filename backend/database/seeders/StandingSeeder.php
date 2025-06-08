<?php

/**
 * Standing Seeder
 * 
 * Seeds the database with calculated standings for tournaments.
 * Processes match results to generate accurate team standings including
 * points, wins, draws, losses, and goal statistics.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\GameMatch;
use App\Models\Team;

class StandingSeeder extends Seeder
{
    public function run(): void
    {
        $tournaments = Tournament::with(['teams', 'matches'])->get();

        foreach ($tournaments as $tournament) {
            // Crear standings para todos los equipos registrados
            foreach ($tournament->teams as $team) {
                $standing = Standing::create([
                    'tournament_id' => $tournament->id,
                    'team_id' => $team->id,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'goal_difference' => 0,
                    'points' => 0,
                ]);

                // Calcular estadísticas basadas en partidos completados
                $completedMatches = $tournament->matches()
                    ->where('status', 'completed')
                    ->where(function ($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                            ->orWhere('away_team_id', $team->id);
                    })->get();

                foreach ($completedMatches as $match) {
                    $isHome = $match->home_team_id == $team->id;
                    $standing->updateFromMatch($match, $isHome);
                }

                $standing->save();
            }

            // Actualizar posiciones
            $standings = Standing::where('tournament_id', $tournament->id)
                ->orderBy('points', 'desc')
                ->orderBy('goal_difference', 'desc')
                ->orderBy('goals_for', 'desc')
                ->get();

            foreach ($standings as $index => $standing) {
                $standing->update(['position' => $index + 1]);
            }
        }
    }
}

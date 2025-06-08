<?php

/**
 * Game Match Seeder
 * 
 * Seeds the database with sample matches for tournaments.
 * Creates matches with different statuses, scores, and referee assignments
 * to test match management and tournament progression features.
 * 
 * Author: Alejandro Silla Tejero
 */

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
        $tournaments = Tournament::whereIn('status', ['in_progress', 'completed'])->get();
        $teams = Team::all();
        $referees = User::where('role', 'referee')->get();

        foreach ($tournaments as $tournament) {
            if ($tournament->name === 'Copa Elite Champions 2024') {
                $this->seedEliteChampionsMatches($tournament, $teams, $referees);
            } elseif ($tournament->name === 'Liga Profesional Primavera') {
                $this->seedLeagueMatches($tournament, $teams, $referees);
            } elseif ($tournament->name === 'Copa Invernal 2024') {
                $this->seedCompletedTournamentMatches($tournament, $teams, $referees);
            }
        }
    }

    private function seedEliteChampionsMatches($tournament, $teams, $referees)
    {
        $venues = [
            'Santiago Bernabéu, Madrid',
            'Camp Nou, Barcelona', 
            'Etihad Stadium, Manchester',
            'Anfield, Liverpool',
            'Allianz Arena, Munich',
            'San Siro, Milan',
            'Parc des Princes, Paris',
            'Stamford Bridge, London'
        ];

        $matches = [
            // Octavos de final completados
            [
                'home_team_id' => $teams[0]->id, // Man City
                'away_team_id' => $teams[7]->id, // Chelsea
                'match_date' => Carbon::now()->subDays(25),
                'venue' => $venues[0],
                'round' => 'round_of_16',
                'status' => 'completed',
                'home_score' => 3,
                'away_score' => 1,
                'winner_team_id' => $teams[0]->id,
            ],
            [
                'home_team_id' => $teams[1]->id, // Liverpool
                'away_team_id' => $teams[6]->id, // Tottenham
                'match_date' => Carbon::now()->subDays(24),
                'venue' => $venues[1],
                'round' => 'round_of_16',
                'status' => 'completed',
                'home_score' => 2,
                'away_score' => 0,
                'winner_team_id' => $teams[1]->id,
            ],
            [
                'home_team_id' => $teams[3]->id, // Real Madrid
                'away_team_id' => $teams[5]->id, // Man United
                'match_date' => Carbon::now()->subDays(23),
                'venue' => $venues[2],
                'round' => 'round_of_16',
                'status' => 'completed',
                'home_score' => 4,
                'away_score' => 2,
                'winner_team_id' => $teams[3]->id,
            ],
            [
                'home_team_id' => $teams[4]->id, // Barcelona
                'away_team_id' => $teams[2]->id, // Atlético
                'match_date' => Carbon::now()->subDays(22),
                'venue' => $venues[3],
                'round' => 'round_of_16',
                'status' => 'completed',
                'home_score' => 1,
                'away_score' => 2,
                'winner_team_id' => $teams[2]->id,
            ],

            // Cuartos de final (algunos completados, otros programados)
            [
                'home_team_id' => $teams[0]->id, // Man City
                'away_team_id' => $teams[1]->id, // Liverpool
                'match_date' => Carbon::now()->subDays(10),
                'venue' => $venues[4],
                'round' => 'quarter_final',
                'status' => 'completed',
                'home_score' => 2,
                'away_score' => 3,
                'winner_team_id' => $teams[1]->id,
            ],
            [
                'home_team_id' => $teams[3]->id, // Real Madrid
                'away_team_id' => $teams[2]->id, // Atlético
                'match_date' => Carbon::now()->subDays(9),
                'venue' => $venues[5],
                'round' => 'quarter_final',
                'status' => 'completed',
                'home_score' => 3,
                'away_score' => 3,
                'winner_team_id' => $teams[3]->id, // Ganó en penales
            ],

            // Semifinales programadas
            [
                'home_team_id' => $teams[1]->id, // Liverpool
                'away_team_id' => $teams[3]->id, // Real Madrid
                'match_date' => Carbon::now()->addDays(15),
                'venue' => $venues[6],
                'round' => 'semi_final',
                'status' => 'scheduled',
            ],

            // Final programada
            [
                'home_team_id' => null, // TBD
                'away_team_id' => null, // TBD
                'match_date' => Carbon::now()->addDays(45),
                'venue' => $venues[7],
                'round' => 'final',
                'status' => 'scheduled',
            ]
        ];

        foreach ($matches as $match) {
            if ($match['home_team_id'] && $match['away_team_id']) {
                GameMatch::create([
                    'tournament_id' => $tournament->id,
                    'referee_id' => $referees->random()->id,
                    ...$match
                ]);
            }
        }
    }

    private function seedLeagueMatches($tournament, $teams, $referees)
    {
        $venues = [
            'Estadio Central',
            'Campo Municipal Norte',
            'Polideportivo Sur',
            'Arena Deportiva Este'
        ];

        // Crear jornadas de liga (algunos partidos completados, otros programados)
        $leagueTeams = $teams->take(6); // 6 equipos en la liga
        $matchesData = [];

        // Jornada 1-3 (completadas)
        $completedMatches = [
            [$leagueTeams[0]->id, $leagueTeams[1]->id, 2, 1, -20],
            [$leagueTeams[2]->id, $leagueTeams[3]->id, 0, 3, -20],
            [$leagueTeams[4]->id, $leagueTeams[5]->id, 1, 1, -20],
            
            [$leagueTeams[1]->id, $leagueTeams[2]->id, 2, 2, -15],
            [$leagueTeams[3]->id, $leagueTeams[4]->id, 1, 0, -15],
            [$leagueTeams[5]->id, $leagueTeams[0]->id, 0, 2, -15],
            
            [$leagueTeams[0]->id, $leagueTeams[3]->id, 3, 1, -10],
            [$leagueTeams[1]->id, $leagueTeams[4]->id, 1, 2, -10],
            [$leagueTeams[2]->id, $leagueTeams[5]->id, 2, 0, -10],
        ];

        foreach ($completedMatches as $match) {
            $winnerId = null;
            if ($match[2] > $match[3]) $winnerId = $match[0];
            elseif ($match[3] > $match[2]) $winnerId = $match[1];

            GameMatch::create([
                'tournament_id' => $tournament->id,
                'home_team_id' => $match[0],
                'away_team_id' => $match[1],
                'home_score' => $match[2],
                'away_score' => $match[3],
                'winner_team_id' => $winnerId,
                'match_date' => Carbon::now()->addDays($match[4]),
                'venue' => $venues[array_rand($venues)],
                'round' => 'group_stage',
                'status' => 'completed',
                'referee_id' => $referees->random()->id,
            ]);
        }

        // Jornadas futuras (programadas)
        $upcomingMatches = [
            [$leagueTeams[4]->id, $leagueTeams[0]->id, 5],
            [$leagueTeams[3]->id, $leagueTeams[1]->id, 5],
            [$leagueTeams[5]->id, $leagueTeams[2]->id, 5],
            
            [$leagueTeams[0]->id, $leagueTeams[2]->id, 12],
            [$leagueTeams[1]->id, $leagueTeams[5]->id, 12],
            [$leagueTeams[4]->id, $leagueTeams[3]->id, 12],
        ];

        foreach ($upcomingMatches as $match) {
            GameMatch::create([
                'tournament_id' => $tournament->id,
                'home_team_id' => $match[0],
                'away_team_id' => $match[1],
                'match_date' => Carbon::now()->addDays($match[2]),
                'venue' => $venues[array_rand($venues)],
                'round' => 'group_stage',
                'status' => 'scheduled',
                'referee_id' => $referees->random()->id,
            ]);
        }
    }

    private function seedCompletedTournamentMatches($tournament, $teams, $referees)
    {
        $venues = ['Estadio de Invierno', 'Arena Central', 'Campo Norte'];
        
        // Torneo completado con todas las fases
        $matches = [
            // Cuartos de final
            [$teams[0]->id, $teams[7]->id, 2, 1, $teams[0]->id, -50, 'quarter_final'],
            [$teams[1]->id, $teams[6]->id, 3, 0, $teams[1]->id, -49, 'quarter_final'],
            [$teams[2]->id, $teams[5]->id, 1, 2, $teams[5]->id, -48, 'quarter_final'],
            [$teams[3]->id, $teams[4]->id, 0, 1, $teams[4]->id, -47, 'quarter_final'],
            
            // Semifinales
            [$teams[0]->id, $teams[1]->id, 1, 3, $teams[1]->id, -35, 'semi_final'],
            [$teams[5]->id, $teams[4]->id, 2, 1, $teams[5]->id, -34, 'semi_final'],
            
            // Final
            [$teams[1]->id, $teams[5]->id, 2, 0, $teams[1]->id, -20, 'final'],
        ];

        foreach ($matches as $match) {
            GameMatch::create([
                'tournament_id' => $tournament->id,
                'home_team_id' => $match[0],
                'away_team_id' => $match[1],
                'home_score' => $match[2],
                'away_score' => $match[3],
                'winner_team_id' => $match[4],
                'match_date' => Carbon::now()->addDays($match[5]),
                'venue' => $venues[array_rand($venues)],
                'round' => $match[6],
                'status' => 'completed',
                'referee_id' => $referees->random()->id,
            ]);
        }
    }
}
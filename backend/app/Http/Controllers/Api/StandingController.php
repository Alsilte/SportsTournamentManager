<?php

/**
 * Standing Controller for Sports Tournament Manager API
 * 
 * Manages tournament standings, rankings, statistics calculations,
 * and leaderboard data with real-time updates.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Standing;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StandingController extends Controller
{
    /**
     * Display standings for a tournament (public access)
     */
    public function index(Request $request, $tournamentId): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($tournamentId);

            $query = Standing::with(['team:id,name,short_name,logo'])
                ->where('tournament_id', $tournamentId);

            // Filter by group if specified
            if ($request->has('group')) {
                $query->where('group_name', $request->group);
            }

            $standings = $query->orderBy('points', 'desc')
                ->orderBy('goal_difference', 'desc')
                ->orderBy('goals_for', 'desc')
                ->get();

            // Add position numbers
            $standings->each(function ($standing, $index) {
                $standing->position = $index + 1;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament->only(['id', 'name', 'tournament_type']),
                    'standings' => $standings
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch standings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate all standings for a tournament
     */
    public function recalculate(Request $request, $tournamentId): JsonResponse
    {
        // Only admins can recalculate standings
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can recalculate standings.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $tournament = Tournament::with(['matches', 'teams'])->findOrFail($tournamentId);

            // Reset all standings for this tournament
            Standing::where('tournament_id', $tournamentId)->delete();

            // Create fresh standings for all registered teams
            foreach ($tournament->teams as $team) {
                Standing::create([
                    'tournament_id' => $tournamentId,
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
            }

            // Process all completed matches
            $completedMatches = $tournament->matches()->where('status', 'completed')->get();

            foreach ($completedMatches as $match) {
                $this->updateStandingsFromMatch($match);
            }

            DB::commit();

            // Fetch updated standings
            $standings = Standing::with(['team:id,name,short_name,logo'])
                ->where('tournament_id', $tournamentId)
                ->orderBy('points', 'desc')
                ->orderBy('goal_difference', 'desc')
                ->orderBy('goals_for', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Standings recalculated successfully',
                'data' => $standings
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to recalculate standings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed statistics for a team in a tournament
     */
    public function teamStats(Request $request, $tournamentId, $teamId): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($tournamentId);
            $standing = Standing::with(['team:id,name,short_name,logo'])
                ->where('tournament_id', $tournamentId)
                ->where('team_id', $teamId)
                ->firstOrFail();

            // Get team's matches in this tournament
            $matches = $tournament->matches()
                ->where(function ($query) use ($teamId) {
                    $query->where('home_team_id', $teamId)
                        ->orWhere('away_team_id', $teamId);
                })
                ->with(['homeTeam:id,name,short_name', 'awayTeam:id,name,short_name'])
                ->orderBy('match_date')
                ->get();

            // Calculate additional stats
            $recentForm = $matches->where('status', 'completed')
                ->sortByDesc('match_date')
                ->take(5)
                ->map(function ($match) use ($teamId) {
                    $isHome = $match->home_team_id == $teamId;
                    $teamScore = $isHome ? $match->home_score : $match->away_score;
                    $opponentScore = $isHome ? $match->away_score : $match->home_score;

                    if ($teamScore > $opponentScore) return 'W';
                    if ($teamScore < $opponentScore) return 'L';
                    return 'D';
                })->values();

            // Home vs Away performance
            $homeMatches = $matches->where('home_team_id', $teamId)->where('status', 'completed');
            $awayMatches = $matches->where('away_team_id', $teamId)->where('status', 'completed');

            $homeStats = [
                'played' => $homeMatches->count(),
                'won' => $homeMatches->where('winner_team_id', $teamId)->count(),
                'drawn' => $homeMatches->where('winner_team_id', null)->count(),
                'lost' => $homeMatches->where('winner_team_id', '!=', $teamId)->where('winner_team_id', '!=', null)->count(),
            ];

            $awayStats = [
                'played' => $awayMatches->count(),
                'won' => $awayMatches->where('winner_team_id', $teamId)->count(),
                'drawn' => $awayMatches->where('winner_team_id', null)->count(),
                'lost' => $awayMatches->where('winner_team_id', '!=', $teamId)->where('winner_team_id', '!=', null)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament->only(['id', 'name']),
                    'team' => $standing->team,
                    'standing' => $standing->makeHidden(['team']),
                    'recent_form' => $recentForm,
                    'home_performance' => $homeStats,
                    'away_performance' => $awayStats,
                    'matches' => $matches
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tournament top scorers
     */
    public function topScorers(Request $request, $tournamentId): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($tournamentId);

            $topScorers = DB::table('match_events')
                ->join('game_matches', 'match_events.game_match_id', '=', 'game_matches.id')
                ->join('players', 'match_events.player_id', '=', 'players.id')
                ->join('users', 'players.user_id', '=', 'users.id')
                ->join('teams', 'match_events.team_id', '=', 'teams.id')
                ->where('game_matches.tournament_id', $tournamentId)
                ->where('match_events.event_type', 'goal')
                ->select([
                    'users.id as user_id',
                    'users.name as player_name',
                    'players.id as player_id',
                    'teams.id as team_id',
                    'teams.name as team_name',
                    'teams.short_name as team_short_name',
                    DB::raw('COUNT(*) as goals')
                ])
                ->groupBy(['users.id', 'users.name', 'players.id', 'teams.id', 'teams.name', 'teams.short_name'])
                ->orderBy('goals', 'desc')
                ->limit($request->get('limit', 10))
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament->only(['id', 'name']),
                    'top_scorers' => $topScorers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top scorers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update standings from a match result
     */
    private function updateStandingsFromMatch($match): void
    {
        if (!$match->isCompleted()) {
            return;
        }

        // Get or create standings for both teams
        $homeStanding = Standing::firstOrCreate([
            'tournament_id' => $match->tournament_id,
            'team_id' => $match->home_team_id
        ]);

        $awayStanding = Standing::firstOrCreate([
            'tournament_id' => $match->tournament_id,
            'team_id' => $match->away_team_id
        ]);

        // Update standings
        $homeStanding->updateFromMatch($match, true);
        $awayStanding->updateFromMatch($match, false);

        $homeStanding->save();
        $awayStanding->save();
    }
}

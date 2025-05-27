<?php
// app/Http/Controllers/Api/TeamController.php - MÉTODO ROSTER CORREGIDO

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamPlayer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Display a listing of teams (public access)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Team::with('manager:id,name')
                ->withCount(['activePlayers as players_count']);

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $teams = $query->orderBy('name')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $teams,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teams',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get team roster - MÉTODO CORREGIDO
     */
    public function roster(int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            // Get active players with their user information
            $activePlayers = $team->players()
                ->wherePivot('is_active', true)
                ->with(['user:id,name,email'])
                ->withPivot('jersey_number', 'position', 'is_captain', 'joined_date')
                ->orderBy('team_players.jersey_number')
                ->get();

            // Get team captain
            $captain = $team->players()
                ->wherePivot('is_captain', true)
                ->wherePivot('is_active', true)
                ->with(['user:id,name'])
                ->first();

            return response()->json([
                'success' => true,
                'data'    => [
                    'team'     => $team->only(['id', 'name', 'short_name', 'logo']),
                    'players'  => $activePlayers,
                    'captain'  => $captain,
                    'total_players' => $activePlayers->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team roster',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created team
     */
    public function store(Request $request): JsonResponse
    {
        // Only team managers and admins can create teams
        if (!in_array($request->user()->role, ['admin', 'team_manager'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only team managers and admins can create teams.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255|unique:teams',
            'short_name'    => 'nullable|string|max:10|unique:teams',
            'description'   => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'founded_year'  => 'nullable|integer|min:1800|max:' . date('Y'),
            'home_venue'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $team = Team::create(array_merge(
                $validator->validated(),
                [
                    'manager_id' => $request->user()->id,
                    'is_active'  => true,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'data'    => $team->load('manager:id,name'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create team',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified team (public access)
     */
    public function show(int $id): JsonResponse
    {
        try {
            $team = Team::with([
                'manager:id,name,email',
                'tournaments:id,name,status',
            ])
                ->withCount(['activePlayers as players_count'])
                ->findOrFail($id);

            // Get recent matches (last 5)
            $recentMatches = $team->matches()
                ->with(['homeTeam:id,name,short_name', 'awayTeam:id,name,short_name', 'tournament:id,name'])
                ->where('status', 'completed')
                ->orderBy('match_date', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'team' => $team,
                    'recent_matches' => $recentMatches
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified team
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            // Check permissions: only team manager or admin can update
            if (!$request->user()->isAdmin() && $team->manager_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only team manager or admin can update.',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name'          => 'sometimes|string|max:255|unique:teams,name,' . $id,
                'short_name'    => 'nullable|string|max:10|unique:teams,short_name,' . $id,
                'description'   => 'nullable|string',
                'contact_email' => 'sometimes|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'founded_year'  => 'nullable|integer|min:1800|max:' . date('Y'),
                'home_venue'    => 'nullable|string|max:255',
                'is_active'     => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $team->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Team updated successfully',
                'data'    => $team->fresh()->load('manager:id,name'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified team
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            // Only admin or team manager can delete
            if (!$request->user()->isAdmin() && $team->manager_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only team manager or admin can delete.',
                ], 403);
            }

            // Check for active tournament registrations
            if ($team->tournaments()
                ->whereIn('status', ['registration_open', 'in_progress'])
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete team with active tournament registrations',
                ], 422);
            }

            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete team',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add player to team
     */
    public function addPlayer(Request $request, int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            // Check permissions
            if (!$request->user()->isAdmin() && $team->manager_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only team manager or admin can add players.',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'player_id'    => 'required|exists:players,id',
                'jersey_number' => 'required|integer|min:1|max:99',
                'position'     => 'nullable|string|max:50',
                'is_captain'   => 'boolean',
                'joined_date'  => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Check if player is already in team
            if ($team->players()
                ->where('player_id', $request->player_id)
                ->wherePivot('is_active', true)
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player is already in this team',
                ], 422);
            }

            // Check if jersey number is taken
            if ($team->teamPlayers()
                ->where('jersey_number', $request->jersey_number)
                ->where('is_active', true)
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jersey number is already taken',
                ], 422);
            }

            $teamPlayer = TeamPlayer::create([
                'team_id'       => $team->id,
                'player_id'     => $request->player_id,
                'jersey_number' => $request->jersey_number,
                'position'      => $request->position,
                'is_captain'    => $request->boolean('is_captain', false),
                'is_active'     => true,
                'joined_date'   => $request->joined_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Player added to team successfully',
                'data'    => $teamPlayer->load('player.user:id,name', 'team:id,name'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add player to team',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove player from team
     */
    public function removePlayer(Request $request, int $teamId, int $playerId): JsonResponse
    {
        try {
            $team = Team::findOrFail($teamId);

            // Check permissions
            if (!$request->user()->isAdmin() && $team->manager_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only team manager or admin can remove players.',
                ], 403);
            }

            // Find active team-player pivot
            $teamPlayer = TeamPlayer::where('team_id', $teamId)
                ->where('player_id', $playerId)
                ->where('is_active', true)
                ->first();

            if (!$teamPlayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found in team or already inactive',
                ], 404);
            }

            // Soft-remove player
            $teamPlayer->update([
                'is_active' => false,
                'left_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Player removed from team successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove player from team',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get team statistics - MÉTODO CORREGIDO
     */
    public function statistics(int $id): JsonResponse
    {
        try {
            $team = Team::findOrFail($id);

            // Get all completed matches for this team
            $allMatches = \App\Models\GameMatch::where(function ($query) use ($id) {
                $query->where('home_team_id', $id)
                    ->orWhere('away_team_id', $id);
            })
                ->where('status', 'completed')
                ->with(['tournament:id,name', 'homeTeam:id,name', 'awayTeam:id,name'])
                ->get();

            // Calculate statistics
            $stats = [
                'total_matches' => $allMatches->count(),
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'clean_sheets' => 0,
            ];

            foreach ($allMatches as $match) {
                $isHome = $match->home_team_id == $id;
                $teamScore = $isHome ? ($match->home_score ?? 0) : ($match->away_score ?? 0);
                $opponentScore = $isHome ? ($match->away_score ?? 0) : ($match->home_score ?? 0);

                $stats['goals_for'] += $teamScore;
                $stats['goals_against'] += $opponentScore;

                if ($opponentScore == 0) {
                    $stats['clean_sheets']++;
                }

                if ($teamScore > $opponentScore) {
                    $stats['wins']++;
                } elseif ($teamScore < $opponentScore) {
                    $stats['losses']++;
                } else {
                    $stats['draws']++;
                }
            }

            $stats['goal_difference'] = $stats['goals_for'] - $stats['goals_against'];

            // Calculate additional metrics
            $stats['win_rate'] = $stats['total_matches'] > 0 ? round(($stats['wins'] / $stats['total_matches']) * 100, 1) : 0;
            $stats['goals_per_match'] = $stats['total_matches'] > 0 ? round($stats['goals_for'] / $stats['total_matches'], 2) : 0;
            $stats['goals_conceded_per_match'] = $stats['total_matches'] > 0 ? round($stats['goals_against'] / $stats['total_matches'], 2) : 0;

            // Get tournament statistics - CORREGIDO
            $tournamentStats = $allMatches->groupBy('tournament.name')->map(function ($matches, $tournamentName) use ($id) {
                $tournamentData = [
                    'tournament_name' => $tournamentName ?: 'Unknown Tournament',
                    'matches' => $matches->count(),
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                ];

                foreach ($matches as $match) {
                    $isHome = $match->home_team_id == $id;
                    $teamScore = $isHome ? ($match->home_score ?? 0) : ($match->away_score ?? 0);
                    $opponentScore = $isHome ? ($match->away_score ?? 0) : ($match->home_score ?? 0);

                    $tournamentData['goals_for'] += $teamScore;
                    $tournamentData['goals_against'] += $opponentScore;

                    if ($teamScore > $opponentScore) {
                        $tournamentData['wins']++;
                    } elseif ($teamScore < $opponentScore) {
                        $tournamentData['losses']++;
                    } else {
                        $tournamentData['draws']++;
                    }
                }

                return $tournamentData;
            })->values();

            // Get recent form (last 5 matches)
            $recentMatches = $allMatches->sortByDesc('match_date')->take(5);
            $recentForm = $recentMatches->map(function ($match) use ($id) {
                $isHome = $match->home_team_id == $id;
                $teamScore = $isHome ? ($match->home_score ?? 0) : ($match->away_score ?? 0);
                $opponentScore = $isHome ? ($match->away_score ?? 0) : ($match->home_score ?? 0);

                if ($teamScore > $opponentScore) return 'W';
                if ($teamScore < $opponentScore) return 'L';
                return 'D';
            })->values();

            // Get top scorers from this team - CORREGIDO
            $topScorers = DB::table('match_events')
                ->join('game_matches', 'match_events.game_match_id', '=', 'game_matches.id')
                ->join('players', 'match_events.player_id', '=', 'players.id')
                ->join('users', 'players.user_id', '=', 'users.id')
                ->where('match_events.team_id', $id)
                ->where('match_events.event_type', 'goal')
                ->where('game_matches.status', 'completed')
                ->select([
                    'users.id as user_id',
                    'users.name as player_name',
                    'players.id as player_id',
                    DB::raw('COUNT(*) as goals')
                ])
                ->groupBy(['users.id', 'users.name', 'players.id'])
                ->orderBy('goals', 'desc')
                ->limit(5)
                ->get();

            // Get total and active tournaments count - CORREGIDO
            $totalTournaments = DB::table('tournament_teams')
                ->where('team_id', $id)
                ->count();

            $activeTournaments = DB::table('tournament_teams')
                ->join('tournaments', 'tournament_teams.tournament_id', '=', 'tournaments.id')
                ->where('tournament_teams.team_id', $id)
                ->whereIn('tournaments.status', ['registration_open', 'in_progress'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'team' => $team->only(['id', 'name', 'short_name', 'logo']),
                    'overall_statistics' => $stats,
                    'tournament_statistics' => $tournamentStats,
                    'recent_form' => $recentForm,
                    'top_scorers' => $topScorers,
                    'total_tournaments' => $totalTournaments,
                    'active_tournaments' => $activeTournaments,
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
}

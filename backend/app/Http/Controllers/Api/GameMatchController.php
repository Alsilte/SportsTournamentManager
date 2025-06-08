<?php

/**
 * Game Match Controller for Sports Tournament Manager API
 * 
 * Handles match scheduling, score management, event tracking,
 * and match lifecycle operations.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Models\Standing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GameMatchController extends Controller
{
    /**
 * Display a listing of matches (public access)
 */
public function index(Request $request): JsonResponse
{
    try {
        $query = GameMatch::with([
            'tournament:id,name',
            'homeTeam:id,name,short_name',
            'awayTeam:id,name,short_name',
            'referee:id,name'
        ]);

        // Filter by tournament
        if ($request->has('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        // Filter by team
        if ($request->has('team_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('home_team_id', $request->team_id)
                    ->orWhere('away_team_id', $request->team_id);
            });
        }

        // 🆕 NUEVO: Filter by referee_id (para el dashboard del árbitro)
        if ($request->has('referee_id')) {
            $query->where('referee_id', $request->referee_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('match_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('match_date', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $matches = $query->orderBy('match_date')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $matches
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch matches',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Store a newly created match
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins can create matches
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can create matches.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|exists:tournaments,id',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'referee_id' => 'nullable|exists:users,id',
            'round' => 'nullable|string|max:50',
            'match_date' => 'required|date|after:now',
            'venue' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify teams are registered in the tournament
            $tournament = \App\Models\Tournament::findOrFail($request->tournament_id);

            if (
                !$tournament->teams()->where('team_id', $request->home_team_id)->exists() ||
                !$tournament->teams()->where('team_id', $request->away_team_id)->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or both teams are not registered in this tournament'
                ], 422);
            }

            // Get validated data and add status
            $validatedData = $validator->validated();
            $validatedData['status'] = 'scheduled';

            $match = GameMatch::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Match created successfully',
                'data' => $match->load(['tournament:id,name', 'homeTeam:id,name', 'awayTeam:id,name', 'referee:id,name'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified match (public access)
     */
    public function show($id): JsonResponse
    {
        try {
            $match = GameMatch::with([
                'tournament:id,name',
                'homeTeam:id,name,short_name,logo',
                'awayTeam:id,name,short_name,logo',
                'referee:id,name',
                'events.player.user:id,name',
                'events.team:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Match not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified match
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $match = GameMatch::findOrFail($id);

            // Only admins and referees can update matches
            if (
                !$request->user()->isAdmin() &&
                (!$request->user()->isReferee() || $match->referee_id !== $request->user()->id)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins or assigned referee can update match.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'referee_id' => 'nullable|exists:users,id',
                'round' => 'nullable|string|max:50',
                'match_date' => 'sometimes|date',
                'venue' => 'nullable|string|max:255',
                'status' => 'sometimes|in:scheduled,in_progress,completed,postponed,cancelled',
                'home_score' => 'nullable|integer|min:0',
                'away_score' => 'nullable|integer|min:0',
                'extra_time_home' => 'nullable|integer|min:0',
                'extra_time_away' => 'nullable|integer|min:0',
                'penalty_home' => 'nullable|integer|min:0',
                'penalty_away' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                $updateData = $validator->validated();

                // Determine winner if match is completed
                if (
                    $request->status === 'completed' &&
                    $request->has('home_score') && $request->has('away_score')
                ) {

                    $homeScore = $request->home_score;
                    $awayScore = $request->away_score;

                    // Check extra time
                    if ($request->has('extra_time_home') && $request->has('extra_time_away')) {
                        $homeScore += $request->extra_time_home;
                        $awayScore += $request->extra_time_away;
                    }

                    // Check penalties
                    if ($request->has('penalty_home') && $request->has('penalty_away')) {
                        $homeScore += $request->penalty_home;
                        $awayScore += $request->penalty_away;
                    }

                    if ($homeScore > $awayScore) {
                        $updateData['winner_team_id'] = $match->home_team_id;
                    } elseif ($awayScore > $homeScore) {
                        $updateData['winner_team_id'] = $match->away_team_id;
                    } else {
                        $updateData['winner_team_id'] = null; // Draw
                    }
                }

                $match->update($updateData);

                // Update standings if match is completed
                if ($match->status === 'completed' && $match->wasChanged('status')) {
                    $this->updateStandings($match);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Match updated successfully',
                    'data' => $match->fresh()->load(['tournament:id,name', 'homeTeam:id,name', 'awayTeam:id,name'])
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add event to match
     */
    public function addEvent(Request $request, $id): JsonResponse
    {
        try {
            $match = GameMatch::findOrFail($id);

            // Only admins and assigned referee can add events
            if (
                !$request->user()->isAdmin() &&
                (!$request->user()->isReferee() || $match->referee_id !== $request->user()->id)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins or assigned referee can add events.'
                ], 403);
            }

            // Match must be in progress
            if ($match->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only add events to matches in progress'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'team_id' => 'required|exists:teams,id',
                'player_id' => 'nullable|exists:players,id',
                'event_type' => 'required|in:goal,yellow_card,red_card,substitution,own_goal',
                'minute' => 'required|integer|min:0|max:120',
                'additional_time' => 'integer|min:0|max:30',
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify team is playing in this match
            if (!in_array($request->team_id, [$match->home_team_id, $match->away_team_id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team is not playing in this match'
                ], 422);
            }

            $validatedData = $validator->validated();
            $validatedData['game_match_id'] = $match->id;

            $event = MatchEvent::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Match event added successfully',
                'data' => $event->load(['team:id,name', 'player.user:id,name'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add match event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update standings based on match result
     */
    private function updateStandings(GameMatch $match): void
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

    /**
     * Get match events
     */
    public function events($id): JsonResponse
    {
        try {
            $match = GameMatch::with([
                'events.team:id,name,short_name',
                'events.player.user:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'match' => $match->only(['id', 'home_score', 'away_score', 'status']),
                    'events' => $match->events
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch match events',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

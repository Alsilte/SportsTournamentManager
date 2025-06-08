<?php

/**
 * Player Controller for Sports Tournament Manager API
 * 
 * Manages player profiles, statistics, team history, and availability
 * with comprehensive player data handling.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    /**
     * Display a listing of players (public access)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Player::with(['user:id,name,email']);

            // Search by name
            if ($request->has('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            // Filter by position
            if ($request->has('position')) {
                $query->where('position', $request->position);
            }

            // Filter by nationality
            if ($request->has('nationality')) {
                $query->where('nationality', $request->nationality);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $players = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $players
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch players',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified player (public access)
     */
    public function show($id): JsonResponse
    {
        try {
            $player = Player::with([
                'user:id,name,email,phone',
                'activeTeams:id,name,short_name,logo',
                'matchEvents.match.tournament:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $player
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified player profile
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);

            // Check permissions: only the player themselves or admin can update
            if (!$request->user()->isAdmin() && $player->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only the player or admin can update profile.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'jersey_number' => 'nullable|integer|min:1|max:99',
                'position' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|date|before:today',
                'nationality' => 'nullable|string|max:100',
                'height' => 'nullable|numeric|min:100|max:250', // cm
                'weight' => 'nullable|numeric|min:30|max:200', // kg
                'preferred_foot' => 'nullable|in:left,right,both',
                'biography' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Usar $validator->validated() en lugar de $request->validated()
            $player->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Player profile updated successfully',
                'data' => $player->fresh()->load('user:id,name,email')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update player profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get player statistics
     */
    public function statistics($id): JsonResponse
    {
        try {
            $player = Player::with([
                'matchEvents' => function ($query) {
                    $query->with(['match.tournament:id,name', 'team:id,name']);
                }
            ])->findOrFail($id);

            // Calculate statistics
            $events = $player->matchEvents;

            $stats = [
                'total_matches' => $events->groupBy('match_id')->count(),
                'goals' => $events->where('event_type', 'goal')->count(),
                'own_goals' => $events->where('event_type', 'own_goal')->count(),
                'yellow_cards' => $events->where('event_type', 'yellow_card')->count(),
                'red_cards' => $events->where('event_type', 'red_card')->count(),
                'substitutions' => $events->where('event_type', 'substitution')->count(),
            ];

            // Goals by tournament
            $goalsByTournament = $events
                ->where('event_type', 'goal')
                ->groupBy('match.tournament.name')
                ->map(function ($goals) {
                    return $goals->count();
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'player' => $player->only(['id', 'position', 'jersey_number']),
                    'user' => $player->user->only(['id', 'name']),
                    'statistics' => $stats,
                    'goals_by_tournament' => $goalsByTournament,
                    'recent_events' => $events->take(10)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch player statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get player's team history
     */
    public function teamHistory($id): JsonResponse
    {
        try {
            $player = Player::with([
                'teams' => function ($query) {
                    $query->withPivot('jersey_number', 'position', 'is_captain', 'is_active', 'joined_date', 'left_date')
                        ->orderBy('team_players.joined_date', 'desc');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'player' => $player->only(['id']),
                    'user' => $player->user->only(['id', 'name']),
                    'team_history' => $player->teams
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch player team history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available players (not in any active team) - MÉTODO ALTERNATIVO
     */
    public function available(Request $request): JsonResponse
    {
        try {
            // Obtener IDs de jugadores que están activos en equipos
            $activePlayerIds = DB::table('team_players')
                ->where('is_active', true)
                ->pluck('player_id')
                ->toArray();

            // Query para obtener jugadores que NO están en la lista de activos
            $query = Player::with(['user:id,name,email'])
                ->whereNotIn('id', $activePlayerIds);

            // Search by name
            if ($request->has('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            // Filter by position
            if ($request->has('position')) {
                $query->where('position', $request->position);
            }

            // Filter by nationality
            if ($request->has('nationality')) {
                $query->where('nationality', $request->nationality);
            }

            // Obtener los jugadores
            $players = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'available_players' => $players,
                    'total_available' => $players->count(),
                    'message' => $players->isEmpty() ? 'No available players found' : 'Available players retrieved successfully'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available players',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

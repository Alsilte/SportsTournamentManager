<?php
// app/Http/Controllers/Api/TournamentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentTeam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    /**
     * Display a listing of tournaments (public access)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tournament::with(['creator:id,name', 'teams'])
                ->withCount('tournamentTeams as registered_teams_count');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by sport type
            if ($request->has('sport_type')) {
                $query->where('sport_type', $request->sport_type);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $tournaments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tournaments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tournaments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tournament
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins can create tournaments
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can create tournaments.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sport_type' => 'required|string|max:100',
            'tournament_type' => 'required|in:league,knockout,group_knockout',
            'max_teams' => 'required|integer|min:2|max:64',
            'registration_start' => 'required|date|after:now',
            'registration_end' => 'required|date|after:registration_start',
            'start_date' => 'required|date|after:registration_end',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'prize_pool' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $validatedData['created_by'] = $request->user()->id;
            $validatedData['status'] = 'draft';

            $tournament = Tournament::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Tournament created successfully',
                'data' => $tournament->load('creator:id,name')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tournament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tournament (public access)
     */
    public function show($id): JsonResponse
    {
        try {
            $tournament = Tournament::with([
                'creator:id,name',
                'teams.manager:id,name',
                'matches.homeTeam:id,name,short_name',
                'matches.awayTeam:id,name,short_name',
                'matches.referee:id,name',
                'standings.team:id,name,short_name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tournament
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified tournament
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($id);

            // Check permissions: only creator or admin can update
            if (!$request->user()->isAdmin() && $tournament->created_by !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only tournament creator or admin can update.'
                ], 403);
            }

            // Prevent updates if tournament is in progress or completed
            if (in_array($tournament->status, ['in_progress', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update tournament that is in progress or completed'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'sport_type' => 'sometimes|string|max:100',
                'tournament_type' => 'sometimes|in:league,knockout,group_knockout',
                'status' => 'sometimes|in:draft,registration_open,in_progress,completed,cancelled',
                'max_teams' => 'sometimes|integer|min:2|max:64',
                'registration_start' => 'sometimes|date',
                'registration_end' => 'sometimes|date|after:registration_start',
                'start_date' => 'sometimes|date|after:registration_end',
                'end_date' => 'nullable|date|after:start_date',
                'location' => 'nullable|string|max:255',
                'prize_pool' => 'nullable|numeric|min:0',
                'rules' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tournament->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tournament updated successfully',
                'data' => $tournament->fresh()->load('creator:id,name')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tournament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tournament
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($id);

            // Only admin or creator can delete
            if (!$request->user()->isAdmin() && $tournament->created_by !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only tournament creator or admin can delete.'
                ], 403);
            }

            // Prevent deletion if tournament has started
            if (in_array($tournament->status, ['in_progress', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete tournament that is in progress or completed'
                ], 422);
            }

            $tournament->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tournament deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tournament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register team for tournament
     */
    public function registerTeam(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tournament = Tournament::findOrFail($id);

            // Check if registration is open
            if (!$tournament->isRegistrationOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tournament registration is not open'
                ], 422);
            }

            // Check if team is already registered
            if ($tournament->teams()->where('team_id', $request->team_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team is already registered for this tournament'
                ], 422);
            }

            // Check if tournament is full
            if ($tournament->registered_teams_count >= $tournament->max_teams) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tournament is full'
                ], 422);
            }

            $registration = TournamentTeam::create([
                'tournament_id' => $tournament->id,
                'team_id' => $request->team_id,
                'registration_date' => now(),
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team registered successfully',
                'data' => $registration->load('team:id,name', 'tournament:id,name')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register team',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tournament standings
     */
    public function standings($id): JsonResponse
    {
        try {
            $tournament = Tournament::with([
                'standings.team:id,name,short_name,logo'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament->only(['id', 'name', 'tournament_type']),
                    'standings' => $tournament->standings
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
     * Get tournament matches
     */
    public function matches($id): JsonResponse
    {
        try {
            $tournament = Tournament::with([
                'matches.homeTeam:id,name,short_name',
                'matches.awayTeam:id,name,short_name',
                'matches.referee:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament->only(['id', 'name']),
                    'matches' => $tournament->matches
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

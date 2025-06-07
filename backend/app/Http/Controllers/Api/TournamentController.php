<?php
// app/Http/Controllers/Api/TournamentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentTeam;
use App\Models\Team;
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

            // LÓGICA CORREGIDA PARA FILTRAR DRAFTS:
            // - Los admins pueden ver TODOS los torneos (incluyendo drafts)
            // - Los usuarios normales solo ven torneos públicos + sus propios drafts
            // - Los usuarios no autenticados solo ven torneos públicos (sin drafts)
            
            $user = $request->user();
            
            if (!$user) {
                // Usuario no autenticado: solo torneos públicos (sin drafts)
                $query->where('status', '!=', 'draft');
            } elseif (!$user->isAdmin()) {
                // Usuario autenticado pero NO admin: 
                // - Torneos públicos (sin drafts)
                // - SUS PROPIOS drafts
                $query->where(function($q) use ($user) {
                    $q->where('status', '!=', 'draft')
                      ->orWhere('created_by', $user->id);
                });
            }
            // Si es admin: NO se aplica ningún filtro, ve TODOS los torneos

            // Filter by status (si se especifica)
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
                'data' => $tournaments,
                'debug' => config('app.debug') ? [
                    'user_id' => $user?->id,
                    'is_admin' => $user?->isAdmin(),
                    'user_role' => $user?->role,
                ] : null
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
     * Método auxiliar para verificar permisos de admin
     * (opcional, para debugging)
     */
    private function debugAdminAccess(Request $request): array
    {
        $user = $request->user();
        
        return [
            'authenticated' => !!$user,
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'is_admin_method' => $user?->isAdmin(),
            'is_admin_direct' => $user?->role === 'admin',
            'has_role_admin' => $user?->hasRole('admin'),
        ];
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
$validatedData['status'] = $request->input('status', 'draft');

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

            // Obtener datos validados
            $validatedData = $validator->validated();

            // VALIDAR TRANSICIONES DE ESTADO
            if (isset($validatedData['status'])) {
                $currentStatus = $tournament->status;
                $newStatus = $validatedData['status'];
                
                $invalidTransitions = [
                    'completed' => ['draft', 'registration_open', 'in_progress'],
                    'in_progress' => ['draft', 'registration_open']
                ];
                
                if (isset($invalidTransitions[$currentStatus]) && 
                    in_array($newStatus, $invalidTransitions[$currentStatus])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot change status from {$currentStatus} to {$newStatus}"
                    ], 422);
                }
            }

            // Actualizar torneo
            $tournament->update($validatedData);

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
   /**
 * Register a team for a tournament
 */
public function registerTeam(Request $request, Tournament $tournament): JsonResponse
{
    try {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $team = Team::findOrFail($request->team_id);

        // VERIFICACIONES DE PERMISOS:
        // 1. El admin (puede registrar cualquier equipo)
        // 2. El manager del equipo (solo puede registrar SUS equipos)
        if (!$user->isAdmin() && $team->manager_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only register teams you manage, or be an admin.'
            ], 403);
        }

        // Check if registration is open
        // NOTA: Los admins pueden saltarse esta restricción si es necesario
        if (!$user->isAdmin() && !$tournament->isRegistrationOpen()) {
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
        // NOTA: Los admins pueden saltarse esta restricción si es necesario
        if (!$user->isAdmin() && $tournament->registered_teams_count >= $tournament->max_teams) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament is full'
            ], 422);
        }

        // *** VALIDACIÓN CONDICIONAL DE JUGADORES ***
        // Solo aplicar restricciones mínimas a managers, admins sin restricciones
        if (!$user->isAdmin()) {
            $activePlayersCount = $team->players()->wherePivot('is_active', true)->count();
            
            // Solo una restricción muy básica para managers
            if ($activePlayersCount < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo debe tener al menos 1 jugador activo'
                ], 422);
            }
        }
        // Los admins no tienen restricciones de jugadores

        // Determinar el estado inicial del registro
        $registrationStatus = $user->isAdmin() ? 'approved' : 'pending';

        $registration = TournamentTeam::create([
            'tournament_id' => $tournament->id,
            'team_id' => $request->team_id,
            'registration_date' => now(),
            'status' => $registrationStatus
        ]);

        $message = $user->isAdmin() 
            ? 'Team registered and approved successfully' 
            : 'Team registered successfully. Awaiting approval.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $registration->load(['team', 'tournament'])
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to register team',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function adminRegisterTeam(Request $request, $id): JsonResponse
{
    // Solo para admins
    if (!$request->user()->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Admin access required'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'team_id' => 'required|exists:teams,id',
        'bypass_restrictions' => 'boolean',
        'status' => 'sometimes|in:pending,approved,rejected'
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
        $bypassRestrictions = $request->boolean('bypass_restrictions', true);

        // Check if team is already registered
        if ($tournament->teams()->where('team_id', $request->team_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Team is already registered for this tournament'
            ], 422);
        }

        // Solo verificar restricciones si no se quiere hacer bypass
        if (!$bypassRestrictions) {
            if (!$tournament->isRegistrationOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tournament registration is not open'
                ], 422);
            }

            if ($tournament->registered_teams_count >= $tournament->max_teams) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tournament is full'
                ], 422);
            }
        }

        $registration = TournamentTeam::create([
            'tournament_id' => $tournament->id,
            'team_id' => $request->team_id,
            'registration_date' => now(),
            'status' => $request->input('status', 'approved')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team registered by admin successfully',
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
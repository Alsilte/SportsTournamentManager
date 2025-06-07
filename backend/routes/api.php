<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\GameMatchController;
use App\Http\Controllers\Api\StandingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
  return response()->json([
    'success' => true,
    'message' => 'API is running',
    'timestamp' => now()
  ]);
});

// Public routes - No authentication required
Route::prefix('')->group(function () {

  // Authentication routes
  Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
  });

  // Public tournament data
  Route::prefix('tournaments')->group(function () {
    Route::get('/', [TournamentController::class, 'index']);
    Route::get('/{id}', [TournamentController::class, 'show']);
    Route::get('/{id}/standings', [StandingController::class, 'index']);
    Route::get('/{id}/matches', [TournamentController::class, 'matches']);
    Route::get('/{id}/top-scorers', [StandingController::class, 'topScorers']);
  });

  // Public team data
  Route::prefix('teams')->group(function () {
    Route::get('/', [TeamController::class, 'index']);
    Route::get('/{id}', [TeamController::class, 'show']);
    Route::get('/{id}/roster', [TeamController::class, 'roster']);
    Route::get('/{id}/statistics', [TeamController::class, 'statistics']);
  });

  // Public player data
  Route::prefix('players')->group(function () {
    Route::get('/available', [PlayerController::class, 'available']); // Esta ruta DEBE ir ANTES que /{id}
    Route::get('/', [PlayerController::class, 'index']);
    Route::get('/{id}', [PlayerController::class, 'show']);
    Route::get('/{id}/statistics', [PlayerController::class, 'statistics']);
    Route::get('/{id}/team-history', [PlayerController::class, 'teamHistory']);
  });

  // Public match data
  Route::prefix('matches')->group(function () {
    Route::get('/', [GameMatchController::class, 'index']);
    Route::get('/{id}', [GameMatchController::class, 'show']);
    Route::get('/{id}/events', [GameMatchController::class, 'events']);
  });
});

// Protected routes - Authentication required
Route::prefix('')->middleware('auth:sanctum')->group(function () {

  // User authentication management
  Route::prefix('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
  });

  // Tournament management (Admin only for create/update/delete)
  Route::prefix('tournaments')->group(function () {
    Route::post('/', [TournamentController::class, 'store']);
    Route::put('/{id}', [TournamentController::class, 'update']);
    Route::delete('/{id}', [TournamentController::class, 'destroy']);
    Route::post('/{id}/register-team', [TournamentController::class, 'registerTeam']);
    Route::post('/{id}/recalculate-standings', [StandingController::class, 'recalculate']);
  });

  // Team management
  Route::prefix('teams')->group(function () {
    Route::post('/', [TeamController::class, 'store']);
    Route::put('/{id}', [TeamController::class, 'update']);
    Route::delete('/{id}', [TeamController::class, 'destroy']);
    Route::post('/{id}/add-player', [TeamController::class, 'addPlayer']);
    Route::delete('/{teamId}/remove-player/{playerId}', [TeamController::class, 'removePlayer']);
    Route::get('/{id}/available-players', [TeamController::class, 'getAvailablePlayers']);
  });

  // Player management
  Route::prefix('players')->group(function () {
    Route::put('/{id}', [PlayerController::class, 'update']);
  });

  // Match management (Admin and Referee only)
  Route::prefix('matches')->group(function () {
    Route::post('/', [GameMatchController::class, 'store']);
    Route::put('/{id}', [GameMatchController::class, 'update']);
    Route::post('/{id}/add-event', [GameMatchController::class, 'addEvent']);
  });

  // Standings management
  Route::prefix('standings')->group(function () {
    Route::get('/tournament/{tournamentId}/team/{teamId}', [StandingController::class, 'teamStats']);
  });

  // Team routes
  Route::apiResource('teams', TeamController::class);
  Route::get('teams/my-teams', [TeamController::class, 'getMyTeams']); // Nueva ruta
  Route::post('teams/{team}/players', [TeamController::class, 'addPlayer']);
  Route::put('teams/{team}/players/{player}', [TeamController::class, 'updatePlayer']);
  Route::delete('teams/{team}/players/{player}', [TeamController::class, 'removePlayer']);
});

// Fallback route for API
Route::fallback(function () {
  return response()->json([
    'success' => false,
    'message' => 'API endpoint not found',
    'available_endpoints' => [
      'GET /api/health' => 'Health check',
      'POST /api/auth/login' => 'User login',
      'POST /api/auth/register' => 'User registration',
      'GET /api/tournaments' => 'List tournaments',
      'GET /api/teams' => 'List teams',
      'GET /api/players' => 'List players',
      'GET /api/matches' => 'List matches'
    ]
  ], 404);
});

<?php

/**
 * Authentication Controller for Sports Tournament Manager API
 * 
 * Handles user registration, login, logout, and profile management with
 * JWT token authentication and role-based access control.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user in the system
     * 
     * Validates user input, creates a new user record, and if the role is 'player',
     * also creates a corresponding player record. Returns a JWT token for authentication.
     * 
     * @param Request $request The HTTP request containing user registration data
     * @return JsonResponse JSON response with user data and authentication token
     */
    public function register(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,team_manager,player,referee',
            'phone' => 'nullable|string|max:20',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create new user with hashed password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // If user is a player, create corresponding player record
            if ($request->role === 'player') {
                Player::create([
                    'user_id' => $user->id,
                ]);
            }

            // Generate authentication token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response with user data and token
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user->load('player'),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);
        } catch (\Exception $e) {
            // Log error and return failure response
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Authenticate user and generate access token
     * 
     * Validates user credentials, checks if account is active, and generates
     * a new JWT token. Deletes any existing tokens for security.
     * 
     * @param Request $request The HTTP request containing login credentials
     * @return JsonResponse JSON response with user data and authentication token
     */
    public function login(Request $request): JsonResponse
    {
        // Validate login credentials
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user account is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deactivated'
                ], 403);
            }

            // Revoke all existing tokens for security
            $user->tokens()->delete();
            
            // Generate new authentication token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response with user data and token
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->load('player'),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Exception $e) {
            // Log error and return failure response
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    /**
     * Get authenticated user profile
     * 
     * Returns the current authenticated user's profile data including
     * related player information and managed teams.
     * 
     * @param Request $request The authenticated HTTP request
     * @return JsonResponse JSON response with user profile data
     */
    public function profile(Request $request): JsonResponse
    {
        // Get authenticated user with relationships
        $user = $request->user()->load('player', 'managedTeams');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user profile
     */
    /**
     * Update authenticated user profile
     * 
     * Allows users to update their profile information including name, phone,
     * avatar, and password. Validates input and hashes password if provided.
     * 
     * @param Request $request The HTTP request containing profile update data
     * @return JsonResponse JSON response with updated user data
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // Get the authenticated user
        $user = $request->user();

        // Validate the incoming profile update data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Prepare update data (exclude sensitive fields)
            $updateData = $request->only(['name', 'phone', 'avatar']);

            // Hash password if provided
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Update user record
            $user->update($updateData);

            // Return success response with updated user data
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->load('player')
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token)
     * 
     * Attempts to revoke the current authentication token using multiple methods:
     * 1. Find and delete the specific token using bearer token
     * 2. Use Laravel Sanctum's currentAccessToken method
     * 3. Fallback to revoking all tokens for the user
     * 
     * @param Request $request The authenticated HTTP request
     * @return JsonResponse JSON response indicating logout status
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Method 1: Try to get the token ID from the request header
            $bearerToken = $request->bearerToken();

            if ($bearerToken) {
                // Find and delete the specific token using SHA256 hash
                $tokenModel = $user->tokens()->where('token', hash('sha256', $bearerToken))->first();

                if ($tokenModel) {
                    $tokenModel->delete();

                    return response()->json([
                        'success' => true,
                        'message' => 'Logged out successfully'
                    ]);
                }
            }

            // Method 2: Try using currentAccessToken (Laravel Sanctum method)
            try {
                $currentToken = $user->currentAccessToken();
                if ($currentToken && method_exists($currentToken, 'delete')) {
                    $user->tokens()->where('id', $currentToken->id)->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Logged out successfully'
                    ]);
                }
            } catch (\Exception $e) {
                // Continue to fallback method if this fails
            }

            // Method 3: Fallback - revoke all tokens for this user
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully (all sessions)'
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     * 
     * Revokes all authentication tokens for the current user, effectively
     * logging them out from all devices and sessions.
     * 
     * @param Request $request The authenticated HTTP request
     * @return JsonResponse JSON response indicating logout status
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Revoke all tokens for the authenticated user
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh authentication token
     * 
     * Revokes the current token and generates a new one for enhanced security.
     * This allows users to refresh their session without re-entering credentials.
     * 
     * @param Request $request The authenticated HTTP request
     * @return JsonResponse JSON response with new authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Method 1: Try to get and revoke current token using bearer token
            $bearerToken = $request->bearerToken();

            if ($bearerToken) {
                // Find and delete the specific token using SHA256 hash
                $tokenModel = $user->tokens()->where('token', hash('sha256', $bearerToken))->first();
                if ($tokenModel) {
                    $tokenModel->delete();
                }
            } else {
                // Fallback: revoke all tokens if specific token not found
                $user->tokens()->delete();
            }

            // Create new authentication token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response with new token
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

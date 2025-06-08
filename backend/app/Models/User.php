<?php

/**
 * User Model for Sports Tournament Manager
 * 
 * Extends Laravel's Authenticatable with role-based access control,
 * API token management, and user relationship handling.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Get the player profile associated with the user.
     */
    public function player()
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Get the teams managed by this user.
     */
    public function managedTeams()
    {
        return $this->hasMany(Team::class, 'manager_id');
    }

    /**
     * Get the tournaments created by this user.
     */
    public function createdTournaments()
    {
        return $this->hasMany(Tournament::class, 'created_by');
    }

    /**
     * Get the matches refereed by this user.
     */
    public function refereedMatches()
    {
        return $this->hasMany(GameMatch::class, 'referee_id');
    }

    // Helper methods

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is team manager.
     */
    public function isTeamManager(): bool
    {
        return $this->hasRole('team_manager');
    }

    /**
     * Check if user is player.
     */
    public function isPlayer(): bool
    {
        return $this->hasRole('player');
    }

    /**
     * Check if user is referee.
     */
    public function isReferee(): bool
    {
        return $this->hasRole('referee');
    }
}

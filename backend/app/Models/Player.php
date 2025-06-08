<?php

/**
 * Player Model for Sports Tournament Manager
 * 
 * Represents player entities with personal information, team associations,
 * and performance statistics tracking.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'jersey_number',
        'position',
        'date_of_birth',
        'nationality',
        'height',
        'weight',
        'preferred_foot',
        'biography',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    // Relationships

    /**
     * Get the user associated with this player.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the teams this player belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_players')
            ->withPivot('jersey_number', 'position', 'is_captain', 'is_active', 'joined_date', 'left_date')
            ->withTimestamps();
    }

    /**
     * Get the active teams this player belongs to.
     */
    public function activeTeams()
    {
        return $this->teams()->wherePivot('is_active', true);
    }

   /**
 * Get the current active team (if any)
 */
public function currentTeam()
{
    return $this->belongsToMany(Team::class, 'team_players')
        ->withPivot('jersey_number', 'position', 'is_captain', 'is_active', 'joined_date', 'left_date')
        ->wherePivot('is_active', true)
        ->withTimestamps()
        ->limit(1);
}

    /**
     * Get the match events for this player.
     */
    public function matchEvents()
    {
        return $this->hasMany(MatchEvent::class);
    }

    // Helper methods

    /**
     * Get player's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get player's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

  /**
 * Check if player is currently active in any team
 */
public function hasActiveTeam(): bool
{
    return $this->teams()->wherePivot('is_active', true)->exists();
}
    /**
     * 🆕 Get current team name (helper)
     */
    public function getCurrentTeamNameAttribute(): ?string
    {
        $currentTeam = $this->currentTeam()->first();
        return $currentTeam ? $currentTeam->name : null;
    }
}
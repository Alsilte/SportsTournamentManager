<?php

/**
 * Team Model for Sports Tournament Manager
 * 
 * Represents team entities with player management, tournament participation,
 * and performance tracking capabilities.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'short_name',
        'logo',
        'description',
        'manager_id',
        'contact_email',
        'contact_phone',
        'founded_year',
        'home_venue',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'founded_year' => 'integer',
        ];
    }

    // Relationships

    /**
     * Get the manager of this team.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the players in this team.
     */
    public function players()
    {
        return $this->belongsToMany(Player::class, 'team_players')
            ->withPivot('jersey_number', 'position', 'is_captain', 'is_active', 'joined_date', 'left_date')
            ->withTimestamps();
    }

    /**
     * Get the active players in this team.
     */
    public function activePlayers()
    {
        return $this->players()->wherePivot('is_active', true);
    }

    /**
     * Get the team captain.
     */
    public function captain()
    {
        return $this->players()->wherePivot('is_captain', true)->wherePivot('is_active', true)->first();
    }

    /**
     * Get the team player registrations.
     */
    public function teamPlayers()
    {
        return $this->hasMany(TeamPlayer::class);
    }

    /**
     * Get the tournaments this team is registered for.
     */
    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_teams')
            ->withPivot('registration_date', 'status', 'group_name', 'seed')
            ->withTimestamps();
    }

    /**
     * Get home matches.
     */
    public function homeMatches()
    {
        return $this->hasMany(GameMatch::class, 'home_team_id');
    }

    /**
     * Get away matches.
     */
    public function awayMatches()
    {
        return $this->hasMany(GameMatch::class, 'away_team_id');
    }

    /**
     * Get all matches (home and away) - MÉTODO CORREGIDO
     */
    public function matches()
    {
        return GameMatch::where('home_team_id', $this->id)
            ->orWhere('away_team_id', $this->id);
    }

    /**
     * Get all matches using relationship scope - MÉTODO ALTERNATIVO
     */
    public function allMatches()
    {
        $homeMatches = $this->homeMatches()->pluck('id');
        $awayMatches = $this->awayMatches()->pluck('id');
        $allMatchIds = $homeMatches->merge($awayMatches);

        return GameMatch::whereIn('id', $allMatchIds);
    }

    /**
     * Get the standings for this team.
     */
    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    // Helper methods

    /**
     * Get active players count.
     */
    public function getActivePlayersCountAttribute(): int
    {
        return $this->activePlayers()->count();
    }

    /**
     * Get team's win rate.
     */
    public function getWinRateAttribute(): float
    {
        $matches = $this->matches()->where('status', 'completed')->get();

        if ($matches->isEmpty()) {
            return 0;
        }

        $wins = $matches->filter(function ($match) {
            return $match->winner_team_id === $this->id;
        })->count();

        return round(($wins / $matches->count()) * 100, 2);
    }

    /**
     * Get team's total goals scored.
     */
    public function getTotalGoalsAttribute(): int
    {
        $matches = $this->matches()->where('status', 'completed')->get();
        $totalGoals = 0;

        foreach ($matches as $match) {
            if ($match->home_team_id === $this->id) {
                $totalGoals += $match->home_score ?? 0;
            } else {
                $totalGoals += $match->away_score ?? 0;
            }
        }

        return $totalGoals;
    }
}

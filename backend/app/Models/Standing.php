<?php

/**
 * Standing Model for Sports Tournament Manager
 * 
 * Manages tournament standings with team statistics, points calculation,
 * and ranking functionality for competitive analysis.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tournament_id',
        'team_id',
        'group_name',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
        'position',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the tournament.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the team.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Helper methods

    /**
     * Calculate points based on wins and draws.
     */
    public function calculatePoints(): int
    {
        return ($this->won * 3) + ($this->drawn * 1);
    }

    /**
     * Update standings from match result.
     */
    public function updateFromMatch(GameMatch $match, bool $isHome): void
    {
        if (!$match->isCompleted()) {
            return;
        }

        $this->played++;

        $teamScore = $isHome ? $match->home_score : $match->away_score;
        $opponentScore = $isHome ? $match->away_score : $match->home_score;

        $this->goals_for += $teamScore;
        $this->goals_against += $opponentScore;
        $this->goal_difference = $this->goals_for - $this->goals_against;

        if ($teamScore > $opponentScore) {
            $this->won++;
        } elseif ($teamScore < $opponentScore) {
            $this->lost++;
        } else {
            $this->drawn++;
        }

        $this->points = $this->calculatePoints();
        $this->touch(); // Update timestamp
    }
}

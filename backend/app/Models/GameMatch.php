<?php

/**
 * GameMatch Model for Sports Tournament Manager
 * 
 * Represents individual matches within tournaments with teams, scores,
 * events tracking, and referee assignment capabilities.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tournament_id',
        'home_team_id',
        'away_team_id',
        'referee_id',
        'round',
        'match_date',
        'venue',
        'status',
        'home_score',
        'away_score',
        'extra_time_home',
        'extra_time_away',
        'penalty_home',
        'penalty_away',
        'winner_team_id',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the tournament this match belongs to.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the home team.
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     */
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the referee.
     */
    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Get the winner team.
     */
    public function winnerTeam()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /**
     * Get the match events.
     */
    public function events()
    {
        return $this->hasMany(MatchEvent::class, 'game_match_id')->orderBy('minute')->orderBy('additional_time');
    }

    // Helper methods

    /**
     * Check if match is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get final score string.
     */
    public function getFinalScoreAttribute(): string
    {
        if (!$this->isCompleted()) {
            return 'N/A';
        }

        $score = "{$this->home_score} - {$this->away_score}";

        if ($this->extra_time_home !== null && $this->extra_time_away !== null) {
            $score .= " (ET: {$this->extra_time_home} - {$this->extra_time_away})";
        }

        if ($this->penalty_home !== null && $this->penalty_away !== null) {
            $score .= " (Pen: {$this->penalty_home} - {$this->penalty_away})";
        }

        return $score;
    }
}

<?php

// app/Models/MatchEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'event_type',
        'minute',
        'additional_time',
        'description',
    ];

    // Relationships

    /**
     * Get the match this event belongs to.
     */
    public function match()
    {
        return $this->belongsTo(GameMatch::class, 'game_match_id');
    }

    /**
     * Get the team.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the player.
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Helper methods

    /**
     * Get formatted time string.
     */
    public function getFormattedTimeAttribute(): string
    {
        $time = $this->minute;
        if ($this->additional_time > 0) {
            $time .= "+{$this->additional_time}";
        }
        return $time . "'";
    }
}

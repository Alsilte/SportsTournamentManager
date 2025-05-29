<?php
// app/Models/TeamPlayer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPlayer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id',
        'player_id',
        'jersey_number',
        'position',
        'is_captain',
        'is_active',
        'joined_date',
        'left_date',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_captain' => 'boolean',
            'is_active' => 'boolean',
            'joined_date' => 'date',
            'left_date' => 'date',
        ];
    }

    // Relationships

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
}

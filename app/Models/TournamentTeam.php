<?php

// app/Models/TournamentTeam.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentTeam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tournament_id',
        'team_id',
        'registration_date',
        'status',
        'group_name',
        'seed',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
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
}

<?php
// app/Models/Tournament.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tournament extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'sport_type',
        'tournament_type',
        'status',
        'max_teams',
        'registration_start',
        'registration_end',
        'start_date',
        'end_date',
        'location',
        'prize_pool',
        'rules',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'registration_start' => 'datetime',
            'registration_end' => 'datetime',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'prize_pool' => 'decimal:2',
        ];
    }

    // Relationships

    /**
     * Get the user who created this tournament.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the teams registered for this tournament.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_teams')
            ->withPivot('registration_date', 'status', 'group_name', 'seed')
            ->withTimestamps();
    }

    /**
     * Get the tournament team registrations.
     */
    public function tournamentTeams()
    {
        return $this->hasMany(TournamentTeam::class);
    }

    /**
     * Get the matches in this tournament.
     */
    public function matches()
    {
        return $this->hasMany(GameMatch::class);
    }

    /**
     * Get the standings for this tournament.
     */
    public function standings()
    {
        return $this->hasMany(Standing::class)
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc');
    }

    // Helper methods

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        $now = Carbon::now();
        return $this->status === 'registration_open'
            && $now->between($this->registration_start, $this->registration_end);
    }

    /**
     * Check if tournament is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['registration_open', 'in_progress']);
    }

    /**
     * Get registered teams count.
     */
    public function getRegisteredTeamsCountAttribute(): int
    {
        return $this->tournamentTeams()->where('status', 'approved')->count();
    }
}

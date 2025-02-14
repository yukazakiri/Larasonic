<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'deadline',
        'total_points',
        'is_group_activity',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'total_points' => 'integer',
        'is_group_activity' => 'boolean',
    ];

    protected $with = ['team'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function userPoints(): HasMany
    {
        return $this->hasMany(ActivityUserPoint::class);
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id', 'team_id');
    }

    /**
     * Get all eligible team members (excluding owner)
     */
    public function getEligibleTeamMembers()
    {
        $this->team->load('owner');
        return $this->teamMembers()
            ->whereNot('users.id', $this->team->owner->id)
            ->get();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ActivityGroup::class);
    }

    protected static function booted()
    {
        static::creating(function ($activity) {
            if (!$activity->team_id) {
                $activity->team_id = auth()->user()->currentTeam->id;
            }
        });
    }
} 
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class ActivityGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_group_id',
        'user_id',
        'points_adjustment',
        'adjustment_reason',
    ];

    protected $casts = [
        'points_adjustment' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ActivityGroup::class, 'activity_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFinalPointsAttribute(): int
    {
        return $this->group->points + ($this->points_adjustment ?? 0);
    }
} 
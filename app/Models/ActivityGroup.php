<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class ActivityGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'name',
        'color',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ActivityGroupMember::class);
    }
} 
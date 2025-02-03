<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

final class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'day_of_week',
        'color',
        'is_recurring',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];

    // Convert time strings to proper format before saving
    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
    }

    // Format times for frontend display
    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
} 
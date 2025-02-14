<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'title',
        'description',
        'type',
        'position',
        'total_points',
    ];

    protected $casts = [
        'position' => 'integer',
        'total_points' => 'float',
    ];

    /**
     * Get the exam that owns the section.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the questions for the section.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)
            ->orderBy('position');
    }

    /**
     * Get the question count for this section
     */
    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }

    /**
     * Calculate and update the total points for this section
     */
    public function updateTotalPoints(): void
    {
        $this->total_points = $this->questions()->sum('points');
        $this->save();
    }
} 
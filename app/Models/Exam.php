<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'passing_score',
        'attempts_limit',
        'show_correct_answers',
        'is_public',
        'available_from',
        'available_to',
        'published_at',
        'is_draft',
        'team_id',
        'shuffle_questions',
        'shuffle_options',
        'allow_review',
        'exam_instructions',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_correct_answers' => 'boolean',
        'is_public' => 'boolean',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'time_limit' => 'integer',
        'passing_score' => 'integer',
        'attempts_limit' => 'integer',
        'is_draft' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'allow_review' => 'boolean',
    ];

    protected $with = [];

    /**
     * Get the sections for the exam.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('position');
    }

    /**
     * Get all questions for the exam, regardless of section.
     */
    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, Section::class)
            ->orderBy('questions.position');
    }

    /**
     * Check if the exam is published.
     */
    public function isPublished(): bool
    {
        return !$this->is_draft && $this->published_at !== null;
    }

    /**
     * Get the team that owns the exam.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the total number of questions for this exam.
     */
    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }

    protected static function booted()
    {
        static::creating(function ($exam) {
            if (!$exam->team_id) {
                $exam->team_id = auth()->user()->currentTeam->id;
            }
        });

        static::deleting(function ($exam) {
            $exam->questions()->each(function ($question) {
                $question->delete();
            });
        });

        static::restoring(function ($exam) {
            $exam->questions()->withTrashed()->each(function ($question) {
                $question->restore();
            });
        });
    }
} 
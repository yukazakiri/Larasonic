<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'type',
        'question',
        'points',
        'position',
        'options',
        'correct_answers',
    ];

    protected $casts = [
        'points' => 'float',
        'position' => 'integer',
        'options' => 'array',
        'correct_answers' => 'array',
    ];

    /**
     * Get the section that owns the question.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function requiresManualGrading(): bool
    {
        return in_array($this->type, ['essay', 'case_study', 'problem_solving']);
    }

    public function getFormattedQuestionAttribute()
    {
        if ($this->type === 'fill_blank') {
            return preg_replace('/\[(.*?)\]/', '______', $this->question);
        }
        return $this->question;
    }

    public static function validationRules($type): array
    {
        $rules = [
            'question' => 'required|string',
            'points' => 'required|numeric|min:1',
            'options' => 'nullable|array',
        ];

        if ($type === 'matching') {
            $rules['options.pairs'] = 'required|array|min:2';
            $rules['options.pairs.*.left'] = 'required|string';
            $rules['options.pairs.*.right'] = 'required|string';
            $rules['correct_answers'] = 'required|array';
            $rules['correct_answers.*'] = 'integer|min:0';
        }

        return $rules;
    }

    protected static function booted()
    {
        static::creating(function ($question) {
            if (!$question->position) {
                // Set position to last + 1 if not specified
                $question->position = static::where('section_id', $question->section_id)
                    ->max('position') + 1;
            }
        });

        static::created(function ($question) {
            if ($question->section_id) {
                $question->section->updateTotalPoints();
            }
        });

        static::updated(function ($question) {
            if ($question->section_id) {
                $question->section->updateTotalPoints();
            }
        });

        static::deleted(function ($question) {
            if ($question->section_id) {
                $question->section->updateTotalPoints();
            }
        });
    }
} 
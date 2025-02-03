<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

final class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(): Response
    {
        $exams = Exam::query()
            ->where('team_id', auth()->user()->currentTeam->id)
            ->withCount('questions')
            ->latest()
            ->get();

        return Inertia::render('Exams/Index', [
            'exams' => $exams->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'is_draft' => $exam->is_draft,
                    'questions_count' => $exam->questions_count,
                    'time_limit' => $exam->time_limit,
                    'published_at' => $exam->published_at?->toDateTimeString(),
                    'created_at' => $exam->created_at->toDateTimeString(),
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create(): Response
    {
        return Inertia::render('Exams/Create');
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $exam = Exam::create([
            ...$validated,
            'is_draft' => true
        ]);

        return redirect()->route('exams.builder', ['exam' => $exam->id]);
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam): Response
    {
        $exam->load([
            'sections' => function ($query) {
                $query->orderBy('position');
            },
            'sections.questions' => function ($query) {
                $query->orderBy('position');
            },
            'sections.questions.options'
        ]);

        // Debug the loaded data
        \Log::info('Exam data:', [
            'exam' => $exam->toArray()
        ]);

        return Inertia::render('Exams/Show', [
            'exam' => $exam
        ]);
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam): Response
    {
        return Inertia::render('Exams/Edit', [
            'exam' => $exam,
        ]);
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sections' => 'array',
            'sections.*.title' => 'required|string',
            'sections.*.description' => 'nullable|string',
            'sections.*.type' => 'required|string',
            'sections.*.position' => 'required|integer|min:1',
            'sections.*.questions' => 'array',
            'sections.*.questions.*.type' => 'required|string',
            'sections.*.questions.*.question' => 'required|string|max:65535',
            'sections.*.questions.*.points' => 'required|numeric|min:0.5|max:100',
            'sections.*.questions.*.options' => 'present|array',
            'sections.*.questions.*.correct_answers' => 'required|array',
            'sections.*.questions.*.position' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        
        try {
            $exam->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            if (isset($validated['sections'])) {
                $sectionIds = [];
                
                foreach ($validated['sections'] as $sectionData) {
                    $section = $exam->sections()->updateOrCreate(
                        [
                            'id' => $sectionData['id'] ?? null,
                            'exam_id' => $exam->id,
                        ],
                        [
                            'title' => $sectionData['title'],
                            'description' => $sectionData['description'],
                            'type' => $sectionData['type'],
                            'position' => $sectionData['position'],
                        ]
                    );
                    
                    $sectionIds[] = $section->id;
                    
                    if (isset($sectionData['questions'])) {
                        $questionIds = [];
                        
                        foreach ($sectionData['questions'] as $questionData) {
                            $questionText = trim($questionData['question']);
                            
                            $question = $section->questions()->updateOrCreate(
                                [
                                    'id' => $questionData['id'] ?? null,
                                    'exam_id' => $exam->id,
                                ],
                                [
                                    'type' => $questionData['type'],
                                    'question' => $questionText,
                                    'points' => $questionData['points'],
                                    'options' => $questionData['options'],
                                    'correct_answers' => $questionData['correct_answers'],
                                    'position' => $questionData['position'],
                                    'requires_manual_grading' => in_array($questionData['type'], ['essay', 'case_study', 'problem_solving']),
                                ]
                            );
                            
                            $questionIds[] = $question->id;
                        }
                        
                        // Delete questions not in the updated list
                        $section->questions()
                            ->whereNotIn('id', $questionIds)
                            ->delete();
                    }
                }
                
                // Delete sections not in the updated list
                $exam->sections()
                    ->whereNotIn('id', $sectionIds)
                    ->delete();
            }

            DB::commit();

            return redirect()
                ->route('exams.builder', ['exam' => $exam->id])
                ->with('success', 'Exam updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Exam update error: ' . $e->getMessage());
            return redirect()
                ->route('exams.builder', ['exam' => $exam->id])
                ->with('error', 'Failed to save changes: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam)
    {
        abort_if($exam->team_id !== auth()->user()->currentTeam->id, 403);
        
        $exam->delete(); // This will trigger the cascading delete due to the model events
        
        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully');
    }

    /**
     * Show the exam builder page.
     */
    public function builder(Exam $exam): Response
    {
        // First, ensure all questions are assigned to a section
        $questionsWithoutSection = $exam->questions()
            ->whereNull('section_id')
            ->get();

        if ($questionsWithoutSection->count() > 0) {
            // Create a default section if none exists
            $defaultSection = $exam->sections()
                ->firstOrCreate(
                    ['title' => 'Default Section'],
                    [
                        'description' => 'Default section for existing questions',
                        'position' => 1,
                        'type' => 'default'
                    ]
                );

            // Assign questions to the default section
            foreach ($questionsWithoutSection as $question) {
                $question->update([
                    'section_id' => $defaultSection->id
                ]);
            }
        }

        // Now load the exam with all its relationships
        $exam->load(['sections' => function ($query) {
            $query->orderBy('position');
        }, 'sections.questions' => function ($query) {
            $query->orderBy('position');
        }]);

        return Inertia::render('Exams/Builder', [
            'exam' => $exam,
            'isPublished' => $exam->isPublished()
        ]);
    }

    /**
     * Publish the exam.
     */
    public function publish(Exam $exam)
    {
        // Validate that the exam has at least one question
        if ($exam->questions()->count() < 1) {
            return response()->json([
                'message' => 'Cannot publish an exam without questions'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $exam->update([
                'published_at' => now(),
                'is_draft' => false
            ]);

            DB::commit();

            if (request()->wantsJson()) {
                return response()->noContent();
            }

            return redirect()->route('exams.show', $exam)->with('success', 'Exam published successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function options(Exam $exam): Response
    {
        return Inertia::render('Exams/Options', [
            'exam' => $exam->load(['sections.questions']),
            'isPublished' => $exam->isPublished()
        ]);
    }
} 
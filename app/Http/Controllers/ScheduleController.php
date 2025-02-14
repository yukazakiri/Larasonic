<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Schedule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

final class ScheduleController extends Controller
{
    public function index(): Response
    {
        $schedules = auth()->user()->currentTeam
            ->schedules()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'days' => [
                'Monday', 'Tuesday', 'Wednesday', 
                'Thursday', 'Friday', 'Saturday', 'Sunday'
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_week' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_recurring' => ['boolean'],
        ]);

        auth()->user()->currentTeam->schedules()->create($validated);

        return redirect()->route('schedules.index');
    }

    public function update(Schedule $schedule, Request $request): RedirectResponse
    {
        $this->authorize('update', $schedule);
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_week' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_recurring' => ['boolean'],
        ]);

        $schedule->update($validated);

        return redirect()->route('schedules.index');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);
        
        $schedule->delete();

        return redirect()->route('schedules.index');
    }
} 
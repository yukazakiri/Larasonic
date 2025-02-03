<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;

final class UserDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = Auth::user();
        $teams = Team::query()
            ->whereIn('id', $user->allTeams()->pluck('id'))
            ->withCount(['users', 'exams'])
            ->get();
        
        return Inertia::render('User/Dashboard', [
            'stats' => [
                [
                    'name' => 'Total Teams',
                    'value' => $teams->count(),
                    'icon' => 'lucide:users',
                ],
                [
                    'name' => 'Active Exams',
                    'value' => $teams->sum('exams_count'),
                    'icon' => 'lucide:clipboard-list',
                ],
                [
                    'name' => 'Team Members',
                    'value' => $teams->sum('users_count'),
                    'icon' => 'lucide:user-plus',
                ],
               
            ],
            'teams' => $teams,
        ]);
    }
} 
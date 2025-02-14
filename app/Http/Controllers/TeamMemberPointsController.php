<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

final class TeamMemberPointsController extends Controller
{
    public function __invoke(Request $request, Team $team): Response
    {
        Gate::authorize('view', $team);

        // Eager load the owner relationship
        $team->load('owner');

        $activities = $team->activities()
            ->with(['userPoints.user'])
            ->get();

        $students = $team->users()
            ->where('team_user.role', 'Student')
            ->whereNot('users.id', $team->owner->id)
            ->with(['activityPoints' => function ($query) use ($team) {
                $query->whereHas('activity', function ($q) use ($team) {
                    $q->where('team_id', $team->id);
                });
            }])
            ->get();

        // Calculate total points for each student
        $students = $students->map(function ($student) use ($activities) {
            $pointsByActivity = [];
            foreach ($activities as $activity) {
                $points = $activity->userPoints
                    ->where('user_id', $student->id)
                    ->first()
                    ?->points ?? 0;
                $pointsByActivity[$activity->id] = $points;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'profile_photo_url' => $student->profile_photo_url,
                'points_by_activity' => $pointsByActivity,
                'total_points' => array_sum($pointsByActivity),
            ];
        });

        return Inertia::render('Teams/MemberPoints', [
            'team' => $team,
            'activities' => $activities,
            'students' => $students,
        ]);
    }
} 
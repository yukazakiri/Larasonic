<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityUserPoint;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

final class ActivityController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Activity::class);

        $activities = Activity::query()
            ->where('team_id', auth()->user()->currentTeam->id)
            ->with(['userPoints.user', 'team', 'teamMembers'])
            ->withCount('teamMembers')
            ->latest()
            ->paginate();

        return Inertia::render('Activities/Index', [
            'activities' => $activities,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Activity::class);

        return Inertia::render('Activities/Create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Activity::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'total_points' => ['required', 'integer', 'min:1'],
            'is_group_activity' => ['boolean'],
        ]);

        $activity = Activity::create($validated);

        return redirect()->route('activities.show', $activity);
    }

    public function show(Activity $activity): Response
    {
        Gate::authorize('view', $activity);

        $activity->load([
            'userPoints.user',
            'team',
            'teamMembers',
            'groups.members.user'
        ]);
        
        $teamMembers = $activity->getEligibleTeamMembers();

        return Inertia::render('Activities/Show', [
            'activity' => $activity,
            'teamMembers' => $teamMembers,
        ]);
    }

    public function assignPoints(Request $request, Activity $activity)
    {
        Gate::authorize('assignPoints', $activity);

        $validated = $request->validate([
            'points' => ['required', 'array'],
            'points.*' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string'],
        ]);

        foreach ($validated['points'] as $userId => $points) {
            ActivityUserPoint::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'user_id' => $userId,
                ],
                [
                    'points' => $points,
                    'notes' => $validated['notes'][$userId] ?? null,
                ]
            );
        }

        return back();
    }

    public function manageGroups(Request $request, Activity $activity)
    {
        Gate::authorize('update', $activity);

        $validated = $request->validate([
            'groups' => ['required', 'array'],
            'groups.*.id' => ['nullable', 'exists:activity_groups,id'],
            'groups.*.name' => ['required', 'string'],
            'groups.*.color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'groups.*.points' => ['required', 'integer', 'min:0'],
            'groups.*.members' => ['required', 'array'],
            'groups.*.members.*' => ['required', 'exists:users,id'],
        ]);

        foreach ($validated['groups'] as $groupData) {
            $group = isset($groupData['id']) 
                ? ActivityGroup::find($groupData['id'])
                : new ActivityGroup(['activity_id' => $activity->id]);

            $group->fill([
                'name' => $groupData['name'],
                'color' => $groupData['color'],
                'points' => $groupData['points'],
            ])->save();

            // Sync members
            $group->members()->delete();
            foreach ($groupData['members'] as $userId) {
                $group->members()->create(['user_id' => $userId]);
            }
        }

        return back();
    }

    public function updateMemberPoints(Request $request, Activity $activity, ActivityGroupMember $member)
    {
        Gate::authorize('assignPoints', $activity);

        $validated = $request->validate([
            'points_adjustment' => ['nullable', 'integer'],
            'adjustment_reason' => ['nullable', 'string', 'required_with:points_adjustment'],
        ]);

        $member->update($validated);

        return back();
    }

    public function destroy(Activity $activity)
    {
        Gate::authorize('delete', $activity);
        
        $activity->delete();
        
        return redirect()->route('activities.index')->with('toast', [
            'type' => 'success',
            'message' => 'Activity deleted successfully'
        ]);
    }
} 
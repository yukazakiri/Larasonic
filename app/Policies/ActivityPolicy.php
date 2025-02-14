<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->belongsToTeam($activity->team);
    }

    public function create(User $user): bool
    {
        return $user->ownsTeam($user->currentTeam);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->ownsTeam($activity->team);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->ownsTeam($activity->team);
    }

    public function assignPoints(User $user, Activity $activity): bool
    {
        return $user->ownsTeam($activity->team);
    }
} 
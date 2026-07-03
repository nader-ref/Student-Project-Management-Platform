<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\ProjectMember;
use App\Models\Projectrequest;
use App\Models\User;

class WorkflowGuard
{
    public const MAX_TEAM_SIZE = 3;

    public static function isProjectRequestProcessed(Projectrequest $projectRequest): bool
    {
        return (bool) $projectRequest->accepted || (bool) $projectRequest->rejected;
    }

    public static function isIdeaProcessed(Idea $idea): bool
    {
        return (bool) $idea->accepted || (bool) $idea->rejected;
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function anyUserEnrolledInProject(array $userIds): bool
    {
        if ($userIds === []) {
            return false;
        }

        return ProjectMember::whereIn('user_id', $userIds)->exists();
    }

    /**
     * @param  array<int, array{user: User, position: int}>  $members
     * @return array<int, int>
     */
    public static function userIdsFromMembers(array $members): array
    {
        return array_map(fn (array $member) => $member['user']->id, $members);
    }

    /**
     * @param  array<int, User>  $users
     * @return array<int, int>
     */
    public static function userIdsFromUsers(array $users): array
    {
        return array_map(fn (User $user) => $user->id, $users);
    }

    public static function teamSizeExceedsMax(int $size): bool
    {
        return $size > self::MAX_TEAM_SIZE;
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function anyUserHasPendingProjectRequest(array $userIds): bool
    {
        if ($userIds === []) {
            return false;
        }

        return Projectrequest::query()
            ->where('accepted', 0)
            ->where(function ($query) {
                $query->where('rejected', 0)->orWhereNull('rejected');
            })
            ->whereHas('members', fn ($query) => $query->whereIn('user_id', $userIds))
            ->exists();
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function anyUserHasPendingIdea(array $userIds): bool
    {
        if ($userIds === []) {
            return false;
        }

        return Idea::query()
            ->where('accepted', 0)
            ->where('rejected', 0)
            ->whereHas('members', fn ($query) => $query->whereIn('user_id', $userIds))
            ->exists();
    }
}

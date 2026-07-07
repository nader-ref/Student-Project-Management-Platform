<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public const USER_SUPERVISOR_CREATED = 'user.supervisor_created';

    public const USER_STUDENT_CREATED = 'user.student_created';

    public const USER_ACTIVATED = 'user.activated';

    public const USER_DEACTIVATED = 'user.deactivated';

    public const USER_PASSWORD_RESET = 'user.password_reset';

    public const PROJECT_REQUEST_SUBMITTED = 'project_request.submitted';

    public const PROJECT_REQUEST_ACCEPTED = 'project_request.accepted';

    public const PROJECT_REQUEST_REJECTED = 'project_request.rejected';

    public const IDEA_SUBMITTED = 'idea.submitted';

    public const IDEA_ACCEPTED = 'idea.accepted';

    public const IDEA_REJECTED = 'idea.rejected';

    public const SUBMISSION_UPLOADED = 'submission.uploaded';

    public const SUBMISSION_REVIEWED = 'submission.reviewed';

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function log(
        string $action,
        string $description,
        ?User $actor = null,
        ?User $targetUser = null,
        ?Model $subject = null,
        array $metadata = [],
    ): ?ActivityLog {
        $actor ??= Auth::user();

        try {
            return ActivityLog::create([
                'actor_user_id' => $actor?->id,
                'action' => $action,
                'description' => $description,
                'target_user_id' => $targetUser?->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'metadata' => self::sanitizeMetadata($metadata),
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private static function sanitizeMetadata(array $metadata): array
    {
        $blockedKeys = [
            'password',
            'password_confirmation',
            'token',
            'remember_token',
            'secret',
            'api_key',
            'authorization',
        ];

        $sanitized = [];

        foreach ($metadata as $key => $value) {
            if (in_array(strtolower((string) $key), $blockedKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeMetadata($value);

                continue;
            }

            if (is_string($value) && self::looksSensitive($value)) {
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private static function looksSensitive(string $value): bool
    {
        return str_contains($value, '$2y$')
            || str_contains($value, '$argon2')
            || strlen($value) > 500;
    }
}

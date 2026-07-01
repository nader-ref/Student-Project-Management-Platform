<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\Projectrequest;
use App\Models\UniProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StudentEnrollmentService
{
    public const MODE_DISCOVERY = 'discovery';

    public const MODE_PENDING = 'pending';

    public const MODE_ENROLLED = 'enrolled';

    /**
     * @return array{
     *     mode: string,
     *     project: ?UniProject,
     *     pendingRequest: ?Projectrequest,
     *     pendingIdea: ?Idea,
     *     teamMembers: Collection,
     *     milestones: Collection,
     *     nextMilestone: ?array
     * }
     */
    public static function resolve(?string $studentName = null, ?User $student = null): array
    {
        $student ??= Auth::user() instanceof User ? Auth::user() : null;
        $studentName ??= $student?->name;

        if (! $studentName && ! $student) {
            return self::emptyResult();
        }

        $project = $student
            ? UniProject::with(['supervisor', 'members.user'])
            ->where('taken', 1)
                ->whereHas('members', fn ($query) => $query->where('user_id', $student->id))
                ->first()
            : null;

        if ($project) {
            $milestones = self::buildMilestones($project);
            $nextMilestone = self::nextUpcomingMilestone($milestones);

            return [
                'mode' => self::MODE_ENROLLED,
                'project' => $project,
                'pendingRequest' => null,
                'pendingIdea' => null,
                'teamMembers' => self::teamFromProject($project, $student),
                'milestones' => $milestones,
                'nextMilestone' => $nextMilestone,
            ];
        }

        $pendingRequest = Projectrequest::with(['project', 'members.user'])
            ->when($student, fn ($query) => $query->whereHas(
                'members',
                fn ($memberQuery) => $memberQuery->where('user_id', $student->id),
            ), fn ($query) => $query->whereRaw('1 = 0'))
            ->where('accepted', 0)
            ->where(function ($query) {
                $query->where('rejected', 0)->orWhereNull('rejected');
            })
            ->latest()
            ->first();

        $pendingIdea = Idea::with(['supervisor', 'members.user'])
            ->when($student, fn ($query) => $query->whereHas(
                'members',
                fn ($memberQuery) => $memberQuery->where('user_id', $student->id),
            ), fn ($query) => $query->whereRaw('1 = 0'))
            ->where('accepted', 0)
            ->where('rejected', 0)
            ->latest()
            ->first();

        if ($pendingRequest || $pendingIdea) {
            return [
                'mode' => self::MODE_PENDING,
                'project' => null,
                'pendingRequest' => $pendingRequest,
                'pendingIdea' => $pendingIdea,
                'teamMembers' => collect(),
                'milestones' => collect(),
                'nextMilestone' => null,
            ];
        }

        return self::emptyResult();
    }

    private static function emptyResult(): array
    {
        return [
            'mode' => self::MODE_DISCOVERY,
            'project' => null,
            'pendingRequest' => null,
            'pendingIdea' => null,
            'teamMembers' => collect(),
            'milestones' => collect(),
            'nextMilestone' => null,
        ];
    }

    public static function teamFromProject(UniProject $project, ?User $currentStudent = null): Collection
    {
        $project->loadMissing('members.user');

        return $project->members
            ->sortBy('position')
            ->map(fn ($member) => [
                'name' => $member->user->name,
                'id' => $member->user->university_number,
                'is_you' => $currentStudent && $member->user_id === $currentStudent->id,
            ])
            ->values();
    }

    public static function buildMilestones(UniProject $project): Collection
    {
        $labels = [
            'seminar_1' => 'Seminar 1',
            'seminar_2' => 'Seminar 2',
            'seminar_3' => 'Seminar 3',
            'final' => 'Final Presentation',
        ];

        $milestones = collect();

        foreach ($labels as $field => $label) {
            if (empty($project->$field)) {
                continue;
            }

            try {
                $date = Carbon::parse($project->$field);
                $milestones->push([
                    'key' => $field,
                    'label' => $label,
                    'date' => $date,
                    'formatted' => $date->format('M d, Y'),
                    'is_past' => $date->isPast(),
                    'days_left' => $date->isFuture() ? (int) now()->diffInDays($date, false) : 0,
                ]);
            } catch (\Exception $e) {
            }
        }

        return $milestones->sortBy('date')->values();
    }

    public static function nextUpcomingMilestone(Collection $milestones): ?array
    {
        return $milestones->first(fn ($m) => ! $m['is_past']) ?: null;
    }

    public static function milestoneLabels(): array
    {
        return [
            'seminar_1' => 'Seminar 1',
            'seminar_2' => 'Seminar 2',
            'seminar_3' => 'Seminar 3',
            'final' => 'Final Presentation',
            'other' => 'Other / General',
        ];
    }

    /**
     * @return array{percent: int, current_phase: string, steps: Collection}
     */
    public static function computeProgress(UniProject $project, Collection $milestones, Collection $submissions): array
    {
        $steps = collect([
            [
                'key' => 'enrolled',
                'label' => 'Team Registered',
                'done' => true,
            ],
        ]);

        foreach ($milestones as $milestone) {
            $approved = $submissions
                ->where('milestone', $milestone['key'])
                ->where('status', 'approved')
                ->isNotEmpty();

            $steps->push([
                'key' => $milestone['key'],
                'label' => $milestone['label'],
                'done' => $milestone['is_past'] || $approved,
                'date' => $milestone['formatted'],
            ]);
        }

        $totalSteps = max($steps->count(), 1);
        $doneSteps = $steps->where('done', true)->count();
        $percent = (int) round(($doneSteps / $totalSteps) * 100);

        $currentPhase = $steps->first(fn ($s) => ! $s['done'])['label'] ?? 'Completed';

        if ($project->status) {
            $currentPhase = ucwords(str_replace('_', ' ', $project->status));
        }

        return [
            'percent' => $percent,
            'current_phase' => $currentPhase,
            'steps' => $steps,
        ];
    }

    /**
     * @return Collection<int, array{priority: string, icon: string, title: string, description: string, tab: string, cta: string}>
     */
    public static function buildNextSteps(
        UniProject $project,
        Collection $milestones,
        Collection $submissions,
        ?array $nextMilestone,
        Collection $contacts,
    ): Collection {
        $steps = collect();
        $labels = self::milestoneLabels();

        foreach ($submissions->where('status', 'needs_revision') as $submission) {
            $milestoneLabel = $labels[$submission->milestone] ?? $submission->milestone;
            $steps->push([
                'priority' => 'urgent',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'Revise your '.$milestoneLabel.' submission',
                'description' => $submission->supervisor_feedback
                    ? \Illuminate\Support\Str::limit($submission->supervisor_feedback, 120)
                    : 'Your supervisor requested changes. Upload an updated file.',
                'tab' => 'submissions',
                'cta' => 'Revise Upload',
            ]);
        }

        if ($nextMilestone) {
            $milestoneKey = $nextMilestone['key'];
            $milestoneSubs = $submissions->where('milestone', $milestoneKey);
            $hasApproved = $milestoneSubs->where('status', 'approved')->isNotEmpty();
            $awaitingReview = $milestoneSubs->where('status', 'submitted')->isNotEmpty();

            if (! $hasApproved && ! $awaitingReview && $nextMilestone['days_left'] <= 21) {
                $priority = $nextMilestone['days_left'] <= 7 ? 'urgent' : 'high';
                $steps->push([
                    'priority' => $priority,
                    'icon' => 'fas fa-file-upload',
                    'title' => 'Submit '.$nextMilestone['label'].' before '.$nextMilestone['formatted'],
                    'description' => $nextMilestone['days_left'].' day'.($nextMilestone['days_left'] === 1 ? '' : 's').' remaining — upload your deliverable for review.',
                    'tab' => 'submissions',
                    'cta' => 'Upload File',
                ]);
            } elseif ($awaitingReview) {
                $steps->push([
                    'priority' => 'normal',
                    'icon' => 'fas fa-hourglass-half',
                    'title' => $nextMilestone['label'].' submission under review',
                    'description' => 'Your file has been sent to the supervisor. You will be notified once it is reviewed.',
                    'tab' => 'submissions',
                    'cta' => 'View Submissions',
                ]);
            }
        }

        $latestReply = $contacts->first(fn ($c) => ! empty($c->Replay));
        if ($latestReply) {
            $steps->push([
                'priority' => 'high',
                'icon' => 'fas fa-reply',
                'title' => 'Supervisor replied: '.\Illuminate\Support\Str::limit($latestReply->subject, 40),
                'description' => \Illuminate\Support\Str::limit($latestReply->Replay, 100),
                'tab' => 'message',
                'cta' => 'View Messages',
            ]);
        }

        if ($submissions->isEmpty()) {
            $steps->push([
                'priority' => 'normal',
                'icon' => 'fas fa-cloud-upload-alt',
                'title' => 'Upload your first project file',
                'description' => 'Start tracking your progress by submitting your first milestone deliverable.',
                'tab' => 'submissions',
                'cta' => 'Submit Now',
            ]);
        }

        if ($contacts->isEmpty()) {
            $supervisorName = $project->supervisor->name ?? 'your supervisor';
            $steps->push([
                'priority' => 'normal',
                'icon' => 'fas fa-envelope',
                'title' => 'Introduce your team to '.$supervisorName,
                'description' => 'Send a short message to confirm your enrollment and ask about first steps.',
                'tab' => 'message',
                'cta' => 'Send Message',
            ]);
        }

        if ($nextMilestone && $nextMilestone['days_left'] <= 14) {
            $alreadyListed = $steps->contains(fn ($s) => str_contains($s['title'], $nextMilestone['label']));
            if (! $alreadyListed) {
                $steps->push([
                    'priority' => $nextMilestone['days_left'] <= 7 ? 'urgent' : 'high',
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Prepare for '.$nextMilestone['label'],
                    'description' => 'Scheduled for '.$nextMilestone['formatted'].' ('.$nextMilestone['days_left'].' days left).',
                    'tab' => 'timeline',
                    'cta' => 'View Timeline',
                ]);
            }
        }

        if ($steps->isEmpty()) {
            $steps->push([
                'priority' => 'normal',
                'icon' => 'fas fa-check-circle',
                'title' => 'You are on track',
                'description' => 'No urgent actions right now. Review your timeline or check in with your supervisor.',
                'tab' => 'timeline',
                'cta' => 'View Timeline',
            ]);
        }

        $priorityOrder = ['urgent' => 0, 'high' => 1, 'normal' => 2];

        return $steps
            ->sortBy(fn ($step) => $priorityOrder[$step['priority']] ?? 3)
            ->take(4)
            ->values();
    }

    /**
     * @return Collection<int, array{type: string, icon: string, tone: string, title: string, meta: string, tab: ?string, timestamp: \Carbon\Carbon}>
     */
    public static function buildRecentActivity(
        Collection $submissions,
        Collection $contacts,
        Collection $announcements,
    ): Collection {
        $events = collect();
        $labels = self::milestoneLabels();

        foreach ($submissions as $submission) {
            $milestoneLabel = $labels[$submission->milestone] ?? $submission->milestone;

            $events->push([
                'type' => 'submission',
                'icon' => 'fas fa-file-upload',
                'tone' => 'info',
                'title' => 'Uploaded: '.$submission->title,
                'meta' => $milestoneLabel.' · '.ucfirst(str_replace('_', ' ', $submission->status)),
                'tab' => 'submissions',
                'timestamp' => $submission->created_at,
            ]);

            if (in_array($submission->status, ['approved', 'needs_revision'], true)) {
                $events->push([
                    'type' => 'submission_review',
                    'icon' => $submission->status === 'approved' ? 'fas fa-check-circle' : 'fas fa-redo',
                    'tone' => $submission->status === 'approved' ? 'success' : 'warning',
                    'title' => $submission->status === 'approved'
                        ? 'Submission approved: '.$submission->title
                        : 'Revision requested: '.$submission->title,
                    'meta' => $milestoneLabel.($submission->supervisor_feedback
                        ? ' · '.\Illuminate\Support\Str::limit($submission->supervisor_feedback, 60)
                        : ''),
                    'tab' => 'submissions',
                    'timestamp' => $submission->updated_at ?? $submission->created_at,
                ]);
            }
        }

        foreach ($contacts as $contact) {
            $supervisorName = $contact->supervisor?->name ?? $contact->supname;

            $events->push([
                'type' => 'message_sent',
                'icon' => 'fas fa-paper-plane',
                'tone' => 'info',
                'title' => 'Message sent: '.$contact->subject,
                'meta' => 'To '.$supervisorName,
                'tab' => 'message',
                'timestamp' => $contact->created_at,
            ]);

            if (! empty($contact->Replay)) {
                $events->push([
                    'type' => 'message_reply',
                    'icon' => 'fas fa-reply',
                    'tone' => 'success',
                    'title' => 'Supervisor replied: '.$contact->subject,
                    'meta' => \Illuminate\Support\Str::limit($contact->Replay, 80),
                    'tab' => 'message',
                    'timestamp' => $contact->updated_at ?? $contact->created_at,
                ]);
            }
        }

        foreach ($announcements as $announcement) {
            $events->push([
                'type' => 'announcement',
                'icon' => 'fas fa-bullhorn',
                'tone' => 'info',
                'title' => 'Announcement: '.$announcement->subject,
                'meta' => \Illuminate\Support\Str::limit($announcement->Message, 80),
                'tab' => 'message',
                'timestamp' => $announcement->created_at,
            ]);
        }

        return $events
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();
    }
}

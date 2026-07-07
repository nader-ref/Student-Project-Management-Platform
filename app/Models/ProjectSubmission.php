<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'submitted_by_user_id',
        'milestone',
        'title',
        'file_path',
        'original_filename',
        'notes',
        'status',
        'supervisor_feedback',
        'reviewed_at',
        'reviewed_by_user_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(UniProject::class, 'project_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'Approved',
            'needs_revision' => 'Revision Required',
            'submitted' => 'Pending Review',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'submitted';
    }

    public function needsRevision(): bool
    {
        return $this->status === 'needs_revision';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

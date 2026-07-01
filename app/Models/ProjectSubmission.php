<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'submitted_by_user_id',
        'student_email',
        'student_name',
        'milestone',
        'title',
        'file_path',
        'original_filename',
        'notes',
        'status',
        'supervisor_feedback',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(UniProject::class, 'project_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}

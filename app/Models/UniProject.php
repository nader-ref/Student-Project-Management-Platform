<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniProject extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'seminar_1' => 'date',
        'seminar_2' => 'date',
        'seminar_3' => 'date',
        'final' => 'date',
    ];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('project_members.position');
    }

    public function projectRequests(): HasMany
    {
        return $this->hasMany(projectrequest::class, 'project_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(supcontact::class, 'project_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class, 'project_id');
    }
}

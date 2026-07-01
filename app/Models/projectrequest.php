<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class projectrequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(UniProject::class, 'project_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectRequestMember::class, 'project_request_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_request_members', 'project_request_id', 'user_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('project_request_members.position');
    }
}

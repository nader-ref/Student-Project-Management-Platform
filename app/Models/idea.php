<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class idea extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(IdeaMember::class, 'idea_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'idea_members', 'idea_id', 'user_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('idea_members.position');
    }
}

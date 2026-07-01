<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supervisor extends Model
{
    use HasFactory;

    public $guarded = [];

    public function UniProjects()
    {
        return $this->hasMany(UniProject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(idea::class, 'supervisor_id');
    }

    public function studentMessages(): HasMany
    {
        return $this->hasMany(contact::class, 'supervisor_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(supcontact::class, 'supervisor_id');
    }
}

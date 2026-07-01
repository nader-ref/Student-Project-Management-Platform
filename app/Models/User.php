<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;


class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'university_number',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class);
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'user_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(UniProject::class, 'project_members', 'user_id', 'project_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('project_members.position');
    }

    public function submittedProjectRequests(): HasMany
    {
        return $this->hasMany(projectrequest::class, 'requested_by_user_id');
    }

    public function projectRequestMemberships(): HasMany
    {
        return $this->hasMany(ProjectRequestMember::class, 'user_id');
    }

    public function projectRequests(): BelongsToMany
    {
        return $this->belongsToMany(projectrequest::class, 'project_request_members', 'user_id', 'project_request_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('project_request_members.position');
    }

    public function submittedIdeas(): HasMany
    {
        return $this->hasMany(idea::class, 'requested_by_user_id');
    }

    public function ideaMemberships(): HasMany
    {
        return $this->hasMany(IdeaMember::class, 'user_id');
    }

    public function ideas(): BelongsToMany
    {
        return $this->belongsToMany(idea::class, 'idea_members', 'user_id', 'idea_id')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('idea_members.position');
    }

    public function resolveDashboardRoute(): ?string
    {
        if ($this->hasRole('admin')) {
            return '/admin';
        }

        if ($this->hasRole('supervisor')) {
            return '/supervisorDashboard';
        }

        if ($this->hasRole('student')) {
            return '/StudentDashboard';
        }

        return null;
    }
}

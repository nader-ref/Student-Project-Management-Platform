<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Idea extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'accepted' => 'boolean',
        'rejected' => 'boolean',
        'similarity_percentage' => 'float',
        'similarity_checked_at' => 'datetime',
        'similarity_match_source_id' => 'integer',
    ];

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

    public function hasSimilarityMatch(): bool
    {
        return $this->similarity_status === 'matched'
            && $this->similarity_percentage !== null;
    }

    public function similarityStatusLabel(): string
    {
        return match ($this->similarity_status) {
            'matched' => 'Matched',
            'no_match' => 'No significant similarity',
            'unavailable' => 'Similarity unavailable',
            default => 'Not analyzed',
        };
    }

    public function similarityDisplayLabel(): string
    {
        if ($this->hasSimilarityMatch()) {
            $level = ucfirst((string) $this->similarity_level);

            return number_format((float) $this->similarity_percentage, 1).'% · '.$level;
        }

        return $this->similarityStatusLabel();
    }

    public function similarityMatchSourceLabel(): string
    {
        return match ($this->similarity_match_source_type) {
            'existing_project' => 'Existing Project',
            'accepted_idea' => 'Accepted Idea',
            default => 'Record',
        };
    }
}

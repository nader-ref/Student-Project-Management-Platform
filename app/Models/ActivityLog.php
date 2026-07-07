<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_user_id',
        'action',
        'description',
        'target_user_id',
        'subject_type',
        'subject_id',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actorLabel(): string
    {
        if (! $this->actor) {
            return 'System';
        }

        $label = $this->actor->name;

        if (filled($this->actor->university_number)) {
            $label .= ' ('.$this->actor->university_number.')';
        }

        return $label;
    }

    public function targetLabel(): string
    {
        if (! $this->targetUser) {
            return '—';
        }

        $label = $this->targetUser->name;

        if (filled($this->targetUser->university_number)) {
            $label .= ' ('.$this->targetUser->university_number.')';
        }

        return $label;
    }

    public function subjectLabel(): string
    {
        if (! $this->subject_type || ! $this->subject_id) {
            return '—';
        }

        return class_basename($this->subject_type).' #'.$this->subject_id;
    }

    public function metadataSummary(int $limit = 120): string
    {
        if (empty($this->metadata)) {
            return '—';
        }

        $parts = collect($this->metadata)->map(function ($value, $key) {
            if (is_array($value)) {
                return $key.': ['.implode(', ', array_map('strval', $value)).']';
            }

            return $key.': '.$value;
        });

        return Str::limit($parts->implode(' · '), $limit);
    }
}

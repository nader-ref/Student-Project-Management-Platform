<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'idea_id',
        'user_id',
        'position',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(idea::class, 'idea_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

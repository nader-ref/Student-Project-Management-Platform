<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequestMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_request_id',
        'user_id',
        'position',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(projectrequest::class, 'project_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class supcontact extends Model
{
    use HasFactory;
     protected $guarded = [];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(UniProject::class, 'project_id');
    }
}

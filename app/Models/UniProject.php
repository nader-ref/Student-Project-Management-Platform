<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniProject extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }
}

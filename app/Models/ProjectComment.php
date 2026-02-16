<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectComment extends Model
{
    protected $fillable = [
        'discussion_id',
        'content',
        'created_by',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(ProjectDiscussion::class, 'discussion_id');
    }
}

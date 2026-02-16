<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'description',
        'created_by',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(KnowledgeDocument::class);
    }
}

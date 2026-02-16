<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    protected $fillable = [
        'name',
        'funnel_id',
        'order',
        'color',
        'chatwoot_status',
        'automations',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}

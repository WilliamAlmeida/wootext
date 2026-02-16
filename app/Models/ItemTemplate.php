<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemTemplate extends Model
{
    protected $fillable = [
        'funnel_id',
        'account_id',
        'title',
        'description',
        'value',
        'order',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function cardItems(): HasMany
    {
        return $this->hasMany(CardItem::class, 'template_id');
    }
}

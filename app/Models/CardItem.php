<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardItem extends Model
{
    protected $fillable = [
        'card_id',
        'conversation_id',
        'account_id',
        'template_id',
        'title',
        'description',
        'value',
        'quantity',
        'order',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ItemTemplate::class, 'template_id');
    }
}

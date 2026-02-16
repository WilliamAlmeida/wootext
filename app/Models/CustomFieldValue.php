<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'card_id',
        'field_id',
        'conversation_id',
        'account_id',
        'value',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'field_id');
    }
}

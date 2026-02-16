<?php

namespace App\Models;

use App\Traits\HasChatwootAccountScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasChatwootAccountScope;

    protected $fillable = [
        'conversation_id',
        'stage_id',
        'account_id',
        'contact_id',
        'order',
        'custom_name',
        'phone_number',
        'transferred_from',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CardItem::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function scheduledMessages(): HasMany
    {
        return $this->hasMany(ScheduledMessage::class, 'conversation_id', 'conversation_id');
    }
}

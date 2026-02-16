<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalChat extends Model
{
    protected $fillable = [
        'name',
        'account_id',
        'created_by',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(InternalChatMember::class, 'chat_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InternalChatMessage::class, 'chat_id');
    }
}

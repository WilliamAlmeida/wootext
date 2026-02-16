<?php

namespace App\Models;

use App\Traits\HasChatwootAccountScope;
use Illuminate\Database\Eloquent\Model;

class ScheduledMessage extends Model
{
    use HasChatwootAccountScope;

    protected $fillable = [
        'conversation_id',
        'account_id',
        'message',
        'scheduled_at',
        'status',
        'sent_at',
        'error_message',
        'created_by',
        'api_token',
        'jwt_access_token',
        'jwt_client',
        'jwt_uid',
        'jwt_expiry',
        'jwt_token_type',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    public function card()
    {
        return $this->belongsTo(Card::class, 'conversation_id', 'conversation_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowExecution extends Model
{
    protected $fillable = [
        'flow_id',
        'conversation_id',
        'account_id',
        'status',
        'current_node_id',
        'context',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ChatbotFlow::class, 'flow_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotFlow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'account_id',
        'is_active',
        'trigger',
        'flow_data',
        'agent_bot_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function executions(): HasMany
    {
        return $this->hasMany(FlowExecution::class, 'flow_id');
    }
}

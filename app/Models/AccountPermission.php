<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPermission extends Model
{
    protected $fillable = [
        'account_id',
        'kanban_enabled',
        'chats_internos_enabled',
        'conexoes_enabled',
        'projects_enabled',
        'chatbot_flows_enabled',
        'allowed_providers',
    ];

    protected function casts(): array
    {
        return [
            'kanban_enabled' => 'boolean',
            'chats_internos_enabled' => 'boolean',
            'conexoes_enabled' => 'boolean',
            'projects_enabled' => 'boolean',
            'chatbot_flows_enabled' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserResourcePermission extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'kanban_access',
        'conexoes_access',
        'chats_internos_access',
        'projects_access',
        'chatbot_flows_access',
        'permissoes_access',
    ];

    protected function casts(): array
    {
        return [
            'kanban_access' => 'boolean',
            'conexoes_access' => 'boolean',
            'chats_internos_access' => 'boolean',
            'projects_access' => 'boolean',
            'chatbot_flows_access' => 'boolean',
            'permissoes_access' => 'boolean',
        ];
    }
}

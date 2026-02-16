<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'conversation_id',
        'account_id',
        'title',
        'completed',
        'due_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'due_date' => 'datetime',
        ];
    }
}

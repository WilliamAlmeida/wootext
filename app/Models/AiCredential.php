<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCredential extends Model
{
    protected $fillable = [
        'account_id',
        'provider',
        'api_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

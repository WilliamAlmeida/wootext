<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowVariable extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'default_value',
        'description',
    ];
}

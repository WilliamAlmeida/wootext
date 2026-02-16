<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'conversation_id',
        'account_id',
        'file_name',
        'original_name',
        'file_size',
        'mime_type',
        'file_path',
        'uploaded_by',
    ];
}

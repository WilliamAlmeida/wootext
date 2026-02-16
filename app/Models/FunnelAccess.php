<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelAccess extends Model
{
    protected $fillable = [
        'funnel_id',
        'user_id',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }
}

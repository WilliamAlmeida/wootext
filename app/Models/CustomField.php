<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $fillable = [
        'funnel_id',
        'name',
        'field_type',
        'options',
        'required',
        'order',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'field_id');
    }
}

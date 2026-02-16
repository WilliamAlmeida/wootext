<?php

namespace App\Models;

use App\Traits\HasChatwootAccountScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funnel extends Model
{
    use HasChatwootAccountScope;

    protected $fillable = [
        'name',
        'account_id',
        'order',
        'color',
        'is_public',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }

    public function itemTemplates(): HasMany
    {
        return $this->hasMany(ItemTemplate::class);
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(FunnelAccess::class);
    }
}

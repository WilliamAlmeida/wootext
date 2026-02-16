<?php

namespace App\Traits;

trait HasChatwootAccountScope
{
    public static function bootHasChatwootAccountScope(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\ChatwootAccount);
    }
}

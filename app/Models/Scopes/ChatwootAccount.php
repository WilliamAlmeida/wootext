<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ChatwootAccount implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $accountId = config('services.chatwoot.account_id', auth()->user()->account_id ?? null);

        if ($accountId) {
            $builder->where('account_id', $accountId);
        }
    }
}

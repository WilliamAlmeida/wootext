<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserVinculed
{
    /**
     * Handle an incoming request and add CSP frame-ancestors header allowing the hosting origin.
     */
    public function handle(Request $request, Closure $next)
    {
        $account_id = $request->user()?->account_id;

        if($account_id === null) {
             return redirect()->route('settings', ['tab' => 'chatwoot'])->withErrors('Your account is not vinculed to any organization. Please contact your administrator.');
        }

        config()->set('services.chatwoot.account_id', $account_id);

        return $next($request);
    }
}

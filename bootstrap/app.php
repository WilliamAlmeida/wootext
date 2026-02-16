<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable Sanctum's stateful frontend support for SPA/auth via session cookie
        $middleware->statefulApi();

        // Append middleware that allows embedding this app in the specified origin via iframe
        $middleware->append(App\Http\Middleware\AllowIframe::class);

        $middleware->alias([
            'user_vinculed' => App\Http\Middleware\UserVinculed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

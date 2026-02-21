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
        // Append to the default 'web' group (Inertia + preload assets)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register your custom middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Optional: you can add more config here in the future, e.g.
        // $middleware->append(...);
        // $middleware->prepend(...);
        // $middleware->priority([...]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
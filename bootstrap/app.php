<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware): void {
        $middleware->alias([
            'role'  => \App\Http\Middleware\RoleMiddleware::class,
            'local' => \App\Http\Middleware\EnsureLocalContext::class,
            'mozo.activo' => \App\Http\Middleware\EnsureMozoActivo::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

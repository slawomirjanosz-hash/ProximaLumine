<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'railway.session' => \App\Http\Middleware\RailwaySessionFix::class,
        ]);
        
        // Trust all proxies for Railway reverse proxy
        $middleware->trustProxies(at: '*');
        
        // Add Railway session fix to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\RailwaySessionFix::class,
            \App\Http\Middleware\RailwayDebug::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

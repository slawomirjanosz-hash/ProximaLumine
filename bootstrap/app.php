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
        // Globalna obsługa błędu 419 - TokenMismatchException
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            \Log::warning('TokenMismatchException (419) caught globally', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => $request->session()->getId(),
                'ip' => $request->ip(),
            ]);
            
            // Regeneruj token sesji
            $request->session()->regenerateToken();
            
            // Jeśli to AJAX request, zwróć JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesja wygasła. Odśwież stronę i spróbuj ponownie.',
                    'error' => 'token_mismatch',
                    'csrf_token' => csrf_token(),
                ], 419);
            }
            
            // Dla zwykłych requestów, przekieruj z komunikatem
            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('error', 'Sesja wygasła. Spróbuj ponownie.');
        });
    })->create();

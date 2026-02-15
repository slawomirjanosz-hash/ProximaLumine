<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RailwayDebug
{
    /**
     * Handle an incoming request to add debug info for Railway issues.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log Railway-specific debugging info for authorization issues
        if ($request->isMethod('POST') && str_contains($request->path(), 'autoryzuj')) {
            \Log::info('Railway debug - Authorization request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'session_id' => $request->session()->getId(),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'session_token' => $request->session()->token(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'is_https' => $request->secure(),
                'app_env' => app()->environment(),
                'session_driver' => config('session.driver'),
                'session_domain' => config('session.domain'),
                'session_secure' => config('session.secure'),
                'session_same_site' => config('session.same_site'),
            ]);
        }
        
        $response = $next($request);
        
        // Add Railway-specific headers for debugging
        if (app()->environment('production')) {
            $response->header('X-Railway-Debug', 'enabled');
            $response->header('X-Session-ID', $request->session()->getId());
            $response->header('X-CSRF-Token-Match', $request->header('X-CSRF-TOKEN') === $request->session()->token() ? 'yes' : 'no');
        }
        
        return $response;
    }
}
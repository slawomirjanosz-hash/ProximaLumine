<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RailwaySessionFix
{
    /**
     * Handle an incoming request to fix Railway session issues.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fix Railway session issues
        if (app()->environment('production')) {
            // Ensure session starts properly on Railway
            if (!$request->session()->isStarted()) {
                $request->session()->start();
            }
            
            // Add CSRF token to all responses for better Railway compatibility
            $response = $next($request);
            
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                if (!isset($data['csrf_token'])) {
                    $data['csrf_token'] = csrf_token();
                    $response->setData($data);
                }
            }
            
            return $response;
        }
        
        return $next($request);
    }
}
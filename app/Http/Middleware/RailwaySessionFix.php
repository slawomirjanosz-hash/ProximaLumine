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
            \Log::info('RailwaySessionFix: Processing request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_started' => $request->session()->isStarted(),
                'session_id' => $request->session()->getId(),
                'has_csrf' => $request->header('X-CSRF-TOKEN') !== null,
            ]);
            
            // Ensure session starts properly on Railway
            if (!$request->session()->isStarted()) {
                $request->session()->start();
                \Log::info('RailwaySessionFix: Session started manually');
            }
            
            // Regenerate CSRF token on login page to avoid stale tokens
            if ($request->is('login') && $request->isMethod('GET')) {
                $request->session()->regenerateToken();
                \Log::info('RailwaySessionFix: CSRF token regenerated for login page');
            }
            
            // Add CSRF token to all responses for better Railway compatibility
            $response = $next($request);
            
            // Handle 419 errors explicitly
            if ($response->getStatusCode() === 419) {
                \Log::warning('RailwaySessionFix: 419 error detected', [
                    'url' => $request->fullUrl(),
                    'session_id' => $request->session()->getId(),
                    'token_mismatch' => true,
                ]);
                
                // Regenerate token and redirect back to login
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', 'Sesja wygasła. Spróbuj zalogować się ponownie.');
            }
            
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
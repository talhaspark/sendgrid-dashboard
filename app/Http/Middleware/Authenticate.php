<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
       protected function redirectTo(Request $request): ?string
    {
        // For API / AJAX requests return null (will get a 401 JSON response).
        // For normal web requests redirect to the login page.
        return $request->expectsJson() ? null : route('login');
    }
}

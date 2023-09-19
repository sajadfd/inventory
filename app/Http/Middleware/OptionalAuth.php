<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = \request()->header('Authorization');
        if (isset($authorizationHeader)) {
            if ($user = auth('sanctum')->user()) {
                auth()->login($user);
            }
        }

        return $next($request);
    }
}

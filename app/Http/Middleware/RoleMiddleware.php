<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that checks the authenticated user's role.
 * Usage: ->middleware('role:admin')
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! isset($user->role) || $user->role !== $role) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

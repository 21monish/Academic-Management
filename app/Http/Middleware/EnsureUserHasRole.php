<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage in routes: ->middleware('role:Super Admin')
     * or multiple:      ->middleware('role:Super Admin,Principal')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $userRole = $user?->role?->role_name;

        if (! $user || ! in_array($userRole, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

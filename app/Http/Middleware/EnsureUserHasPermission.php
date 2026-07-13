<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Usage in routes: ->middleware('permission:student.view')
     * Multiple permissions are treated as OR: ->middleware('permission:fee_collection.view,fee_collection.create')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || $permissions === []) {
            abort(403, 'You do not have permission to access this page.');
        }

        foreach ($permissions as $permission) {
            if (hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}

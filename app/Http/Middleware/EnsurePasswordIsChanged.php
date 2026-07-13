<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.change*')) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('password.change.show');
        }

        return $next($request);
    }
}

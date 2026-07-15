<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.force_https') || $request->isSecure()) {
            return $next($request);
        }

        return redirect()->secure($request->getRequestUri(), 308);
    }
}

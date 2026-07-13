<?php

namespace App\Http\Middleware;

use App\Services\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareAccessScope
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        if ($request->user()) {
            $scope = $this->accessScope->forUser($request->user());
            $request->session()->put('access_scope', $scope);
            View::share('accessScope', $scope);
        }

        return $next($request);
    }
}

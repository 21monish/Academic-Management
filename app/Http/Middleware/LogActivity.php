<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->routeIs('logout')) {
            return $response;
        }

        if ($request->user() && Schema::hasTable('activity_logs') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            ActivityLog::create([
                'user_id' => $request->user()->user_id,
                'method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'url' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'status_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}

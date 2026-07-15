<?php

namespace App\Http\Middleware;

use App\Services\AccessScopeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
            try {
                $scope = $this->accessScope->forUser($request->user());
            } catch (Throwable $exception) {
                report($exception);

                $user = $request->user();
                $scope = [
                    'role' => $user?->role?->role_name,
                    'level' => $user?->role?->role_name === 'Super Admin' ? 'system' : 'own',
                    'university_id' => $user?->university_id,
                    'college_id' => $user?->college_id,
                    'dept_id' => $user?->dept_id,
                    'programme_ids' => $user?->programme_id ? [(int) $user->programme_id] : [],
                    'subject_ids' => [],
                    'semester_ids' => [],
                    'staff_id' => null,
                ];
            }

            try {
                $request->session()->put('access_scope', $scope);
            } catch (Throwable $exception) {
                report($exception);
            }

            View::share('accessScope', $scope);
        }

        return $next($request);
    }
}

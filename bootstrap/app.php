<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePasswordIsChanged::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ShareAccessScope::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\LogActivity::class);
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('status', 'Your session expired. Please log in again.');
        });
    })->create();

<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // Simple authorize helper used by resource controllers.
    // Keeps compatibility with controllers calling $this->authorize(...)
    // even if base Laravel policies/traits are customized/removed.
    protected function authorize(string $ability, mixed $arguments = []): void
    {
        $this->authorizeGate($ability, $arguments);
    }

    private function authorizeGate(string $ability, mixed $arguments = []): void
    {
        $user = auth()->user();

        // If gate/policy exists, let Laravel decide.
        if (app('auth')->guard() && $user) {
            if (is_array($arguments) && $arguments !== []) {
                if (!\Illuminate\Support\Facades\Gate::allows($ability, $arguments)) {
                    abort(403);
                }
            } else {
                if (!\Illuminate\Support\Facades\Gate::allows($ability)) {
                    abort(403);
                }
            }
        } else {
            abort(403);
        }
    }
}

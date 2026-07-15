<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        $moduleAbilities = [
            'programme.view',
            'programme.create',
            'programme.profile',
            'programme.edit',
            'programme.delete',
            'semester.view',
            'semester.create',
            'semester.update',
            'semester.delete',
            'subject.view',
            'subject.create',
            'subject.update',
            'subject.delete',
            'subject.activate',
            'subject.deactivate',
            'student.view',
            'student.create',
            'student.update',
            'student.delete',
            'user_permission.view',
            'user_permission.update',
            'attendance.view',
            'attendance.create',
            'exam.view',
            'exam.create',
            'fee.view',
            'fee.create',
            'leave.view',
            'leave.create',
            'notice.view',
            'notice.create',
            'report.view',
        ];

        foreach ($moduleAbilities as $ability) {
            Gate::define($ability, fn ($user) => $this->hasModulePermission($user, $ability));
        }
    }

    private function hasModulePermission($user, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        [$module, $action] = normalizePermissionParts($ability);
        $modules = permissionModuleAliases($module);

        return Permission::query()
            ->whereIn('module_name', $modules)
            ->where('action', $action)
            ->whereHas('users', fn ($query) => $query->where('users.user_id', $user->user_id))
            ->exists();
    }
}

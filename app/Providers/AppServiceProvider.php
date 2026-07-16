<?php

namespace App\Providers;

use App\Models\Permission;
use App\Services\LicenseService;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
            if ($rootUrl = $this->httpsUrl(config('app.url'))) {
                URL::forceRootUrl($rootUrl);
            }

            URL::forceScheme('https');

            app(Vite::class)->createAssetPathsUsing(function (string $path): string {
                if ($assetUrl = $this->httpsUrl(config('app.asset_url') ?: config('app.url'))) {
                    return rtrim($assetUrl, '/').'/'.ltrim($path, '/');
                }

                return secure_asset($path);
            });
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
            'certificate.view',
        ];

        foreach ($moduleAbilities as $ability) {
            Gate::define($ability, fn ($user) => $this->hasModulePermission($user, $ability));
        }
    }

    private function httpsUrl(mixed $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        return preg_replace('/^http:\/\//i', 'https://', rtrim($url, '/'));
    }

    private function hasModulePermission($user, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        [$module, $action] = normalizePermissionParts($ability);
        $modules = permissionModuleAliases($module);

        try {
            if (! Schema::hasTable('permissions') || ! Schema::hasTable('user_permissions')) {
                return false;
            }

            $hasPermission = Permission::query()
                ->whereIn('module_name', $modules)
                ->where('action', $action)
                ->whereHas('users', fn ($query) => $query->where('users.user_id', $user->user_id))
                ->exists();

            return $hasPermission && app(LicenseService::class)->canAccessPermission($user, $ability);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}

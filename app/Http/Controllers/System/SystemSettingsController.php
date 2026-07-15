<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingService;
use App\Services\UploadService;
use App\Support\ValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SystemSettingService $settings,
        protected UploadService $uploads
    ) {
    }

    public function __invoke(): View
    {
        $settings = $this->settings->all();

        return view('system.settings', [
            'settings' => $settings,
            'logoPreviewUrl' => $this->settings->publicUrl($settings['logo_url']),
            'canUpdate' => hasPermission('system_settings.update'),
            'sections' => $this->technicalSections(),
            'maintenance' => [
                'enabled' => app()->isDownForMaintenance(),
                'driver' => config('app.maintenance.driver', env('APP_MAINTENANCE_DRIVER', 'file')),
            ],
            'commands' => [
                'Apply .env changes' => 'php artisan config:clear',
                'Cache config for production' => 'php artisan config:cache',
                'Enable maintenance mode' => 'php artisan down',
                'Disable maintenance mode' => 'php artisan up',
                'Create storage link' => 'php artisan storage:link',
                'Run database migrations' => 'php artisan migrate',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_name' => ['required', 'string', 'max:120'],
            'application_short_name' => ['required', 'string', 'max:80'],
            'created_by' => ['required', 'string', 'max:150'],
            'created_by_contact' => ValidationRules::phone(),
            'footer_text' => ['nullable', 'string', 'max:255'],
            'support_email' => ValidationRules::email(false, 150),
            'support_phone' => ValidationRules::phone(),
            'logo_url' => ['nullable', 'string', 'max:300'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            $validated['logo_url'] = $this->uploads->storePublicUpload($request->file('logo'), 'uploads/logos');
        }

        $this->settings->save($validated, $request->user()?->user_id);

        return redirect()->route('system.settings')->with('status', 'System settings updated.');
    }

    private function technicalSections(): array
    {
        return [
            'Application' => [
                ['label' => 'Application Name', 'value' => config('app.name'), 'source' => 'APP_NAME'],
                ['label' => 'Environment', 'value' => config('app.env'), 'source' => 'APP_ENV'],
                ['label' => 'Debug Mode', 'value' => config('app.debug') ? 'Enabled' : 'Disabled', 'source' => 'APP_DEBUG'],
                ['label' => 'Application URL', 'value' => config('app.url'), 'source' => 'APP_URL'],
                ['label' => 'Timezone', 'value' => config('app.timezone'), 'source' => 'APP_TIMEZONE / config/app.php'],
                ['label' => 'Locale', 'value' => config('app.locale'), 'source' => 'APP_LOCALE'],
            ],
            'Database' => [
                ['label' => 'Connection', 'value' => config('database.default'), 'source' => 'DB_CONNECTION'],
                ['label' => 'Host', 'value' => config('database.connections.mysql.host'), 'source' => 'DB_HOST'],
                ['label' => 'Port', 'value' => config('database.connections.mysql.port'), 'source' => 'DB_PORT'],
                ['label' => 'Database', 'value' => config('database.connections.mysql.database'), 'source' => 'DB_DATABASE'],
                ['label' => 'Username', 'value' => config('database.connections.mysql.username'), 'source' => 'DB_USERNAME'],
                ['label' => 'Password', 'value' => filled(config('database.connections.mysql.password')) ? 'Set' : 'Empty', 'source' => 'DB_PASSWORD'],
            ],
            'Runtime Stores' => [
                ['label' => 'Session Driver', 'value' => config('session.driver'), 'source' => 'SESSION_DRIVER'],
                ['label' => 'Session Lifetime', 'value' => config('session.lifetime').' minutes', 'source' => 'SESSION_LIFETIME'],
                ['label' => 'Cache Store', 'value' => config('cache.default'), 'source' => 'CACHE_STORE'],
                ['label' => 'Queue Connection', 'value' => config('queue.default'), 'source' => 'QUEUE_CONNECTION'],
                ['label' => 'Filesystem Disk', 'value' => config('filesystems.default'), 'source' => 'FILESYSTEM_DISK'],
            ],
            'Mail' => [
                ['label' => 'Mailer', 'value' => config('mail.default'), 'source' => 'MAIL_MAILER'],
                ['label' => 'Host', 'value' => config('mail.mailers.smtp.host', 'Not configured'), 'source' => 'MAIL_HOST'],
                ['label' => 'Port', 'value' => config('mail.mailers.smtp.port', 'Not configured'), 'source' => 'MAIL_PORT'],
                ['label' => 'From Address', 'value' => config('mail.from.address'), 'source' => 'MAIL_FROM_ADDRESS'],
                ['label' => 'From Name', 'value' => config('mail.from.name'), 'source' => 'MAIL_FROM_NAME'],
            ],
            'Security' => [
                ['label' => 'Application Key', 'value' => filled(config('app.key')) ? 'Set' : 'Missing', 'source' => 'APP_KEY'],
                ['label' => 'Session Encryption', 'value' => config('session.encrypt') ? 'Enabled' : 'Disabled', 'source' => 'SESSION_ENCRYPT'],
                ['label' => 'HTTPS Cookies', 'value' => config('session.secure') ? 'Enabled' : 'Disabled / automatic', 'source' => 'SESSION_SECURE_COOKIE'],
                ['label' => 'Same Site Cookies', 'value' => config('session.same_site') ?: 'Not set', 'source' => 'SESSION_SAME_SITE'],
                ['label' => 'Bcrypt Rounds', 'value' => config('hashing.bcrypt.rounds'), 'source' => 'BCRYPT_ROUNDS'],
            ],
            'Project Paths' => [
                ['label' => 'Base Path', 'value' => base_path(), 'source' => 'Laravel base_path()'],
                ['label' => 'Storage Path', 'value' => storage_path(), 'source' => 'Laravel storage_path()'],
                ['label' => 'Public Path', 'value' => public_path(), 'source' => 'Laravel public_path()'],
                ['label' => 'Uploads - Photos', 'value' => public_path('uploads/photos'), 'source' => 'Photo uploads'],
                ['label' => 'Uploads - Notices', 'value' => public_path('uploads/notices'), 'source' => 'Notice attachments'],
            ],
        ];
    }
}

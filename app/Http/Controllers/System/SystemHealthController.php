<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SystemHealthController extends Controller
{
    public function __invoke(): View
    {
        $logPath = storage_path('logs/laravel.log');
        $checks = $this->checks($logPath);
        $flatChecks = collect($checks)->flatten(1);

        return view('system.health', [
            'checks' => $checks,
            'summary' => [
                'total' => $flatChecks->count(),
                'passing' => $flatChecks->where('ok', true)->count(),
                'failing' => $flatChecks->where('ok', false)->count(),
            ],
            'errors' => $this->recentErrors($logPath),
            'logPath' => $logPath,
            'logSize' => is_file($logPath) ? $this->humanSize(filesize($logPath)) : '0 B',
        ]);
    }

    private function checks(string $logPath): array
    {
        $debugEnabled = (bool) config('app.debug');
        $isProduction = app()->environment('production');
        $databaseOk = $this->databaseOk();

        return [
            'Application' => [
                [
                    'label' => 'Application',
                    'status' => 'Online',
                    'ok' => true,
                    'detail' => config('app.name').' is booting in '.config('app.env').' mode.',
                ],
                [
                    'label' => 'Application Key',
                    'status' => filled(config('app.key')) ? 'Set' : 'Missing',
                    'ok' => filled(config('app.key')),
                    'detail' => filled(config('app.key')) ? 'APP_KEY is configured.' : 'Run php artisan key:generate.',
                ],
                [
                    'label' => 'Debug Mode',
                    'status' => $debugEnabled ? 'Enabled' : 'Disabled',
                    'ok' => ! $debugEnabled || ! $isProduction,
                    'detail' => $debugEnabled && $isProduction
                        ? 'Disable APP_DEBUG on production.'
                        : ($debugEnabled ? 'Enabled for local development.' : 'Production error pages are protected.'),
                ],
            ],
            'Database' => [
                [
                    'label' => 'Connection',
                    'status' => $databaseOk ? 'Connected' : 'Failed',
                    'ok' => $databaseOk,
                    'detail' => $databaseOk ? 'Default database connection is responding.' : 'Database connection failed.',
                ],
                [
                    'label' => 'Roles Schema',
                    'status' => $this->tableHasColumns('user_roles', ['role_id', 'university_id', 'created_by', 'role_name']) ? 'Ready' : 'Incomplete',
                    'ok' => $this->tableHasColumns('user_roles', ['role_id', 'university_id', 'created_by', 'role_name']),
                    'detail' => 'Checks role ownership and university scoping columns.',
                ],
                [
                    'label' => 'University Logos',
                    'status' => $this->tableHasColumns('universities', ['logo_url']) ? 'Ready' : 'Missing',
                    'ok' => $this->tableHasColumns('universities', ['logo_url']),
                    'detail' => 'Checks logo_url column for university branding.',
                ],
                [
                    'label' => 'University UPI Settings',
                    'status' => $this->tableHasColumns('universities', ['upi_id', 'upi_name', 'upi_note_prefix']) ? 'Ready' : 'Missing',
                    'ok' => $this->tableHasColumns('universities', ['upi_id', 'upi_name', 'upi_note_prefix']),
                    'detail' => 'Checks per-university QR payment columns.',
                ],
                [
                    'label' => 'System Settings',
                    'status' => $this->tableHasColumns('system_settings', ['setting_key', 'setting_value']) ? 'Ready' : 'Missing',
                    'ok' => $this->tableHasColumns('system_settings', ['setting_key', 'setting_value']),
                    'detail' => 'Checks saved application branding and support settings.',
                ],
                [
                    'label' => 'Default Roles',
                    'status' => $this->defaultRolesReady() ? 'Ready' : 'Missing',
                    'ok' => $this->defaultRolesReady(),
                    'detail' => 'Default system roles with permissions.',
                ],
            ],
            'Storage' => [
                [
                    'label' => 'Storage',
                    'status' => File::isWritable(storage_path()) ? 'Writable' : 'Not writable',
                    'ok' => File::isWritable(storage_path()),
                    'detail' => storage_path(),
                ],
                [
                    'label' => 'Laravel Log',
                    'status' => is_file($logPath) ? 'Available' : 'Missing',
                    'ok' => is_file($logPath),
                    'detail' => $logPath,
                ],
                [
                    'label' => 'Public Storage',
                    'status' => $this->publicStorageOk() ? 'Linked' : 'Missing',
                    'ok' => $this->publicStorageOk(),
                    'detail' => public_path('storage'),
                ],
                ...$this->directoryChecks([
                    public_path('uploads/photos'),
                    public_path('uploads/logos'),
                    public_path('uploads/notices'),
                    storage_path('app/uploads/documents'),
                    storage_path('app/generated/halltickets'),
                    storage_path('app/generated/receipts'),
                    storage_path('app/generated/results'),
                ]),
            ],
            'Features' => [
                [
                    'label' => 'UPI QR Payments',
                    'status' => $this->universitiesWithUpi() > 0 ? 'Configured' : 'Not configured',
                    'ok' => $this->universitiesWithUpi() > 0,
                    'detail' => $this->universitiesWithUpi() > 0
                        ? $this->universitiesWithUpi().' universit'.($this->universitiesWithUpi() === 1 ? 'y has' : 'ies have').' UPI configured.'
                        : 'Add UPI ID in each university record to enable QR payments.',
                ],
                [
                    'label' => 'Chatbot Knowledge',
                    'status' => $this->tableHasColumns('chatbot_knowledge', ['question', 'answer', 'normalized_question']) ? 'Ready' : 'Missing',
                    'ok' => $this->tableHasColumns('chatbot_knowledge', ['question', 'answer', 'normalized_question']),
                    'detail' => 'Checks chatbot add/update knowledge storage.',
                ],
            ],
        ];
    }

    private function publicStorageOk(): bool
    {
        $publicStorage = public_path('storage');
        $target = storage_path('app/public');
        $resolved = realpath($publicStorage);

        return (is_link($publicStorage) || is_dir($publicStorage) || file_exists($publicStorage))
            && $resolved !== false
            && realpath($target) === $resolved;
    }

    private function databaseOk(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function tableHasColumns(string $table, array $columns): bool
    {
        try {
            if (! Schema::hasTable($table)) {
                return false;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    return false;
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function defaultRolesReady(): bool
    {
        try {
            $systemRoles = ['Super Admin', 'Admin', 'Student'];

            return Schema::hasTable('user_roles')
                && Schema::hasTable('permissions')
                && DB::table('user_roles')->where('is_system_role', true)->whereIn('role_name', $systemRoles)->count() === count($systemRoles)
                && DB::table('user_roles')->where('is_system_role', true)->whereNotIn('role_name', $systemRoles)->doesntExist()
                && DB::table('permissions')->count() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function universitiesWithUpi(): int
    {
        try {
            if (! $this->tableHasColumns('universities', ['upi_id'])) {
                return 0;
            }

            return DB::table('universities')->whereNotNull('upi_id')->where('upi_id', '!=', '')->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function directoryChecks(array $paths): array
    {
        return array_map(fn (string $path) => [
            'label' => basename($path),
            'status' => File::isDirectory($path) && File::isWritable($path) ? 'Writable' : 'Missing',
            'ok' => File::isDirectory($path) && File::isWritable($path),
            'detail' => $path,
        ], $paths);
    }

    private function recentErrors(string $logPath): array
    {
        if (! is_file($logPath) || ! is_readable($logPath)) {
            return [];
        }

        $content = $this->tail($logPath, 1024 * 1024);
        $entries = preg_split('/(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/m', $content, -1, PREG_SPLIT_NO_EMPTY);
        $levels = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'];
        $errors = [];
        $databaseCurrentlyOk = $this->databaseOk();

        foreach (array_reverse($entries) as $entry) {
            if (! preg_match('/^\[(?<date>[^\]]+)\]\s+(?<env>[^.]+)\.(?<level>[A-Z]+):\s+(?<message>.*)$/s', trim($entry), $matches)) {
                continue;
            }

            if (! in_array($matches['level'], $levels, true)) {
                continue;
            }

            $message = strtok($matches['message'], "\n") ?: $matches['message'];

            if ($this->shouldHideLogEntry($message, $entry, $databaseCurrentlyOk)) {
                continue;
            }

            $errors[] = [
                'date' => $matches['date'],
                'environment' => $matches['env'],
                'level' => $matches['level'],
                'message' => str($message)->limit(220)->toString(),
                'trace' => str($entry)->after("\n")->limit(1200)->toString(),
            ];

            if (count($errors) >= 30) {
                break;
            }
        }

        return $errors;
    }

    private function shouldHideLogEntry(string $message, string $entry, bool $databaseCurrentlyOk): bool
    {
        return str_contains($message, 'Psy\\Exception\\ParseErrorException')
            || str_contains($message, 'PHP Parse error: Syntax error, unexpected T_NS_SEPARATOR')
            || str_contains($message, 'Illuminate\\Auth\\SessionGuard::setUser(): Argument #1 ($user) must be of type')
            || str_contains($entry, 'vendor/laravel/tinker/src/Console/TinkerCommand.php')
            || str_contains($entry, 'SessionGuard::setUser')
            || ($databaseCurrentlyOk && str_contains($message, 'SQLSTATE[HY000] [2002] No connection could be made'));
    }

    private function tail(string $path, int $bytes): string
    {
        $size = filesize($path);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        if ($size > $bytes) {
            fseek($handle, -$bytes, SEEK_END);
        }

        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max($bytes, 0);
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }
}

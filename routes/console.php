<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:readiness-check', function () {
    $checks = [];
    $add = function (string $label, bool $ok, string $detail = '') use (&$checks): void {
        $checks[] = compact('label', 'ok', 'detail');
    };

    $add('Application key', filled(config('app.key')), 'Set APP_KEY with php artisan key:generate.');
    $add('Debug disabled in production', ! app()->environment('production') || ! config('app.debug'), 'APP_DEBUG must be false in production.');
    $add('Storage writable', File::isWritable(storage_path()), storage_path());

    $publicStorage = public_path('storage');
    $storageTarget = storage_path('app/public');
    $add(
        'Public storage link',
        file_exists($publicStorage) && realpath($publicStorage) !== false && realpath($publicStorage) === realpath($storageTarget),
        'Run php artisan storage:link.'
    );

    foreach ([
        public_path('uploads/photos'),
        public_path('uploads/notices'),
        storage_path('app/uploads/documents'),
        storage_path('app/generated/halltickets'),
        storage_path('app/generated/receipts'),
        storage_path('app/generated/results'),
    ] as $path) {
        $add("Directory: {$path}", File::isDirectory($path) && File::isWritable($path), 'Create it and grant write permission.');
    }

    try {
        DB::connection()->getPdo();
        $add('Database connection', true, config('database.default'));
    } catch (\Throwable $exception) {
        $add('Database connection', false, $exception->getMessage());
    }

    if (config('cache.default') === 'database') {
        $cacheTable = config('cache.stores.database.table', 'cache');
        $add("Database cache table [{$cacheTable}]", Schema::hasTable($cacheTable), 'Run php artisan migrate --force.');
    }

    if (config('session.driver') === 'database') {
        $sessionTable = config('session.table', 'sessions');
        $add("Database session table [{$sessionTable}]", Schema::hasTable($sessionTable), 'Run php artisan migrate --force.');
    }

    $systemRoles = ['Super Admin', 'Admin', 'Student'];
    $rolesReady = Schema::hasTable('user_roles')
        && Schema::hasTable('permissions')
        && DB::table('user_roles')->where('is_system_role', true)->whereIn('role_name', $systemRoles)->count() === count($systemRoles)
        && DB::table('user_roles')->where('is_system_role', true)->whereNotIn('role_name', $systemRoles)->doesntExist()
        && DB::table('permissions')->count() > 0;
    $add('Default roles and permissions', $rolesReady, 'Run php artisan db:seed --force.');

    $failed = collect($checks)->where('ok', false);

    foreach ($checks as $check) {
        $status = $check['ok'] ? '<info>PASS</info>' : '<error>FAIL</error>';
        $this->line(sprintf('%s  %s', str_pad($check['label'], 72), $status));

        if (! $check['ok'] && $check['detail'] !== '') {
            $this->line('  '.$check['detail']);
        }
    }

    if ($failed->isNotEmpty()) {
        $this->newLine();
        $this->error($failed->count().' readiness check(s) failed.');

        return 1;
    }

    $this->newLine();
    $this->info('All readiness checks passed.');

    return 0;
})->purpose('Check whether the application has production-ready environment basics.');

Artisan::command('app:database-setup
    {--fresh : Drop all tables and rebuild the database with migrate:fresh}
    {--no-migrate : Prepare the database only; skip migrations}
    {--no-seed : Skip default seeders}
    {--sync-permissions : Sync existing users with role-default permissions after seeding}
    {--force : Run without confirmation prompts}', function () {
    $connection = config('database.default');
    $config = config("database.connections.{$connection}", []);
    $driver = $config['driver'] ?? $connection;
    $database = $config['database'] ?? null;
    $fresh = (bool) $this->option('fresh');
    $force = (bool) $this->option('force');

    if ($fresh && ! $force && ! $this->confirm('This will drop existing tables before rebuilding. Continue?')) {
        $this->warn('Database setup cancelled.');

        return 1;
    }

    $this->info("Preparing {$driver} database for connection [{$connection}]...");

    try {
        match ($driver) {
            'sqlite' => ensureSqliteDatabaseFile($database),
            'mysql', 'mariadb' => ensureMysqlDatabaseExists($config),
            default => $this->warn("Automatic database creation is not implemented for [{$driver}]. I will still try to connect and migrate."),
        };

        DB::purge($connection);
        DB::connection($connection)->getPdo();
        $this->info('Database connection is ready.');
    } catch (Throwable $exception) {
        $this->error('Database setup failed: '.$exception->getMessage());

        return 1;
    }

    if (! $this->option('no-migrate')) {
        $migrationCommand = $fresh ? 'migrate:fresh' : 'migrate';
        $this->call($migrationCommand, ['--force' => true]);
    }

    if (! $this->option('no-seed')) {
        $this->call('db:seed', ['--force' => true]);
    }

    if ($this->option('sync-permissions')) {
        $this->call('permissions:sync-users');
    }

    $this->call('app:readiness-check');
    $this->info('Database automation completed.');

    return 0;
})->purpose('Create/check the configured database, run migrations, and seed required data.');

if (! function_exists('ensureSqliteDatabaseFile')) {
    function ensureSqliteDatabaseFile(?string $database): void
    {
        if (! $database || $database === ':memory:') {
            return;
        }

        File::ensureDirectoryExists(dirname($database));

        if (! File::exists($database)) {
            File::put($database, '');
        }
    }
}

if (! function_exists('ensureMysqlDatabaseExists')) {
    function ensureMysqlDatabaseExists(array $config): void
    {
        $database = $config['database'] ?? null;

        if (! $database) {
            throw new RuntimeException('DB_DATABASE is empty.');
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $pdo = new PDO(
            "mysql:host={$host};port={$port};charset={$charset}",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        $quotedDatabase = '`'.str_replace('`', '``', $database).'`';
        $quotedCharset = preg_replace('/[^A-Za-z0-9_]/', '', $charset) ?: 'utf8mb4';
        $quotedCollation = preg_replace('/[^A-Za-z0-9_]/', '', $collation) ?: 'utf8mb4_unicode_ci';

        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$quotedDatabase} CHARACTER SET {$quotedCharset} COLLATE {$quotedCollation}");
    }
}

Artisan::command('permissions:sync-users {--replace : Replace each user permission set with role defaults instead of only adding missing permissions}', function () {
    if (! Schema::hasTable('users') || ! Schema::hasTable('role_permissions') || ! Schema::hasTable('user_permissions')) {
        $this->error('Permission tables are not ready. Run migrations first.');

        return 1;
    }

    $replace = (bool) $this->option('replace');
    $updatedUsers = 0;
    $attachedPermissions = 0;

    User::query()
        ->whereNotNull('role_id')
        ->with('role.permissions')
        ->orderBy('user_id')
        ->chunkById(100, function ($users) use ($replace, &$updatedUsers, &$attachedPermissions): void {
            foreach ($users as $user) {
                $permissionIds = $user->role?->permissions
                    ->pluck('permission_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all() ?? [];

                if ($replace) {
                    $user->permissions()->sync($permissionIds);
                    $attachedPermissions += count($permissionIds);
                    $updatedUsers++;

                    continue;
                }

                $currentIds = $user->permissions()
                    ->pluck('permissions.permission_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $missingIds = array_values(array_diff($permissionIds, $currentIds));

                if ($missingIds === []) {
                    continue;
                }

                $user->permissions()->syncWithoutDetaching($missingIds);
                $attachedPermissions += count($missingIds);
                $updatedUsers++;
            }
        }, 'user_id');

    $mode = $replace ? 'replaced with role defaults' : 'updated with missing role-default permissions';
    $this->info("{$updatedUsers} user(s) {$mode}; {$attachedPermissions} permission link(s) written.");

    return 0;
})->purpose('Update existing user_permissions from role permission defaults.');

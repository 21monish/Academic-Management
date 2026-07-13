<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoDataCleanupSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tableNames() as $table) {
                if (in_array($table, ['migrations'], true)) {
                    continue;
                }

                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function tableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%'"))
                ->pluck('name')
                ->sortDesc()
                ->values()
                ->all();
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select('SHOW TABLES'))
                ->map(fn ($row) => array_values((array) $row)[0] ?? null)
                ->filter()
                ->sortDesc()
                ->values()
                ->all();
        }

        return collect(DB::select("select table_name from information_schema.tables where table_schema = 'public'"))
            ->pluck('table_name')
            ->sortDesc()
            ->values()
            ->all();
    }
}

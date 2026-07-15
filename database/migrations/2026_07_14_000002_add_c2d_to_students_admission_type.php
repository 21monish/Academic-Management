<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasColumn('students', 'admission_type')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY admission_type ENUM('Direct','ACPC','Management','D2D','C2D') NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasColumn('students', 'admission_type')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('students')
                ->where('admission_type', 'C2D')
                ->update(['admission_type' => null]);

            DB::statement("ALTER TABLE students MODIFY admission_type ENUM('Direct','ACPC','Management','D2D') NULL");
        }
    }
};

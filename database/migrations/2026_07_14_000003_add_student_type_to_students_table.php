<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students')) {
            return;
        }

        if (! Schema::hasColumn('students', 'student_type')) {
            Schema::table('students', function (Blueprint $table) {
                $table->enum('student_type', ['Regular', 'D2D', 'C2D'])
                    ->default('Regular')
                    ->after('admission_date');
            });
        }

        DB::table('students')
            ->whereIn('admission_type', ['D2D', 'C2D'])
            ->update(['student_type' => DB::raw('admission_type')]);

        if (Schema::getConnection()->getDriverName() === 'mysql' && Schema::hasColumn('students', 'admission_type')) {
            DB::table('students')
                ->whereIn('admission_type', ['D2D', 'C2D'])
                ->update(['admission_type' => null]);

            DB::statement("ALTER TABLE students MODIFY admission_type ENUM('Direct','ACPC','Management') NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('students')) {
            return;
        }

        if (Schema::hasColumn('students', 'student_type')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('student_type');
            });
        }
    }
};

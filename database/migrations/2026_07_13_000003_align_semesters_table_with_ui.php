<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            if (! Schema::hasColumn('semesters', 'name')) {
                $table->string('name', 100)->nullable()->after('programme_id');
            }

            if (! Schema::hasColumn('semesters', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_current');
            }
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            if (Schema::hasColumn('semesters', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('semesters', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('role_id')
                ->constrained('universities', 'university_id')
                ->nullOnDelete();
            $table->foreignId('dept_id')->nullable()->after('college_id')
                ->constrained('departments', 'dept_id')
                ->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->after('dept_id')
                ->constrained('programmes', 'programme_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['programme_id']);
            $table->dropForeign(['dept_id']);
            $table->dropForeign(['university_id']);
            $table->dropColumn(['programme_id', 'dept_id', 'university_id']);
        });
    }
};

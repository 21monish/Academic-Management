<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->foreignId('university_id')
                ->nullable()
                ->after('role_id')
                ->constrained('universities', 'university_id')
                ->nullOnDelete();
        });

        try {
            Schema::table('user_roles', function (Blueprint $table) {
                $table->dropUnique('user_roles_role_name_unique');
            });
        } catch (Throwable) {
            // Some test databases may not carry the generated index name.
        }

        Schema::table('user_roles', function (Blueprint $table) {
            $table->index(['university_id', 'role_name'], 'user_roles_university_role_name_index');
        });
    }

    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex('user_roles_university_role_name_index');
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->unique('role_name');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('university_id')
                ->constrained('users', 'user_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_roles', 'staff_type')) {
            return;
        }

        Schema::table('user_roles', function (Blueprint $table) {
            $table->enum('staff_type', ['Teaching', 'Non-Teaching', 'Both'])
                ->nullable()
                ->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('user_roles', 'staff_type')) {
            return;
        }

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropColumn('staff_type');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 80)->unique(); // Super Admin, HOD, Student, ...
            $table->text('description')->nullable();
            $table->enum('staff_type', ['Teaching', 'Non-Teaching', 'Both'])->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};

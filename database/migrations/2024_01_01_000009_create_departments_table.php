<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id('dept_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->string('code', 10)->nullable(); // e.g. CE, ME
            $table->string('name', 150);
            $table->text('description')->nullable();

            // FK to staff added later in Module 3 migrations, once the staff table exists
            $table->unsignedBigInteger('hod_staff_id')->nullable();

            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

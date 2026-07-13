<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('dept_id')->nullable()
                ->constrained('departments', 'dept_id')
                ->nullOnDelete();

            $table->string('employee_code', 30)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->text('address')->nullable();
            $table->string('photo_url', 300)->nullable();

            $table->enum('staff_type', ['Teaching', 'Non-Teaching', 'Both']);
            $table->enum('employment_type', ['Permanent', 'Contractual', 'Visiting']);
            $table->date('join_date')->nullable();
            $table->date('contract_end_date')->nullable(); // NULL for permanent
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();

            $table->string('enrollment_no', 30)->unique(); // GTU enrollment number
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('photo_url', 300)->nullable();

            $table->foreignId('category_id')->nullable()
                ->constrained('categories', 'category_id')
                ->nullOnDelete();

            $table->date('admission_date')->nullable();
            $table->enum('student_type', ['Regular', 'D2D', 'C2D'])->default('Regular');
            $table->enum('admission_type', ['Direct', 'ACPC', 'Management'])->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

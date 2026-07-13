<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->date('enrolled_on')->nullable();
            $table->enum('status', ['Active', 'Detained', 'PassedOut', 'Withdrawn'])->default('Active');

            $table->unique(['student_id', 'semester_id'], 'student_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

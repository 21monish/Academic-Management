<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id('summary_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();

            $table->integer('total_lectures')->default(0);
            $table->integer('attended_lectures')->default(0);
            $table->float('attendance_percentage')->nullable(); // computed field
            $table->boolean('is_detained')->default(false);      // below threshold flag
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['student_id', 'subject_id', 'semester_id'], 'attendance_summary_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};

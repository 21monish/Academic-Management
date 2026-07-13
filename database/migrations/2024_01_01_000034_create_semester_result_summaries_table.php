<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_result_summaries', function (Blueprint $table) {
            $table->id('summary_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();

            $table->float('total_marks_obtained')->nullable();
            $table->float('total_max_marks')->nullable();
            $table->float('sgpa')->nullable();
            $table->float('cgpa')->nullable();
            $table->integer('total_credits_earned')->nullable();
            $table->integer('backlogs_count')->default(0);
            $table->enum('overall_status', ['Pass', 'Fail', 'ATKT', 'Detained'])->nullable();
            $table->boolean('is_published')->default(false);

            $table->unique(['student_id', 'exam_id'], 'student_exam_summary_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_result_summaries');
    }
};

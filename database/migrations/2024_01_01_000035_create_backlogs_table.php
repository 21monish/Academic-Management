<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlogs', function (Blueprint $table) {
            $table->id('backlog_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('original_exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();

            $table->enum('backlog_type', ['ATKT', 'Regular']);
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['Pending', 'Cleared', 'Lapsed'])->default('Pending');
            $table->date('registered_on')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlogs');
    }
};

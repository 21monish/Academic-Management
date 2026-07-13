<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id('result_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('exam_subject_id')
                ->constrained('exam_subjects', 'exam_subject_id')
                ->cascadeOnDelete();
            $table->foreignId('enrollment_id')
                ->constrained('student_enrollments', 'enrollment_id')
                ->cascadeOnDelete();

            $table->float('theory_marks')->nullable();
            $table->float('practical_marks')->nullable();
            $table->float('internal_marks')->nullable();
            $table->float('total_marks')->nullable();
            $table->float('percentage')->nullable();
            $table->integer('grade_point')->nullable();
            $table->string('grade', 5)->nullable(); // AA/AB/BB/BC/CC/CD/DD/FF - looked up via grade_master per programme
            $table->enum('result_status', ['Pass', 'Fail', 'ATKT', 'Absent'])->nullable();
            $table->boolean('is_atkt')->default(false); // allowed to keep terms
            $table->boolean('is_published')->default(false);
            $table->timestamp('declared_at')->nullable();

            $table->unique(['student_id', 'exam_subject_id'], 'student_exam_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};

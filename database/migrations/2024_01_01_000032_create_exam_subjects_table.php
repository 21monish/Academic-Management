<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id('exam_subject_id');
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->date('exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->integer('max_theory_marks')->nullable();
            $table->integer('max_practical_marks')->nullable();
            $table->integer('max_internal_marks')->nullable();
            $table->integer('passing_theory_marks')->nullable();
            $table->integer('passing_practical_marks')->nullable();
            $table->integer('passing_internal_marks')->nullable();

            $table->unique(['exam_id', 'subject_id'], 'exam_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theory_exam_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();

            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_published')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theory_exam_schedules');
    }
};

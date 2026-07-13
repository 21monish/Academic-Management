<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practical_exam_schedules', function (Blueprint $table) {
            $table->id('prac_schedule_id');
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('dept_id')
                ->constrained('departments', 'dept_id')
                ->cascadeOnDelete();

            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('lab_no', 30)->nullable();
            $table->integer('batch_size')->nullable(); // max students per batch
            $table->enum('status', ['Scheduled', 'Ongoing', 'Completed'])->default('Scheduled');
            $table->string('external_examiner_name', 150)->nullable();
            $table->string('external_examiner_org', 200)->nullable();
            $table->foreignId('internal_examiner_staff_id')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();
            $table->boolean('is_published')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_exam_schedules');
    }
};

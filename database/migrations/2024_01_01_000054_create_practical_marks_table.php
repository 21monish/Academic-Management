<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practical_marks', function (Blueprint $table) {
            $table->id('prac_marks_id');
            $table->foreignId('batch_id')
                ->constrained('practical_batches', 'batch_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->float('journal_marks')->nullable();
            $table->float('viva_marks')->nullable();
            $table->float('performance_marks')->nullable();
            $table->float('total_marks')->nullable(); // feeds into RESULT per source spec (app-level sync, not a DB FK)
            $table->float('max_marks')->nullable();
            $table->string('grade', 5)->nullable();
            $table->enum('result_status', ['Pass', 'Fail'])->nullable();
            $table->foreignId('marked_by_staff_id')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('marked_at')->useCurrent();

            $table->unique(['batch_id', 'student_id', 'subject_id'], 'practical_marks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_marks');
    }
};

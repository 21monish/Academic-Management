<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlog_exams', function (Blueprint $table) {
            $table->id('backlog_exam_id');
            $table->foreignId('backlog_id')
                ->constrained('backlogs', 'backlog_id')
                ->cascadeOnDelete();
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete(); // re-attempt exam

            $table->float('theory_marks')->nullable();
            $table->float('practical_marks')->nullable();
            $table->float('internal_marks')->nullable();
            $table->float('total_marks')->nullable();
            $table->string('grade', 5)->nullable();
            $table->enum('result_status', ['Pass', 'Fail'])->nullable();
            $table->boolean('is_cleared')->default(false);
            $table->timestamp('declared_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlog_exams');
    }
};

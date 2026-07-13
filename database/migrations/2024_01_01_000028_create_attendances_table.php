<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('lecture_id')
                ->constrained('lectures', 'lecture_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();

            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused']);
            $table->string('remarks', 200)->nullable();
            $table->timestamp('marked_at')->useCurrent();
            $table->foreignId('marked_by')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();

            $table->unique(['lecture_id', 'student_id'], 'lecture_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

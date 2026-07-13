<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practical_batch_students', function (Blueprint $table) {
            $table->id('pbs_id');
            $table->foreignId('batch_id')
                ->constrained('practical_batches', 'batch_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('hall_ticket_id')->nullable()
                ->constrained('hall_tickets', 'hall_ticket_id')
                ->nullOnDelete();

            $table->integer('seat_no')->nullable();
            $table->enum('attendance_status', ['Present', 'Absent'])->nullable();
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['batch_id', 'student_id'], 'batch_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_batch_students');
    }
};

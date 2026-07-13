<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seating_arrangements', function (Blueprint $table) {
            $table->id('seating_id');
            $table->foreignId('schedule_id')
                ->constrained('theory_exam_schedules', 'schedule_id')
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->constrained('exam_rooms', 'room_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('hall_ticket_id')->nullable()
                ->constrained('hall_tickets', 'hall_ticket_id')
                ->nullOnDelete();

            $table->integer('seat_no')->nullable();
            $table->string('seat_label', 20)->nullable(); // e.g. A-12
            $table->enum('status', ['Assigned', 'Present', 'Absent', 'Malpractice'])->default('Assigned');
            $table->boolean('is_present')->nullable();
            $table->foreignId('invigilator_staff_id')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_arrangements');
    }
};

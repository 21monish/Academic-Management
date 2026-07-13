<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id('slot_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->enum('day_of_week', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('lecture_type', ['Theory', 'Lab']);
            $table->string('room_no', 20)->nullable();
            $table->string('academic_year', 10)->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};

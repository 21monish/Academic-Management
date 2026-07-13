<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invigilator_duties', function (Blueprint $table) {
            $table->id('duty_id');
            $table->foreignId('schedule_id')
                ->constrained('theory_exam_schedules', 'schedule_id')
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->constrained('exam_rooms', 'room_id')
                ->cascadeOnDelete();
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->enum('duty_type', ['Chief', 'Invigilator', 'FlyingSquad', 'Observer']);
            $table->time('duty_start_time')->nullable();
            $table->time('duty_end_time')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->text('remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invigilator_duties');
    }
};

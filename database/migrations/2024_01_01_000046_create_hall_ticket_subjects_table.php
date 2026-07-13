<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_ticket_subjects', function (Blueprint $table) {
            $table->id('hts_id');
            $table->foreignId('hall_ticket_id')
                ->constrained('hall_tickets', 'hall_ticket_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->enum('subject_type', ['Theory', 'Practical', 'Both'])->nullable();
            $table->date('theory_exam_date')->nullable();
            $table->time('theory_exam_time')->nullable();
            $table->string('theory_room_no', 20)->nullable();
            $table->integer('theory_seat_no')->nullable();
            $table->date('practical_exam_date')->nullable();
            $table->time('practical_exam_time')->nullable();
            $table->string('practical_lab_no', 20)->nullable();
            $table->boolean('is_backlog')->default(false);
            $table->boolean('is_eligible')->nullable(); // subject-level eligibility
            $table->text('ineligibility_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_ticket_subjects');
    }
};

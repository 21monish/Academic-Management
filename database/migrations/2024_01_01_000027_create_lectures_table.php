<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lectures', function (Blueprint $table) {
            $table->id('lecture_id');
            $table->foreignId('slot_id')
                ->constrained('timetable_slots', 'slot_id')
                ->cascadeOnDelete();

            $table->date('lecture_date');
            $table->enum('lecture_type', ['Theory', 'Lab'])->nullable(); // can override slot type
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->text('topic_covered')->nullable();
            $table->boolean('is_extra')->default(false); // extra/makeup lecture
            $table->boolean('is_cancelled')->default(false);
            $table->text('cancel_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};

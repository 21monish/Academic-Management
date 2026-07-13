<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practical_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->foreignId('prac_schedule_id')
                ->constrained('practical_exam_schedules', 'prac_schedule_id')
                ->cascadeOnDelete();

            $table->string('batch_name', 50)->nullable(); // e.g. Batch A
            $table->integer('batch_no')->nullable();
            $table->date('batch_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('max_students')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_batches');
    }
};

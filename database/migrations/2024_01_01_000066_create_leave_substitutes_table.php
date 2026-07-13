<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_substitutes', function (Blueprint $table) {
            $table->id('substitute_id');
            $table->foreignId('application_id')
                ->constrained('leave_applications', 'application_id')
                ->cascadeOnDelete();
            $table->foreignId('substitute_staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->date('class_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('lecture_type', ['Theory', 'Lab'])->nullable();
            $table->enum('status', ['Pending', 'Confirmed', 'Completed'])->default('Pending');
            $table->text('remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_substitutes');
    }
};

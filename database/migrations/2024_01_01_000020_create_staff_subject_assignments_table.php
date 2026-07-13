<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_subject_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();

            $table->enum('lecture_type', ['Theory', 'Lab', 'Both']);
            $table->string('academic_year', 10)->nullable(); // e.g. 2024-25
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_subject_assignments');
    }
};

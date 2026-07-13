<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum', function (Blueprint $table) {
            $table->id('curriculum_id');
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->boolean('is_mandatory')->default(true);
            $table->integer('max_marks')->nullable();
            $table->integer('min_passing_marks')->nullable();

            $table->unique(['programme_id', 'semester_id', 'subject_id'], 'curriculum_prog_sem_subj_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum');
    }
};

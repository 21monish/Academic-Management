<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_elective_choices', function (Blueprint $table) {
            $table->id('choice_id');
            $table->foreignId('enrollment_id')
                ->constrained('student_enrollments', 'enrollment_id')
                ->cascadeOnDelete();
            $table->foreignId('group_id')
                ->constrained('elective_groups', 'group_id')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects', 'subject_id')
                ->cascadeOnDelete();

            $table->unique(['enrollment_id', 'group_id'], 'enrollment_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_elective_choices');
    }
};

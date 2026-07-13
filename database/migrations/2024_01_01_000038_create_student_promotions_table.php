<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('from_semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('to_semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->enum('promotion_status', ['Promoted', 'Detained', 'Withdrawn']);
            $table->integer('backlogs_at_promotion')->default(0);
            $table->float('sgpa_at_promotion')->nullable();
            $table->float('attendance_pct')->nullable();
            $table->boolean('is_manual_override')->default(false);
            $table->foreignId('approved_by')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('promoted_on')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id('fee_structure_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();
            $table->foreignId('fee_category_id')
                ->constrained('fee_categories', 'fee_category_id')
                ->cascadeOnDelete();
            $table->foreignId('student_category_id')->nullable()
                ->constrained('categories', 'category_id') // SC/ST/OBC/GEN/EWS
                ->nullOnDelete();

            $table->float('amount');
            $table->float('late_fine_per_day')->default(0);
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};

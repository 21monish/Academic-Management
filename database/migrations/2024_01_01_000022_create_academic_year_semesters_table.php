<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_year_semesters', function (Blueprint $table) {
            $table->id('ays_id');
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();

            $table->date('odd_start_date')->nullable();
            $table->date('odd_end_date')->nullable();
            $table->date('even_start_date')->nullable();
            $table->date('even_end_date')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_year_semesters');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id('scholarship_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->string('scheme_name', 200); // e.g. Post Matric Scholarship
            $table->string('provider', 200)->nullable(); // Govt / Trust / College
            $table->float('amount')->nullable();
            $table->enum('status', ['Applied', 'Approved', 'Disbursed', 'Rejected'])->default('Applied');
            $table->date('applied_on')->nullable();
            $table->date('approved_on')->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->text('remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};

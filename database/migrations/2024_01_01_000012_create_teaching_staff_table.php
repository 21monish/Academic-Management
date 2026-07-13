<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_staff', function (Blueprint $table) {
            $table->id('teaching_id');
            $table->foreignId('staff_id')->unique() // enforces the 1-to-1 relationship
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->string('qualification', 200)->nullable(); // e.g. Ph.D. in CSE
            $table->string('specialization', 200)->nullable();
            $table->enum('designation', ['Professor', 'Associate Professor', 'Assistant Professor', 'Lecturer', 'Visiting Faculty', 'Lab Instructor'])->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('research_area')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_staff');
    }
};

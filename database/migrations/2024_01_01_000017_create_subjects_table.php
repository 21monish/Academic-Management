<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->foreignId('dept_id')
                ->constrained('departments', 'dept_id')
                ->cascadeOnDelete();

            $table->string('code', 20)->unique(); // GTU subject code
            $table->string('name', 200);
            $table->enum('type', ['Theory', 'Lab', 'Tutorial']);
            $table->enum('subject_category', ['Core', 'Elective', 'Open Elective', 'Audit']);
            $table->integer('credits')->nullable();
            $table->integer('theory_hours')->nullable();
            $table->integer('lab_hours')->nullable();
            $table->integer('tutorial_hours')->nullable();
            $table->boolean('is_elective')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

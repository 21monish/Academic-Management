<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id('semester_id');
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();

            $table->integer('semester_no'); // 1 to 8
            $table->string('academic_year', 10)->nullable(); // e.g. 2024-25
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

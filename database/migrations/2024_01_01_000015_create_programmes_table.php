<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id('programme_id');
            $table->foreignId('dept_id')
                ->constrained('departments', 'dept_id')
                ->cascadeOnDelete();

            $table->string('code', 20)->unique(); // e.g. BE_CE, ME_CSE
            $table->string('name', 150);           // e.g. Bachelor of Engineering
            $table->enum('level', ['UG', 'PG', 'Diploma', 'PhD']);
            $table->integer('duration_semesters')->nullable(); // e.g. 8
            $table->integer('total_credits')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elective_groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->foreignId('curriculum_id')
                ->constrained('curriculum', 'curriculum_id')
                ->cascadeOnDelete();

            $table->string('group_name', 100); // e.g. Elective-I
            $table->integer('select_count');    // how many to choose from group
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elective_groups');
    }
};

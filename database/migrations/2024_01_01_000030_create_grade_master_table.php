<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_master', function (Blueprint $table) {
            $table->id('grade_id');
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();

            $table->string('grade_letter', 5);
            $table->float('min_percentage');
            $table->float('max_percentage');
            $table->float('grade_point');
            $table->string('description', 100)->nullable(); // e.g. Outstanding
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_master');
    }
};

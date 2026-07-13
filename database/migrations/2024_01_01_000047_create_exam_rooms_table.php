<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_rooms', function (Blueprint $table) {
            $table->id('room_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();

            $table->string('room_no', 20);
            $table->string('building', 100)->nullable();
            $table->integer('floor_no')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->enum('room_type', ['Hall', 'Classroom', 'Lab'])->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_rooms');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id('leave_type_id');
            $table->string('code', 10)->unique(); // e.g. CL, EL, ML, COH, DL
            $table->string('name', 100); // e.g. Casual Leave
            $table->enum('applicable_to', ['Teaching', 'NonTeaching', 'Both']);
            $table->integer('max_days_per_year')->nullable();
            $table->integer('max_consecutive_days')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward_allowed')->default(false);
            $table->integer('max_carry_forward_days')->nullable();
            $table->boolean('requires_document')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

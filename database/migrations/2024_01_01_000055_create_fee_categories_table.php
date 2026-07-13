<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id('fee_category_id');
            $table->string('name', 100); // e.g. Tuition, Exam Fee, Library
            $table->text('description')->nullable();
            $table->enum('fee_type', ['Academic', 'Exam', 'Hostel', 'Transport', 'Misc']);
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};

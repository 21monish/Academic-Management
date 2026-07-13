<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_categories', function (Blueprint $table) {
            $table->id('notice_category_id');
            $table->string('name', 100); // e.g. Exam, Holiday, Academic
            $table->string('color_code', 10)->nullable(); // hex color for UI badge
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_categories');
    }
};

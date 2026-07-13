<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id('holiday_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->string('holiday_name', 150);
            $table->date('holiday_date');
            $table->enum('holiday_type', ['National', 'State', 'Regional', 'College'])->nullable();
            $table->boolean('is_optional')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendars');
    }
};

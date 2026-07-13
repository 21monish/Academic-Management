<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id('rule_id');
            $table->foreignId('programme_id')
                ->constrained('programmes', 'programme_id')
                ->cascadeOnDelete();

            $table->integer('from_semester_no');
            $table->float('min_attendance_pct')->nullable(); // e.g. 75.0
            $table->integer('max_backlogs_allowed')->nullable();
            $table->float('min_sgpa_required')->nullable(); // 0 if not applicable
            $table->boolean('allow_atkt_promotion')->default(true);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_rules');
    }
};

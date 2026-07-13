<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id('balance_id');
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();
            $table->foreignId('leave_type_id')
                ->constrained('leave_types', 'leave_type_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->float('total_allocated')->default(0);
            $table->float('carry_forwarded')->default(0);
            $table->float('total_available')->default(0); // allocated + carry_forwarded
            $table->float('used')->default(0);
            $table->float('pending_approval')->default(0);
            $table->float('remaining')->default(0); // computed
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['staff_id', 'leave_type_id', 'academic_year_id'], 'leave_balance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_cancellations', function (Blueprint $table) {
            $table->id('cancel_id');
            $table->foreignId('application_id')
                ->constrained('leave_applications', 'application_id')
                ->cascadeOnDelete();
            $table->foreignId('cancelled_by')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->text('reason')->nullable();
            $table->enum('cancel_status', ['Requested', 'Approved', 'Rejected'])->default('Requested');
            $table->foreignId('approved_by')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete();
            $table->timestamp('cancelled_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_cancellations');
    }
};

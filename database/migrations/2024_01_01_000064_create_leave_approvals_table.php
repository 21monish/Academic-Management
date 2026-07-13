<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id('approval_id');
            $table->foreignId('application_id')
                ->constrained('leave_applications', 'application_id')
                ->cascadeOnDelete();
            $table->foreignId('approver_staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->integer('approval_level'); // 1 = HOD, 2 = Principal
            $table->enum('decision', ['Approved', 'Rejected', 'Forwarded']);
            $table->text('remarks')->nullable();
            $table->timestamp('decided_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};

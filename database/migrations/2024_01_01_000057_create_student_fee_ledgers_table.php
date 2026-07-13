<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_ledgers', function (Blueprint $table) {
            $table->id('ledger_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('fee_structure_id')
                ->constrained('fee_structures', 'fee_structure_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();
            $table->foreignId('semester_id')
                ->constrained('semesters', 'semester_id')
                ->cascadeOnDelete();

            $table->float('total_amount'); // gross amount
            $table->float('concession_amount')->default(0);
            $table->float('scholarship_amount')->default(0);
            $table->float('net_payable')->nullable(); // after deductions
            $table->float('amount_paid')->default(0);
            $table->float('balance_due')->nullable();
            $table->enum('payment_status', ['Unpaid', 'Partial', 'Paid', 'Overdue'])->default('Unpaid');
            $table->boolean('is_hall_ticket_cleared')->default(false); // used by hall ticket module
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_ledgers');
    }
};

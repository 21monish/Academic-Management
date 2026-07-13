<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('ledger_id')
                ->constrained('student_fee_ledgers', 'ledger_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();

            $table->float('amount_paid');
            $table->date('payment_date')->nullable();
            $table->enum('payment_mode', ['Cash', 'Online', 'Cheque', 'DD', 'NEFT']);
            $table->string('transaction_ref', 100)->nullable(); // UTR / txn id
            $table->string('receipt_no', 50)->unique();
            $table->string('bank_name', 100)->nullable(); // for cheque/DD
            $table->string('cheque_no', 30)->nullable();
            $table->date('cheque_date')->nullable();
            $table->enum('payment_status', ['Pending', 'Cleared', 'Bounced', 'Cancelled'])->default('Pending');
            $table->foreignId('collected_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};

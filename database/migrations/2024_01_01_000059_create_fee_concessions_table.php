<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_concessions', function (Blueprint $table) {
            $table->id('concession_id');
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('ledger_id')
                ->constrained('student_fee_ledgers', 'ledger_id')
                ->cascadeOnDelete();

            $table->enum('concession_type', ['Merit', 'Sports', 'Staff Ward', 'Physically Challenged']);
            $table->float('concession_amount')->nullable(); // fixed amount
            $table->float('concession_pct')->nullable();    // percentage discount
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->date('approved_on')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_concessions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_tickets', function (Blueprint $table) {
            $table->id('hall_ticket_id');
            $table->foreignId('config_id')
                ->constrained('hall_ticket_configs', 'config_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->cascadeOnDelete();
            $table->foreignId('enrollment_id')
                ->constrained('student_enrollments', 'enrollment_id')
                ->cascadeOnDelete();

            $table->string('hall_ticket_no', 30)->unique();
            $table->enum('exam_type', ['Theory', 'Practical', 'Both'])->nullable();
            $table->enum('status', ['Draft', 'Generated', 'Downloaded', 'Revoked'])->default('Draft');
            $table->boolean('is_eligible')->nullable(); // final eligibility flag
            $table->text('ineligibility_reason')->nullable();

            // Business-rule flags (see Cross-Module FK Reference in the source spec):
            // attendance_cleared mirrors ATTENDANCE_SUMMARY.attendance_percentage >= config threshold
            // fees_cleared mirrors STUDENT_FEE_LEDGER.is_hall_ticket_cleared = TRUE
            // Both are computed/synced in application logic, not DB foreign keys.
            $table->boolean('attendance_cleared')->default(false);
            $table->boolean('fees_cleared')->default(false);

            $table->boolean('generated')->default(false);
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('downloaded_at')->nullable();
            $table->text('qr_code_data')->nullable(); // JSON payload for QR
            $table->string('barcode', 100)->nullable();
            $table->foreignId('generated_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_tickets');
    }
};

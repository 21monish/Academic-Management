<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_ticket_configs', function (Blueprint $table) {
            $table->id('config_id');
            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->cascadeOnDelete();
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();

            $table->date('issue_start_date')->nullable();
            $table->date('issue_end_date')->nullable();
            $table->float('min_attendance_pct')->nullable(); // e.g. 75.0
            $table->boolean('fees_clearance_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable(); // printed on hall ticket
            $table->string('principal_signature_url', 300)->nullable();
            $table->string('college_seal_url', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_ticket_configs');
    }
};

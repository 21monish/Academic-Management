<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->foreignId('staff_id')
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete(); // applicant
            $table->foreignId('leave_type_id')
                ->constrained('leave_types', 'leave_type_id')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years', 'academic_year_id')
                ->cascadeOnDelete();

            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_days')->nullable();
            $table->enum('half_day_type', ['None', 'Morning', 'Afternoon'])->default('None');
            $table->text('reason')->nullable();
            $table->string('document_url', 300)->nullable(); // medical cert etc.
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Draft');
            $table->foreignId('applied_to_staff_id')->nullable()
                ->constrained('staff', 'staff_id')
                ->nullOnDelete(); // reporting authority
            $table->text('applicant_remarks')->nullable();
            $table->timestamp('applied_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};

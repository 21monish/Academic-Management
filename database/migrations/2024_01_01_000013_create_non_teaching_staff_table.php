<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_teaching_staff', function (Blueprint $table) {
            $table->id('nt_staff_id');
            $table->foreignId('staff_id')->unique() // enforces the 1-to-1 relationship
                ->constrained('staff', 'staff_id')
                ->cascadeOnDelete();

            $table->enum('role', ['Peon', 'Lab Assistant', 'Lab Technician', 'Librarian', 'Accountant', 'Clerk', 'Office Assistant', 'Transport Staff', 'Security Staff'])->nullable();
            $table->string('department_section', 100)->nullable();
            $table->string('grade', 50)->nullable(); // pay grade / band
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_teaching_staff');
    }
};

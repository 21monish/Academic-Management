<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id('college_id');
            $table->foreignId('university_id')
                ->constrained('universities', 'university_id')
                ->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->string('name', 200);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('principal_name', 150)->nullable();
            $table->enum('affiliation_type', ['Autonomous', 'Affiliated', 'Constituent'])->nullable();
            $table->enum('college_type', ['Government', 'Private', 'Grant-in-Aid'])->nullable();
            $table->date('affiliated_on')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};

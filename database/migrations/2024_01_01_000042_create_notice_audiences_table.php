<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_audiences', function (Blueprint $table) {
            $table->id('audience_id');
            $table->foreignId('notice_id')
                ->constrained('notices', 'notice_id')
                ->cascadeOnDelete();

            $table->enum('target_type', ['Department', 'Programme', 'Semester', 'Role', 'Individual']);
            // Polymorphic: target_id points to the respective table based on target_type.
            // Resolved in application code - no DB-level FK since the target table varies.
            $table->unsignedBigInteger('target_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_audiences');
    }
};

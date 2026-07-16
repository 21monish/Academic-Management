<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id('approval_id');
            $table->foreignId('requested_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('action', 80);
            $table->string('subject_type', 150);
            $table->unsignedBigInteger('subject_id');
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();

            $table->index(['status', 'action']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['requested_by', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};

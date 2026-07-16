<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_audits', function (Blueprint $table) {
            $table->id('audit_id');
            $table->foreignId('actor_user_id')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('permission_id')->nullable()
                ->constrained('permissions', 'permission_id')
                ->nullOnDelete();
            $table->string('action', 20);
            $table->string('context', 80)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_user_id', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();
            $table->string('token_hash', 255);
            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();
            $table->dateTime('login_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_revoked')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};

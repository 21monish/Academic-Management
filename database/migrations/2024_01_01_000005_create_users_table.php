<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('role_id')->nullable()
                ->constrained('user_roles', 'role_id')
                ->nullOnDelete();

            // college_id FK is added later (2024_01_01_000010) once colleges table exists
            $table->unsignedBigInteger('college_id')->nullable();

            // Polymorphic link to STAFF or STUDENT (resolved in app code, not a DB FK)
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('reference_type', ['Staff', 'Student'])->nullable();

            $table->string('username', 80)->unique();
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->dateTime('last_login')->nullable();
            $table->string('reset_token', 255)->nullable();
            $table->dateTime('reset_token_expiry')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Kept for Laravel's built-in password reset flow
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Laravel's own session store (SESSION_DRIVER=database).
        // This is separate from the app-level user_sessions audit table below.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

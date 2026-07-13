<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_acknowledgements', function (Blueprint $table) {
            $table->id('ack_id');
            $table->foreignId('notice_id')
                ->constrained('notices', 'notice_id')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();

            $table->timestamp('acknowledged_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();

            $table->unique(['notice_id', 'user_id'], 'notice_user_ack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_acknowledgements');
    }
};

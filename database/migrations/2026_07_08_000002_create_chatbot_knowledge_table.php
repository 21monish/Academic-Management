<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_knowledge', function (Blueprint $table) {
            $table->id('chatbot_knowledge_id');
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('question', 500);
            $table->string('normalized_question', 500)->unique();
            $table->text('answer');
            $table->unsignedInteger('hits')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_knowledge');
    }
};

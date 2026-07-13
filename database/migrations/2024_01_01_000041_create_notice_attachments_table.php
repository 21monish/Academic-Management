<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_attachments', function (Blueprint $table) {
            $table->id('attachment_id');
            $table->foreignId('notice_id')
                ->constrained('notices', 'notice_id')
                ->cascadeOnDelete();

            $table->string('file_name', 200)->nullable();
            $table->string('file_url', 500); // cloud / server path
            $table->string('file_type', 50)->nullable(); // PDF/DOCX/IMG
            $table->integer('file_size_kb')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_attachments');
    }
};

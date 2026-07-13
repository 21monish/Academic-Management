<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id('notice_id');
            $table->foreignId('college_id')
                ->constrained('colleges', 'college_id')
                ->cascadeOnDelete();
            $table->foreignId('notice_category_id')->nullable()
                ->constrained('notice_categories', 'notice_category_id')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();

            $table->string('title', 300);
            $table->text('content')->nullable(); // full body of notice
            $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent'])->default('Normal');
            $table->enum('audience_type', ['All', 'College', 'Dept', 'Programme', 'Semester', 'Role']);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable(); // NULL = indefinite
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('requires_acknowledgement')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('module_name', 80); // e.g. attendance, result
            $table->string('action', 50);       // e.g. view, create, approve
            $table->string('description', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id('university_id');
            $table->string('name', 200);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 200)->nullable();
            $table->date('established_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};

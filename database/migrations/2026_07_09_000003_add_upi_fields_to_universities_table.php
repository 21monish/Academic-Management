<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->string('upi_id', 100)->nullable()->after('logo_url');
            $table->string('upi_name', 150)->nullable()->after('upi_id');
            $table->string('upi_note_prefix', 100)->nullable()->after('upi_name');
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn(['upi_id', 'upi_name', 'upi_note_prefix']);
        });
    }
};

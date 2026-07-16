<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('license_plans')) {
            Schema::create('license_plans', function (Blueprint $table) {
                $table->id('plan_id');
                $table->string('code', 40)->unique();
                $table->string('name', 120);
                $table->decimal('monthly_price', 10, 2)->default(0);
                $table->unsignedInteger('max_students')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('universities', function (Blueprint $table) {
            if (! Schema::hasColumn('universities', 'license_plan_id')) {
                $table->unsignedBigInteger('license_plan_id')->nullable()->index();
            }

            if (! Schema::hasColumn('universities', 'license_status')) {
                $table->string('license_status', 30)->default('Active')->index();
            }

            if (! Schema::hasColumn('universities', 'license_expires_on')) {
                $table->date('license_expires_on')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            foreach (['license_plan_id', 'license_status', 'license_expires_on'] as $column) {
                if (Schema::hasColumn('universities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('license_plans');
    }
};

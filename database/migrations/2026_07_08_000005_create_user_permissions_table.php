<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id('up_id');
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained('permissions', 'permission_id')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'permission_id']);
        });

        DB::table('users')
            ->join('role_permissions', 'users.role_id', '=', 'role_permissions.role_id')
            ->select('users.user_id', 'role_permissions.permission_id')
            ->orderBy('users.user_id')
            ->chunk(500, function ($rows): void {
                $records = $rows->map(fn ($row) => [
                    'user_id' => $row->user_id,
                    'permission_id' => $row->permission_id,
                ])->all();

                DB::table('user_permissions')->insertOrIgnore($records);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};

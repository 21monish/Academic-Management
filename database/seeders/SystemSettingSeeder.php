<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SystemSettingService::defaults() as $key => $value) {
            SystemSetting::firstOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $value,
                    'updated_at' => now(),
                ]
            );
        }
    }
}

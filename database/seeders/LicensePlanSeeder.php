<?php

namespace Database\Seeders;

use App\Models\LicensePlan;
use Illuminate\Database\Seeder;

class LicensePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'basic',
                'name' => 'Basic',
                'monthly_price' => 2999,
                'max_students' => 1000,
                'features' => ['core', 'institution', 'people', 'academic', 'reports', 'certificates', 'notices'],
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'monthly_price' => 6999,
                'max_students' => 5000,
                'features' => ['core', 'institution', 'people', 'academic', 'attendance', 'exams', 'fees', 'reports', 'certificates', 'notices', 'chatbot'],
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'monthly_price' => 11999,
                'max_students' => null,
                'features' => ['*'],
            ],
        ];

        foreach ($plans as $plan) {
            LicensePlan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan + ['is_active' => true]
            );
        }
    }
}

<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Str;
use Throwable;

class SystemSettingService
{
    public static function defaults(): array
    {
        return [
            'application_name' => config('app.name', 'GTU ITR'),
            'application_short_name' => 'Academic Management',
            'created_by' => 'Monish Kanojiya',
            'created_by_contact' => '9313623141',
            'footer_text' => 'Developed by Monish Kanojiya | 9313623141',
            'support_email' => null,
            'support_phone' => null,
            'logo_url' => null,
        ];
    }

    public static function editableKeys(): array
    {
        return array_keys(self::defaults());
    }

    public function all(): array
    {
        $settings = self::defaults();

        try {
            $storedSettings = SystemSetting::query()
                ->pluck('setting_value', 'setting_key')
                ->all();

            foreach ($storedSettings as $key => $value) {
                if (array_key_exists($key, $settings)) {
                    $settings[$key] = $value;
                }
            }
        } catch (Throwable) {
            return $settings;
        }

        return $settings;
    }

    public function branding(): array
    {
        $settings = $this->all();
        $settings['logo_url'] = $this->publicUrl($settings['logo_url']);

        return $settings;
    }

    public function publicUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset($path);
    }

    public function save(array $values, ?int $updatedBy = null): void
    {
        foreach (self::editableKeys() as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $value === '' ? null : $value,
                    'updated_by' => $updatedBy,
                    'updated_at' => now(),
                ]
            );
        }
    }
}

<?php

namespace App\Services;

use App\Models\Setting;

class SettingStore
{
    /**
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public static function get(string $name, array $default = []): array
    {
        $setting = Setting::query()->where('name', $name)->first();

        if (! $setting instanceof Setting) {
            return $default;
        }

        $payload = $setting->payload;

        if (! is_array($payload)) {
            return $default;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function set(string $name, array $payload, string $group = 'general'): Setting
    {
        return Setting::query()->updateOrCreate(
            ['name' => $name],
            [
                'group' => $group,
                'payload' => $payload,
                'locked' => false,
            ],
        );
    }
}

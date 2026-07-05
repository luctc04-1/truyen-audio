<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettings
{
    private const DEFAULTS = [
        'site_name'                => 'Truyen Audio',
        'site_url'                 => 'https://truyenaudio.local',
        'storage_driver'           => 'local',
        'free_episodes_before_vip' => 10,
        'registration_enabled'     => true,
        'default_meta_title'       => 'Truyện Audio - Nghe truyện Việt Nam',
        'default_meta_description' => 'Kho truyện audio chọn lọc, nghe mọi lúc mọi nơi.',
        'default_og_image'         => '/images/og-default.jpg',
        'robots'                   => 'index,follow',
    ];

    public static function all(): array
    {
        return Cache::remember('system_settings', 300, function () {
            $stored = SystemSetting::query()
                ->whereIn('setting_key', array_keys(self::DEFAULTS))
                ->pluck('setting_value', 'setting_key');

            $settings = self::DEFAULTS;

            foreach ($stored as $key => $value) {
                if (is_array($value) && array_key_exists('value', $value)) {
                    $settings[$key] = $value['value'];
                }
            }

            return $settings;
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    public static function forgetCache(): void
    {
        Cache::forget('system_settings');
    }
}

<?php

namespace App\Modules\Admin\Controllers;

use App\Models\SystemSetting;
use App\Support\SystemSettings;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsAdminController extends BaseController
{
    private const DEFAULTS = [
        'site_name'                 => 'Truyen Audio',
        'site_url'                  => 'https://truyenaudio.local',
        'storage_driver'            => 'local',
        'free_episodes_before_vip'  => 10,
        'registration_enabled'        => true,
        'default_meta_title'        => 'Truyện Audio - Nghe truyện Việt Nam',
        'default_meta_description'  => 'Kho truyện audio chọn lọc, nghe mọi lúc mọi nơi.',
        'default_og_image'          => '/images/og-default.jpg',
        'robots'                    => 'index,follow',
    ];

    public function show(): JsonResponse
    {
        return $this->success($this->allSettings());
    }

    public function publicConfig(): JsonResponse
    {
        $settings = SystemSettings::all();

        return $this->success([
            'site_name'                => $settings['site_name'],
            'registration_enabled'     => (bool) $settings['registration_enabled'],
            'free_episodes_before_vip' => (int) $settings['free_episodes_before_vip'],
            'default_meta_title'       => $settings['default_meta_title'],
            'default_meta_description' => $settings['default_meta_description'],
            'default_og_image'         => $settings['default_og_image'],
            'robots'                   => $settings['robots'],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_name'                => 'sometimes|string|max:255',
            'site_url'                 => 'sometimes|string|max:500',
            'storage_driver'           => 'sometimes|string|in:local,s3',
            'free_episodes_before_vip' => 'sometimes|integer|min:0|max:100',
            'registration_enabled'     => 'sometimes|boolean',
            'default_meta_title'       => 'sometimes|string|max:255',
            'default_meta_description' => 'sometimes|string|max:1000',
            'default_og_image'         => 'sometimes|string|max:500',
            'robots'                   => 'sometimes|string|max:100',
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => ['value' => $value]]
            );
        }

        SystemSettings::forgetCache();

        return $this->success($this->allSettings(), 'Đã lưu cài đặt');
    }

    private function allSettings(): array
    {
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
    }
}

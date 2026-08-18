<?php

namespace App\Modules\Admin\Controllers;

use App\Models\SystemSetting;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends BaseController
{
    public function index(): JsonResponse
    {
        $settings = SystemSetting::all()->pluck('setting_value', 'setting_key')->toArray();

        $defaultSettings = [
            'site_name' => 'Truyện Audio Hay',
            'site_description' => 'Kho truyện audio chọn lọc chất lượng cao',
            'main_domain' => config('app.url', 'http://truyen-audio.me'),
            'storage_provider' => 'local',
            'free_episodes_limit' => 10,
            'allow_registration' => true,
            'meta_title' => 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online',
            'meta_description' => 'Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya, ngôn tình, tiên hiệp mượt mà chất lượng cao.',
            'og_image' => '/android-chrome-512x512.png',
            'robots' => 'index,follow',
        ];

        $merged = array_merge($defaultSettings, $settings);

        return $this->success($merged);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'main_domain' => 'nullable|string|max:255',
            'storage_provider' => 'nullable|string|max:50',
            'free_episodes_limit' => 'nullable|integer|min:0',
            'allow_registration' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'og_image' => 'nullable|string',
            'robots' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return $this->success($validated, 'Lưu cài đặt hệ thống thành công');
    }
}

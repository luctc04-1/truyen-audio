<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['code' => 'vip_1m',  'name' => 'VIP 1 tháng',  'price' => 37000,  'duration_days' => 30],
            ['code' => 'vip_3m',  'name' => 'VIP 3 tháng',  'price' => 99000,  'duration_days' => 90],
            ['code' => 'vip_6m',  'name' => 'VIP 6 tháng',  'price' => 169000, 'duration_days' => 180],
            ['code' => 'vip_12m', 'name' => 'VIP 12 tháng', 'price' => 289000, 'duration_days' => 365],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['code' => $plan['code']],
                array_merge($plan, ['description' => null, 'is_active' => true])
            );
        }
    }
}

<?php

namespace App\Modules\Plan\Support;

use App\Models\Plan;

class PlanPresenter
{
    private const BADGES = [
        'vip_3m'  => ['badge' => 'Phổ biến'],
        'vip_12m' => ['badge' => 'Tiết kiệm nhất', 'badgeColor' => 'green'],
    ];

    public static function forVipPage(Plan $plan, float $baseMonthlyPrice): array
    {
        $months    = max($plan->duration_days / 30, 1);
        $price     = (float) $plan->price;
        $monthly   = (int) round($price / $months);
        $fullPrice = $baseMonthlyPrice * $months;
        $discount  = $fullPrice > $price
            ? (int) round((1 - $price / $fullPrice) * 100)
            : 0;

        $badge = self::BADGES[$plan->code] ?? [];

        return [
            'id'         => $plan->id,
            'code'       => $plan->code,
            'duration'   => self::label($plan),
            'price'      => self::formatVnd($price),
            'per'        => $months <= 1
                ? null
                : '~' . self::formatVnd($monthly, false) . '/tháng',
            'discount'   => $discount > 0 ? "-{$discount}%" : null,
            'badge'      => $badge['badge'] ?? null,
            'badgeColor' => $badge['badgeColor'] ?? null,
        ];
    }

    private static function label(Plan $plan): string
    {
        return match ($plan->code) {
            'vip_1m'  => '1 tháng',
            'vip_3m'  => '3 tháng',
            'vip_6m'  => '6 tháng',
            'vip_12m' => '12 tháng',
            default   => $plan->name,
        };
    }

    private static function formatVnd(float $amount, bool $withSuffix = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');

        return $withSuffix ? "{$formatted}đ" : $formatted;
    }
}

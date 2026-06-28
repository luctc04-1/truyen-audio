<?php

namespace App\Modules\Plan\Controllers;

use App\Models\Plan;
use App\Modules\Plan\Support\PlanPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class PlanController extends BaseController
{
    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->active()
            ->orderBy('duration_days')
            ->get();

        $baseMonthly = (float) ($plans->firstWhere('code', 'vip_1m')?->price ?? 37000);

        $data = $plans
            ->map(fn (Plan $plan) => PlanPresenter::forVipPage($plan, $baseMonthly))
            ->values();

        return $this->success($data);
    }
}

<?php

namespace App\Modules\Payment\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function activateFromOrder(User $user, Plan $plan, Order $order): Subscription
    {
        return DB::transaction(function () use ($user, $plan, $order) {
            $active = $user->subscriptions()
                ->currentlyValid()
                ->orderByDesc('end_at')
                ->lockForUpdate()
                ->first();

            $startAt = now();
            $endAt = ($active && $active->end_at?->isFuture())
                ? $active->end_at->copy()->addDays($plan->duration_days)
                : $startAt->copy()->addDays($plan->duration_days);

            Subscription::query()
                ->where('user_id', $user->id)
                ->active()
                ->update(['is_active' => false]);

            return Subscription::create([
                'user_id'    => $user->id,
                'plan_id'    => $plan->id,
                'order_id'   => $order->id,
                'start_at'   => $startAt,
                'end_at'     => $endAt,
                'is_active'  => true,
                'created_at' => now(),
            ]);
        });
    }
}

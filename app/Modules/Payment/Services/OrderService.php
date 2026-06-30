<?php

namespace App\Modules\Payment\Services;

use App\Events\VipPaymentSucceeded;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\Exceptions\APIException;

class OrderService
{
    public function __construct(
        protected PayOsService $payOsService,
        protected SubscriptionService $subscriptionService,
        protected AuthService $authService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createCheckout(User $user, string $planId): array
    {
        $plan = Plan::query()->active()->findOrFail($planId);

        $order = Order::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'order_code'     => $this->generateOrderCode(),
            'amount'         => $plan->price,
            'status'         => 'pending',
            'payment_method' => 'payos',
            'created_at'     => now(),
        ]);

        try {
            $payment = $this->payOsService->createPaymentLink($user, $plan, $order);
        } catch (APIException $e) {
            $order->update(['status' => 'failed']);
            Log::error('PayOS create payment failed', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'order_id'         => $order->id,
            'order_code'       => (int) $order->order_code,
            'checkout_url'     => $payment->checkoutUrl,
            'qr_code'          => $payment->qrCode,
            'qr_payload'       => self::extractQrPayload($payment->qrCode),
            'amount'           => (int) $payment->amount,
            'plan_name'        => $plan->name,
            'account_number'   => $payment->accountNumber,
            'account_name'     => $payment->accountName,
            'bank_bin'         => $payment->bin,
            'transfer_content' => $order->order_code,
        ];
    }

    public function markPaid(int $orderCode): ?Order
    {
        return DB::transaction(function () use ($orderCode) {
            $order = Order::query()
                ->where('order_code', (string) $orderCode)
                ->lockForUpdate()
                ->first();

            if (! $order || $order->status === 'paid') {
                return $order;
            }

            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $order->load(['user', 'plan']);

            $subscription = $this->subscriptionService->activateFromOrder(
                $order->user,
                $order->plan,
                $order,
            );

            $userPayload = $this->authService->formatUser($order->user->fresh());

            VipPaymentSucceeded::dispatch(
                $order->user_id,
                (int) $order->order_code,
                $userPayload,
                [
                    'plan_name' => $subscription->plan?->name,
                    'end_at'    => $subscription->end_at?->toIso8601String(),
                ],
            );

            return $order->fresh();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function statusForUser(User $user, int $orderCode): ?array
    {
        $order = Order::query()
            ->where('order_code', (string) $orderCode)
            ->where('user_id', $user->id)
            ->with('plan')
            ->first();

        if (! $order) {
            return null;
        }

        return [
            'order_code' => (int) $order->order_code,
            'status'     => $order->status,
            'plan_name'  => $order->plan?->name,
            'amount'     => (float) $order->amount,
            'paid_at'    => $order->paid_at?->toIso8601String(),
        ];
    }

    protected function generateOrderCode(): string
    {
        do {
            $code = (string) random_int(1_000_000, 2_147_483_647);
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }

    protected static function extractQrPayload(string $qrCode): ?string
    {
        $qr = trim($qrCode);

        if ($qr === '') {
            return null;
        }

        if (str_starts_with($qr, '000201')) {
            return $qr;
        }

        if (str_starts_with($qr, 'http://') || str_starts_with($qr, 'https://') || str_starts_with($qr, 'data:')) {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9+/=]+$/', $qr) && strlen($qr) > 200) {
            return null;
        }

        return $qr;
    }
}

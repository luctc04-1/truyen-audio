<?php

namespace App\Modules\Payment\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkResponse;
use PayOS\Models\Webhooks\WebhookData;
use PayOS\PayOS;

class PayOsService
{
    public function client(): PayOS
    {
        return new PayOS(
            clientId: config('payos.client_id'),
            apiKey: config('payos.api_key'),
            checksumKey: config('payos.checksum_key'),
        );
    }

    public function createPaymentLink(User $user, Plan $plan, Order $order): CreatePaymentLinkResponse
    {
        $returnUrl = config('payos.return_url');
        $cancelUrl = config('payos.cancel_url');

        $separator = str_contains($returnUrl, '?') ? '&' : '?';
        $returnUrl .= $separator.'order_code='.$order->order_code;

        $request = new CreatePaymentLinkRequest(
            orderCode: (int) $order->order_code,
            amount: (int) $order->amount,
            description: $order->order_code,
            cancelUrl: $cancelUrl,
            returnUrl: $returnUrl,
            buyerName: Str::limit($user->username ?? $user->email, 50, ''),
            buyerEmail: $user->email,
        );

        return $this->client()->paymentRequests->create($request);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhook(array $payload): WebhookData
    {
        return $this->client()->webhooks->verify($payload);
    }
}

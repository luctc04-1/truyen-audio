<?php

namespace App\Modules\Payment\Controllers;

use App\Modules\Payment\Services\OrderService;
use App\Modules\Payment\Services\PayOsService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayOS\Exceptions\WebhookException;

class PayOsWebhookController extends BaseController
{
    public function __construct(
        protected PayOsService $payOsService,
        protected OrderService $orderService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $webhookData = $this->payOsService->verifyWebhook($request->all());
        } catch (WebhookException $e) {
            Log::warning('PayOS webhook verification failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        if ($webhookData->code !== '00') {
            return response()->json([
                'success' => true,
                'message' => 'Ignored non-success payment.',
            ]);
        }

        $this->orderService->markPaid($webhookData->orderCode);

        return response()->json([
            'success' => true,
            'message' => 'OK',
        ]);
    }
}

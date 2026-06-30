<?php

namespace App\Modules\Payment\Controllers;

use App\Modules\Payment\Services\OrderService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PayOS\Exceptions\APIException;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => 'required|uuid|exists:plans,id',
        ]);

        try {
            $checkout = $this->orderService->createCheckout(
                $request->user(),
                $data['plan_id'],
            );

            return $this->success($checkout, 'Tạo đơn thanh toán thành công', 201);
        } catch (APIException $e) {
            return $this->error('Không tạo được liên kết thanh toán PayOS. Vui lòng thử lại.', 502);
        }
    }

    public function show(Request $request, int $orderCode): JsonResponse
    {
        $status = $this->orderService->statusForUser($request->user(), $orderCode);

        if (! $status) {
            return $this->error('Không tìm thấy đơn hàng.', 404);
        }

        return $this->success($status);
    }
}

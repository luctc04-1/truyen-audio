<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Plan;
use App\Modules\Admin\Support\AdminPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanAdminController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'          => 'required|string|max:50|unique:plans,code',
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description'   => 'nullable|string|max:1000',
            'is_active'     => 'sometimes|boolean',
        ]);

        $plan = Plan::create([
            ...$data,
            'is_active'  => $data['is_active'] ?? true,
            'created_at' => now(),
        ]);

        return $this->success(
            AdminPresenter::plan($plan->loadCount('orders')),
            'Đã tạo gói VIP',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $plan = Plan::withCount('orders')->find($id);

        if (! $plan) {
            return $this->error('Không tìm thấy gói VIP', 404);
        }

        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'price'         => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'description'   => 'nullable|string|max:1000',
            'is_active'     => 'sometimes|boolean',
        ]);

        $plan->fill($data)->save();

        return $this->success(
            AdminPresenter::plan($plan->fresh()->loadCount('orders')),
            'Đã cập nhật gói VIP'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $plan = Plan::withCount('orders')->find($id);

        if (! $plan) {
            return $this->error('Không tìm thấy gói VIP', 404);
        }

        if ($plan->orders_count > 0) {
            return $this->error('Không thể xóa gói đã có đơn hàng', 422);
        }

        $plan->delete();

        return $this->success(null, 'Đã xóa gói VIP');
    }
}

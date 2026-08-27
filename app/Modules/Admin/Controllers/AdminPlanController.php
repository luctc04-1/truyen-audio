<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Plan;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPlanController extends BaseController
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('orders')->orderBy('price', 'asc')->get();
        return $this->success($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:plans,code',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $plan = Plan::create($validated);
        return $this->success($plan, 'Tạo gói VIP thành công', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->error('Không tìm thấy gói VIP', 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:plans,code,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $plan->update($validated);
        return $this->success($plan, 'Cập nhật gói VIP thành công');
    }

    public function destroy(string $id): JsonResponse
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->error('Không tìm thấy gói VIP', 404);
        }

        if ($plan->orders()->exists()) {
            return $this->error('Không thể xóa gói đã có đơn hàng. Vui lòng chuyển sang trạng thái ngưng hoạt động.', 400);
        }

        $plan->delete();
        return $this->success(null, 'Đã xóa gói VIP');
    }
}

<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Order;
use App\Models\Subscription;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user:id,username,email', 'plan:id,code,name,price,duration_days']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'ilike', "%{$keyword}%")
                  ->orWhereHas('user', function ($sq) use ($keyword) {
                      $sq->where('username', 'ilike', "%{$keyword}%")
                        ->orWhere('email', 'ilike', "%{$keyword}%");
                  });
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $order = Order::with('plan')->find($id);

        if (!$order) {
            return $this->error('Không tìm thấy đơn hàng', 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:paid,pending,cancelled,refunded',
        ]);

        $order->status = $validated['status'];
        if ($validated['status'] === 'paid' && !$order->paid_at) {
            $order->paid_at = now();

            // Grant subscription if not exists
            if ($order->plan && $order->user_id) {
                $now = now();
                $endAt = $now->copy()->addDays($order->plan->duration_days ?: 30);
                Subscription::create([
                    'user_id' => $order->user_id,
                    'plan_id' => $order->plan_id,
                    'order_id' => $order->id,
                    'start_at' => $now,
                    'end_at' => $endAt,
                    'is_active' => true,
                ]);
            }
        }

        $order->save();

        return $this->success($order, 'Cập nhật trạng thái đơn hàng thành công');
    }
}

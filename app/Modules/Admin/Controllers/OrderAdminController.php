<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Modules\Payment\Services\SubscriptionService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderAdminController extends BaseController
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user:id,username,email', 'plan:id,code,name']);

        if ($status = trim((string) $request->input('status'))) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('order_code', 'ilike', "%{$escaped}%")
                    ->orWhereHas('user', function ($uq) use ($escaped) {
                        $uq->where('username', 'ilike', "%{$escaped}%")
                            ->orWhere('email', 'ilike', "%{$escaped}%");
                    });
            });
        }

        $perPage   = min((int) $request->input('per_page', 20), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Order $order) => AdminPresenter::order($order))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::query()->with(['user', 'plan', 'subscription'])->find($id);

        if (! $order) {
            return $this->error('Không tìm thấy đơn hàng', 404);
        }

        $data = $request->validate([
            'status' => 'required|string|in:paid,pending,cancelled,failed',
        ]);

        $order->status = $data['status'];

        if ($data['status'] === 'paid' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        if (in_array($data['status'], ['cancelled', 'failed'], true)) {
            $order->paid_at = null;
        }

        $order->save();

        if ($data['status'] === 'paid' && $order->user && $order->plan && ! $order->subscription()->exists()) {
            $this->subscriptionService->activateFromOrder($order->user, $order->plan, $order);
        }

        return $this->success(
            AdminPresenter::order($order->fresh()->load(['user:id,username,email', 'plan:id,code,name'])),
            'Đã cập nhật đơn hàng'
        );
    }

    public function plans(): JsonResponse
    {
        $plans = Plan::query()
            ->withCount('orders')
            ->orderBy('duration_days')
            ->get();

        return $this->success(
            $plans->map(fn (Plan $plan) => AdminPresenter::plan($plan))->values()
        );
    }
}

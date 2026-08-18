<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount(['listeningHistories', 'favorites', 'orders', 'comments', 'devices'])
            ->with(['subscriptions' => function ($q) {
                $q->with('plan')->orderByDesc('end_at')->limit(1);
            }]);

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'ilike', "%{$keyword}%")
                  ->orWhere('email', 'ilike', "%{$keyword}%");
            });
        }

        if ($request->filled('is_admin')) {
            $query->where('is_admin', filter_var($request->input('is_admin'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        // Append is_premium to items
        $items = collect($paginator->items())->map(function ($user) {
            $latestSub = $user->subscriptions->first();
            $isVip = $latestSub && $latestSub->end_at && $latestSub->end_at->isFuture();
            
            return array_merge($user->toArray(), [
                'is_vip' => (bool) $isVip,
                'vip_plan' => $isVip ? ($latestSub->plan?->name ?? 'VIP') : null,
                'vip_end_at' => $isVip ? $latestSub->end_at->format('d/m/Y H:i') : null,
            ]);
        });

        return $this->success([
            'items' => $items,
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with(['devices', 'orders.plan', 'subscriptions.plan'])
            ->withCount(['listeningHistories', 'favorites', 'comments'])
            ->find($id);

        if (!$user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        return $this->success($user);
    }

    public function toggleAdmin(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return $this->success($user, $user->is_admin ? 'Đã cấp quyền Quản trị viên' : 'Đã hủy quyền Quản trị viên');
    }

    public function grantVip(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'plan_id' => 'nullable|uuid|exists:plans,id',
        ]);

        $days = (int) $validated['days'];
        $planId = $validated['plan_id'] ?? Plan::first()?->id;

        $now = now();
        $endAt = $now->copy()->addDays($days);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'start_at' => $now,
            'end_at' => $endAt,
            'is_active' => true,
        ]);

        return $this->success($subscription, "Đã cấp VIP {$days} ngày cho người dùng");
    }
}

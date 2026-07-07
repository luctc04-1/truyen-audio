<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Modules\Payment\Services\SubscriptionService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAdminController extends BaseController
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount(['devices', 'listeningHistories']);

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('username', 'ilike', "%{$escaped}%")
                    ->orWhere('email', 'ilike', "%{$escaped}%");
            });
        }

        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        if ($request->has('is_banned')) {
            $query->where('is_banned', $request->boolean('is_banned'));
        }

        if ($request->has('is_premium')) {
            if ($request->boolean('is_premium')) {
                $query->whereHas('subscriptions', fn ($q) => $q->currentlyValid());
            } else {
                $query->whereDoesntHave('subscriptions', fn ($q) => $q->currentlyValid());
            }
        }

        $perPage   = min((int) $request->input('per_page', 20), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (User $user) => AdminPresenter::user($user))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (! $user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        $data = $request->validate([
            'is_admin'  => 'sometimes|boolean',
            'is_banned' => 'sometimes|boolean',
            'username'  => 'sometimes|string|max:100',
        ]);

        $user->fill($data)->save();

        return $this->success(
            AdminPresenter::user($user->fresh()->loadCount(['devices', 'listeningHistories'])),
            'Cập nhật người dùng thành công'
        );
    }

    public function grantVip(Request $request, string $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (! $user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        $data = $request->validate([
            'plan_id' => 'required|uuid|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $subscription = $this->subscriptionService->grantManual($user, $plan);

        return $this->success(
            AdminPresenter::user($user->fresh()->loadCount(['devices', 'listeningHistories'])),
            "Đã cấp VIP {$plan->name} đến {$subscription->end_at->format('d/m/Y')}"
        );
    }

    public function revokeVip(string $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (! $user) {
            return $this->error('Không tìm thấy người dùng', 404);
        }

        $this->subscriptionService->revokeActive($user);

        return $this->success(
            AdminPresenter::user($user->fresh()->loadCount(['devices', 'listeningHistories'])),
            'Đã thu hồi quyền VIP'
        );
    }

    private function findUser(string $id): ?User
    {
        return User::withCount(['devices', 'listeningHistories'])->find($id);
    }
}

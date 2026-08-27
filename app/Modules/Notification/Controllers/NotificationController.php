<?php

namespace App\Modules\Notification\Controllers;

use App\Models\UserNotification;
use App\Modules\Notification\Services\NotificationService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * Lấy danh sách thông báo của người dùng hiện tại (phân trang).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        $unreadOnly = $request->boolean('unread_only', false);
        $type = $request->input('type');

        $query = UserNotification::query()
            ->where('user_id', $user->id)
            ->with('notification')
            ->orderByDesc('created_at');

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        if ($type) {
            $query->whereHas('notification', fn ($q) => $q->where('type', $type));
        }

        $paginator = $query->paginate($perPage);

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        $items = $paginator->getCollection()
            ->map(fn (UserNotification $un) => $this->notificationService->formatUserNotification($un))
            ->values();

        return $this->success([
            'items'        => $items,
            'unread_count' => $unreadCount,
            'pagination'   => $this->paginationMeta($paginator),
        ], 'Danh sách thông báo');
    }

    /**
     * Lấy nhanh số lượng thông báo chưa đọc.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user->id);

        return $this->success([
            'unread_count' => $count,
        ], 'Số lượng thông báo chưa đọc');
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $userNotification = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->with('notification')
            ->first();

        if (! $userNotification) {
            return $this->error('Không tìm thấy thông báo', 404);
        }

        if (! $userNotification->is_read) {
            $userNotification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return $this->success([
            'notification' => $this->notificationService->formatUserNotification($userNotification),
            'unread_count' => $unreadCount,
        ], 'Đã đánh dấu đã đọc');
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng là đã đọc.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        UserNotification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success([
            'unread_count' => 0,
        ], 'Đã đánh dấu tất cả thông báo là đã đọc');
    }

    /**
     * Xóa 1 thông báo.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $userNotification = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $userNotification) {
            return $this->error('Không tìm thấy thông báo', 404);
        }

        $userNotification->delete();
        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return $this->success([
            'id'           => $id,
            'unread_count' => $unreadCount,
        ], 'Đã xóa thông báo');
    }

    /**
     * Xóa toàn bộ thông báo của người dùng.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();

        UserNotification::query()
            ->where('user_id', $user->id)
            ->delete();

        return $this->success([
            'unread_count' => 0,
        ], 'Đã xóa toàn bộ thông báo');
    }

    /**
     * Đăng ký thiết bị nhận Web Push Notification.
     */
    public function subscribePush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'   => 'required|string',
            'public_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
        ]);

        $user = $request->user();

        $subscription = \App\Models\PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id'    => $user?->id,
                'public_key' => $data['public_key'] ?? null,
                'auth_token' => $data['auth_token'] ?? null,
                'user_agent' => $request->userAgent(),
            ]
        );

        return $this->success([
            'id'       => $subscription->id,
            'endpoint' => $subscription->endpoint,
        ], 'Đăng ký nhận thông báo đẩy thành công');
    }

    /**
     * Hủy đăng ký nhận Web Push Notification.
     */
    public function unsubscribePush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
        ]);

        \App\Models\PushSubscription::where('endpoint', $data['endpoint'])->delete();

        return $this->success(null, 'Đã hủy nhận thông báo đẩy');
    }
}

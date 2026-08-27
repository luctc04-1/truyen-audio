<?php

namespace App\Modules\Notification\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Tạo thông báo và gán cho 1 người dùng cụ thể, đồng thời phát realtime event.
     */
    public function createForUser(
        string $userId,
        string $title,
        string $content,
        string $type = 'system',
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $actionUrl = null,
    ): ?UserNotification {
        return DB::transaction(function () use ($userId, $title, $content, $type, $referenceType, $referenceId, $actionUrl) {
            $notification = Notification::create([
                'title'          => $title,
                'content'        => $content,
                'type'           => $type,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'action_url'     => $actionUrl,
                'created_at'     => now(),
            ]);

            $userNotification = UserNotification::create([
                'user_id'         => $userId,
                'notification_id' => $notification->id,
                'is_read'         => false,
                'read_at'         => null,
                'created_at'      => now(),
            ]);

            $userNotification->setRelation('notification', $notification);

            $formatted = $this->formatUserNotification($userNotification);
            $unreadCount = $this->getUnreadCount($userId);

            try {
                NotificationCreated::dispatch($userId, $formatted, $unreadCount);
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast notification', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }

            return $userNotification;
        });
    }

    /**
     * Tạo thông báo và gán cho nhiều người dùng.
     *
     * @param  array<int, string>  $userIds
     */
    public function createForMultipleUsers(
        array $userIds,
        string $title,
        string $content,
        string $type = 'system',
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $actionUrl = null,
    ): void {
        $userIds = array_values(array_unique(array_filter($userIds)));
        if (empty($userIds)) {
            return;
        }

        DB::transaction(function () use ($userIds, $title, $content, $type, $referenceType, $referenceId, $actionUrl) {
            $notification = Notification::create([
                'title'          => $title,
                'content'        => $content,
                'type'           => $type,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'action_url'     => $actionUrl,
                'created_at'     => now(),
            ]);

            $now = now();
            $rows = array_map(fn ($uid) => [
                'user_id'         => $uid,
                'notification_id' => $notification->id,
                'is_read'         => false,
                'read_at'         => null,
                'created_at'      => $now,
            ], $userIds);

            UserNotification::insert($rows);

            foreach ($userIds as $uid) {
                $unreadCount = $this->getUnreadCount($uid);
                $fakeRecord = new UserNotification([
                    'user_id'         => $uid,
                    'notification_id' => $notification->id,
                    'is_read'         => false,
                    'created_at'      => $now,
                ]);
                $fakeRecord->setRelation('notification', $notification);

                try {
                    NotificationCreated::dispatch($uid, $this->formatUserNotification($fakeRecord), $unreadCount);
                } catch (\Throwable $e) {
                    // Ignore broadcast errors
                }
            }
        });
    }

    /**
     * Đếm số lượng thông báo chưa đọc của user.
     */
    public function getUnreadCount(string $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Định dạng dữ liệu trả về cho client.
     */
    public function formatUserNotification(UserNotification $userNotification): array
    {
        $notification = $userNotification->notification;

        $actionUrl = $notification?->action_url;
        if (! $actionUrl) {
            if ($notification?->reference_type === 'series' && $notification?->reference_id) {
                $actionUrl = "/story/{$notification->reference_id}";
            } elseif ($notification?->reference_type === 'community_post') {
                $actionUrl = $notification->reference_id ? "/community?post_id={$notification->reference_id}#post-{$notification->reference_id}" : "/community";
            } elseif ($notification?->reference_type === 'vip' || $notification?->reference_type === 'order') {
                $actionUrl = "/vip";
            }
        }

        return [
            'id'             => $userNotification->id,
            'is_read'        => (bool) $userNotification->is_read,
            'read_at'        => $userNotification->read_at?->toIso8601String(),
            'created_at'     => $userNotification->created_at?->toIso8601String(),
            'title'          => $notification?->title ?? '',
            'content'        => $notification?->content ?? '',
            'type'           => $notification?->type ?? 'system',
            'reference_type' => $notification?->reference_type,
            'reference_id'   => $notification?->reference_id,
            'action_url'     => $actionUrl,
        ];
    }

    /**
     * Gửi thông báo thanh toán VIP thành công.
     */
    public function sendVipPaymentSuccess(User $user, Plan $plan, Order $order): ?UserNotification
    {
        $title = '👑 Nâng cấp VIP thành công!';
        $content = "Chúc mừng bạn đã kích hoạt thành công gói VIP {$plan->name}. Hãy tận hưởng trọn vẹn toàn bộ kho truyện audio không giới hạn!";

        return $this->createForUser(
            userId: $user->id,
            title: $title,
            content: $content,
            type: 'vip',
            referenceType: 'vip',
            referenceId: $order->id,
            actionUrl: '/vip',
        );
    }

    /**
     * Gửi thông báo khi có người trả lời bình luận trong truyện.
     */
    public function sendStoryCommentReply(
        User $actor,
        string $recipientUserId,
        string $seriesId,
        string $seriesTitle,
        string $replySnippet,
        ?string $commentId = null,
    ): ?UserNotification {
        if ($actor->id === $recipientUserId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($replySnippet)), 0, 80, '...');
        $title = "💬 {$actorName} đã trả lời bình luận của bạn";
        $content = "Trong truyện \"{$seriesTitle}\": \"{$snippet}\"";
        $actionUrl = $commentId
            ? "/story/{$seriesId}?comment_id={$commentId}#comment-{$commentId}"
            : "/story/{$seriesId}";

        return $this->createForUser(
            userId: $recipientUserId,
            title: $title,
            content: $content,
            type: 'comment',
            referenceType: 'series',
            referenceId: $seriesId,
            actionUrl: $actionUrl,
        );
    }

    /**
     * Gửi thông báo khi có người thích/thả tim bình luận truyện.
     */
    public function sendStoryCommentLike(
        User $actor,
        string $recipientUserId,
        string $seriesId,
        string $seriesTitle,
        string $commentSnippet,
        string $commentId,
    ): ?UserNotification {
        if ($actor->id === $recipientUserId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($commentSnippet)), 0, 80, '...');
        $title = "❤️ {$actorName} đã thích bình luận của bạn";
        $content = "Trong truyện \"{$seriesTitle}\": \"{$snippet}\"";
        $actionUrl = "/story/{$seriesId}?comment_id={$commentId}#comment-{$commentId}";

        return $this->createForUser(
            userId: $recipientUserId,
            title: $title,
            content: $content,
            type: 'comment',
            referenceType: 'series',
            referenceId: $seriesId,
            actionUrl: $actionUrl,
        );
    }

    /**
     * Gửi thông báo khi có người thích bài viết cộng đồng của bạn.
     */
    public function sendCommunityPostLike(
        User $actor,
        string $postAuthorId,
        string $postId,
        string $postSnippet,
    ): ?UserNotification {
        if ($actor->id === $postAuthorId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($postSnippet)), 0, 80, '...');
        $title = "❤️ {$actorName} đã thích bài viết của bạn";
        $content = "\"{$snippet}\"";
        $actionUrl = "/community?post_id={$postId}#post-{$postId}";

        return $this->createForUser(
            userId: $postAuthorId,
            title: $title,
            content: $content,
            type: 'community',
            referenceType: 'community_post',
            referenceId: $postId,
            actionUrl: $actionUrl,
        );
    }

    /**
     * Gửi thông báo khi có người bình luận bài viết cộng đồng của bạn.
     */
    public function sendCommunityPostComment(
        User $actor,
        string $postAuthorId,
        string $postId,
        string $commentSnippet,
        ?string $commentId = null,
    ): ?UserNotification {
        if ($actor->id === $postAuthorId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($commentSnippet)), 0, 80, '...');
        $title = "💬 {$actorName} đã bình luận bài viết của bạn";
        $content = "\"{$snippet}\"";
        $actionUrl = $commentId
            ? "/community?post_id={$postId}&comment_id={$commentId}#comment-{$commentId}"
            : "/community?post_id={$postId}#post-{$postId}";

        return $this->createForUser(
            userId: $postAuthorId,
            title: $title,
            content: $content,
            type: 'community',
            referenceType: 'community_post',
            referenceId: $postId,
            actionUrl: $actionUrl,
        );
    }

    /**
     * Gửi thông báo khi có người trả lời bình luận trong bài viết cộng đồng.
     */
    public function sendCommunityCommentReply(
        User $actor,
        string $parentAuthorId,
        string $postId,
        string $replySnippet,
        ?string $commentId = null,
    ): ?UserNotification {
        if ($actor->id === $parentAuthorId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($replySnippet)), 0, 80, '...');
        $title = "💬 {$actorName} đã trả lời thảo luận của bạn";
        $content = "\"{$snippet}\"";
        $actionUrl = $commentId
            ? "/community?post_id={$postId}&comment_id={$commentId}#comment-{$commentId}"
            : "/community?post_id={$postId}#post-{$postId}";

        return $this->createForUser(
            userId: $parentAuthorId,
            title: $title,
            content: $content,
            type: 'community',
            referenceType: 'community_post',
            referenceId: $postId,
            actionUrl: $actionUrl,
        );
    }

    /**
     * Gửi thông báo khi có người thích bình luận trong bài viết cộng đồng.
     */
    public function sendCommunityCommentLike(
        User $actor,
        string $commentAuthorId,
        string $postId,
        string $commentSnippet,
        string $commentId,
    ): ?UserNotification {
        if ($actor->id === $commentAuthorId) {
            return null;
        }

        $actorName = $actor->username ?: ($actor->email ?: 'Một người dùng');
        $snippet = mb_strimwidth(trim(strip_tags($commentSnippet)), 0, 80, '...');
        $title = "❤️ {$actorName} đã thích bình luận của bạn";
        $content = "Trong cộng đồng: \"{$snippet}\"";
        $actionUrl = "/community?post_id={$postId}&comment_id={$commentId}#comment-{$commentId}";

        return $this->createForUser(
            userId: $commentAuthorId,
            title: $title,
            content: $content,
            type: 'community',
            referenceType: 'community_post',
            referenceId: $postId,
            actionUrl: $actionUrl,
        );
    }
}

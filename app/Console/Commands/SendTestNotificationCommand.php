<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Console\Command;

class SendTestNotificationCommand extends Command
{
    protected $signature = 'notification:test 
                            {user? : Email hoặc ID của user (mặc định lấy user đầu tiên)}
                            {--title= : Tiêu đề thông báo}
                            {--content= : Nội dung thông báo}
                            {--type=system : Loại thông báo (system, vip, comment, community, story)}';

    protected $description = 'Gửi một thông báo thử nghiệm tới người dùng để test tính năng Real-time';

    public function handle(NotificationService $notificationService): int
    {
        $userIdentifier = $this->argument('user');

        if ($userIdentifier) {
            $user = User::query()
                ->where('email', $userIdentifier)
                ->orWhere('username', $userIdentifier)
                ->orWhere('id', $userIdentifier)
                ->first();
        } else {
            $user = User::first();
        }

        if (! $user) {
            $this->error('Không tìm thấy người dùng nào trong cơ sở dữ liệu.');
            return 1;
        }

        $type = $this->option('type') ?: 'system';
        $title = $this->option('title') ?: match ($type) {
            'vip'       => '👑 Nâng cấp VIP thành công!',
            'like'      => '❤️ Có người vừa thích bình luận của bạn!',
            'comment'   => '💬 Có người vừa trả lời bình luận của bạn!',
            'community' => '👥 Bài viết của bạn nhận được phản hồi mới!',
            default     => '🔔 Thông báo thử nghiệm hệ thống',
        };

        $content = $this->option('content') ?: match ($type) {
            'vip'       => 'Tài khoản của bạn đã được nâng cấp lên gói VIP 1 Năm. Chúc bạn nghe truyện vui vẻ!',
            'like'      => 'Người dùng "Thính Giả 123" vừa thả tim cho bình luận của bạn.',
            'comment'   => 'Người dùng "Thính Giả 123" vừa phản hồi bình luận của bạn.',
            'community' => 'Có 1 thảo luận mới trong bài viết bạn đã đăng ở mục Cộng đồng.',
            default     => 'Đây là thông báo kiểm tra tính năng Realtime Push và Notification Dropdown.',
        };

        $this->info("Đang gửi thông báo tới user: {$user->username} ({$user->email} - ID: {$user->id})...");

        $series = \App\Models\Series::first();
        $actionUrl = match ($type) {
            'vip'       => '/vip',
            'community' => '/community',
            'comment', 'like' => $series ? "/story/{$series->id}" : '/library',
            default     => null,
        };

        $notif = $notificationService->createForUser(
            userId: $user->id,
            title: $title,
            content: $content,
            type: $type === 'like' ? 'comment' : $type,
            referenceType: $type === 'vip' ? 'vip' : ($type === 'community' ? 'community_post' : ($series ? 'series' : null)),
            referenceId: $series?->id,
            actionUrl: $actionUrl,
        );

        $unreadCount = $notificationService->getUnreadCount($user->id);

        $this->newLine();
        $this->info('✅ Gửi thông báo thành công!');
        $this->table(['Trường', 'Giá trị'], [
            ['ID Thông báo', $notif->id],
            ['User ID', $user->id],
            ['Tiêu đề', $title],
            ['Loại', $type],
            ['Số lượng chưa đọc hiện tại', $unreadCount],
        ]);

        return 0;
    }
}

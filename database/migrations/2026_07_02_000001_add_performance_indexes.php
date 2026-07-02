<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Index bổ sung để tăng tốc các truy vấn phổ biến.
 *
 * - community_posts(created_at DESC): danh sách bài viết không lọc tag
 * - community_post_comment_likes(comment_id, user_id): EXISTS liked_by_me theo comment
 * - community_post_likes(post_id, user_id): EXISTS liked_by_me theo post
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Danh sách bài viết "Tất cả" — ORDER BY created_at DESC không dùng được
        // index (tag, created_at DESC) vì không có WHERE tag.
        $this->safe(
            'CREATE INDEX IF NOT EXISTS idx_community_posts_created_at_desc
             ON community_posts (created_at DESC)'
        );

        // EXISTS liked_by_me cho comment: WHERE comment_id = ? AND user_id = ?
        // UNIQUE (user_id, comment_id) đã tồn tại nhưng (comment_id, user_id)
        // hiệu quả hơn khi comment_id là cột tương quan trong subquery.
        $this->safe(
            'CREATE INDEX IF NOT EXISTS idx_community_post_comment_likes_comment_user
             ON community_post_comment_likes (comment_id, user_id)'
        );

        // EXISTS liked_by_me cho post: WHERE post_id = ? AND user_id = ?
        // Tương tự, (post_id, user_id) hiệu quả hơn UNIQUE (user_id, post_id)
        // khi post_id là cột tương quan.
        $this->safe(
            'CREATE INDEX IF NOT EXISTS idx_community_post_likes_post_user
             ON community_post_likes (post_id, user_id)'
        );
    }

    public function down(): void
    {
        $this->safe('DROP INDEX IF EXISTS idx_community_posts_created_at_desc');
        $this->safe('DROP INDEX IF EXISTS idx_community_post_comment_likes_comment_user');
        $this->safe('DROP INDEX IF EXISTS idx_community_post_likes_post_user');
    }

    private function safe(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'already exists') || str_contains($msg, 'duplicate')) {
                return;
            }
            throw $e;
        }
    }
};

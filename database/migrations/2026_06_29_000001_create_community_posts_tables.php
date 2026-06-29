<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Bảng bài viết cộng đồng, like và bình luận.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->safeStatement('
            CREATE TABLE IF NOT EXISTS community_posts (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                content TEXT NOT NULL,
                tag VARCHAR(32) NOT NULL DEFAULT \'thaoluan\',
                series_id UUID NULL REFERENCES series(id) ON DELETE SET NULL,
                created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_posts_tag_created
             ON community_posts (tag, created_at DESC)'
        );

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_posts_user_id ON community_posts (user_id)'
        );

        $this->safeStatement('
            CREATE TABLE IF NOT EXISTS community_post_likes (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                post_id UUID NOT NULL REFERENCES community_posts(id) ON DELETE CASCADE,
                created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (user_id, post_id)
            )
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_post_likes_post_id ON community_post_likes (post_id)'
        );

        $this->safeStatement('
            CREATE TABLE IF NOT EXISTS community_post_comments (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                post_id UUID NOT NULL REFERENCES community_posts(id) ON DELETE CASCADE,
                content TEXT NOT NULL,
                created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_post_comments_post_created
             ON community_post_comments (post_id, created_at ASC)'
        );
    }

    public function down(): void
    {
        $this->safeStatement('DROP TABLE IF EXISTS community_post_comments');
        $this->safeStatement('DROP TABLE IF EXISTS community_post_likes');
        $this->safeStatement('DROP TABLE IF EXISTS community_posts');
    }

    private function safeStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            $message = strtolower($e->getMessage());

            if (
                str_contains($message, 'already exists')
                || str_contains($message, 'duplicate')
            ) {
                return;
            }

            throw $e;
        }
    }
};

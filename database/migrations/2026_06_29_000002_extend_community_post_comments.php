<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phản hồi lồng nhau và like bình luận cộng đồng.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->safeStatement('ALTER TABLE community_post_comments ADD COLUMN IF NOT EXISTS parent_id UUID NULL');

        $this->safeStatement('
            DO $$ BEGIN
                ALTER TABLE community_post_comments
                    ADD CONSTRAINT community_post_comments_parent_id_fkey
                    FOREIGN KEY (parent_id) REFERENCES community_post_comments(id) ON DELETE CASCADE;
            EXCEPTION
                WHEN duplicate_object THEN NULL;
            END $$
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_post_comments_parent_id ON community_post_comments (parent_id)'
        );

        $this->safeStatement('
            CREATE TABLE IF NOT EXISTS community_post_comment_likes (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                comment_id UUID NOT NULL REFERENCES community_post_comments(id) ON DELETE CASCADE,
                created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (user_id, comment_id)
            )
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_community_post_comment_likes_comment_id ON community_post_comment_likes (comment_id)'
        );
    }

    public function down(): void
    {
        $this->safeStatement('DROP TABLE IF EXISTS community_post_comment_likes');
        $this->safeStatement('DROP INDEX IF EXISTS idx_community_post_comments_parent_id');
        $this->safeStatement('ALTER TABLE community_post_comments DROP CONSTRAINT IF EXISTS community_post_comments_parent_id_fkey');
        $this->safeStatement('ALTER TABLE community_post_comments DROP COLUMN IF EXISTS parent_id');
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

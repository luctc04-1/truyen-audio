<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mở rộng đánh giá / bình luận:
 * - ratings.content, ratings.updated_at
 * - comments.parent_id, comments.is_pinned
 * - bảng comment_likes (like bình luận)
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // ─── ratings ─────────────────────────────────────────────────────
        $this->safeStatement('ALTER TABLE ratings ADD COLUMN IF NOT EXISTS content TEXT NULL');
        $this->safeStatement('ALTER TABLE ratings ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ NULL');

        // ─── comments ────────────────────────────────────────────────────
        $this->safeStatement('ALTER TABLE comments ADD COLUMN IF NOT EXISTS parent_id UUID NULL');
        $this->safeStatement(
            'ALTER TABLE comments ADD COLUMN IF NOT EXISTS is_pinned BOOLEAN NOT NULL DEFAULT false'
        );

        $this->safeStatement('
            DO $$ BEGIN
                ALTER TABLE comments
                    ADD CONSTRAINT comments_parent_id_fkey
                    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE;
            EXCEPTION
                WHEN duplicate_object THEN NULL;
            END $$
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_comments_series_pinned_created
             ON comments (series_id, is_pinned DESC, created_at DESC)
             WHERE episode_id IS NULL'
        );

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_comments_parent_id ON comments (parent_id)'
        );

        // ─── comment_likes ─────────────────────────────────────────────────
        $this->safeStatement('
            CREATE TABLE IF NOT EXISTS comment_likes (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                comment_id UUID NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
                created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (user_id, comment_id)
            )
        ');

        $this->safeStatement(
            'CREATE INDEX IF NOT EXISTS idx_comment_likes_comment_id ON comment_likes (comment_id)'
        );
    }

    public function down(): void
    {
        $this->safeStatement('DROP TABLE IF EXISTS comment_likes');

        $this->safeStatement('DROP INDEX IF EXISTS idx_comments_parent_id');
        $this->safeStatement('DROP INDEX IF EXISTS idx_comments_series_pinned_created');

        $this->safeStatement('
            ALTER TABLE comments DROP CONSTRAINT IF EXISTS comments_parent_id_fkey
        ');

        $this->safeStatement('ALTER TABLE comments DROP COLUMN IF EXISTS is_pinned');
        $this->safeStatement('ALTER TABLE comments DROP COLUMN IF EXISTS parent_id');

        $this->safeStatement('ALTER TABLE ratings DROP COLUMN IF EXISTS updated_at');
        $this->safeStatement('ALTER TABLE ratings DROP COLUMN IF EXISTS content');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows before adding constraint (keep latest per user+episode)
        DB::statement("
            DELETE FROM listening_histories
            WHERE id NOT IN (
                SELECT DISTINCT ON (user_id, episode_id) id
                FROM listening_histories
                ORDER BY user_id, episode_id, last_listened_at DESC
            )
        ");

        Schema::table('listening_histories', function (Blueprint $table) {
            $table->unique(['user_id', 'episode_id'], 'listening_histories_user_episode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('listening_histories', function (Blueprint $table) {
            $table->dropUnique('listening_histories_user_episode_unique');
        });
    }
};

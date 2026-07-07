<?php

namespace App\Modules\Admin\Support;

use App\Models\Comment;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CrawlJob;
use App\Models\Episode;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Rating;
use App\Models\Series;
use App\Models\UploadJob;
use App\Models\User;
use App\Modules\Series\Support\SeriesPresenter;

class AdminPresenter
{
    public static function series(Series $series): array
    {
        return [
            'id'              => $series->id,
            'title'           => $series->title,
            'slug'            => $series->slug,
            'cover_url'       => $series->cover_url ?: 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=120&q=80',
            'author'          => $series->author,
            'narrator'        => $series->narrator,
            'category'        => $series->category,
            'description'     => $series->description,
            'is_premium'      => (bool) $series->is_premium,
            'is_complete'     => (bool) $series->is_complete,
            'is_hot'          => (bool) $series->is_hot,
            'hot_order'       => (int) ($series->hot_order ?? 0),
            'total_episodes'  => (int) ($series->total_episodes ?: $series->episodes_count ?? 0),
            'total_listens'   => (int) ($series->total_listens ?? $series->listen_count ?? 0),
            'average_rating'  => round((float) ($series->average_rating ?? 0), 1),
            'status'          => $series->status,
            'published_at'    => optional($series->published_at)->toIso8601String(),
            'created_at'      => optional($series->created_at)->toIso8601String(),
        ];
    }

    public static function episode(Episode $episode): array
    {
        return [
            'id'               => $episode->id,
            'series_id'        => $episode->series_id,
            'series_title'     => $episode->series?->title,
            'title'            => $episode->title,
            'episode_number'   => $episode->episode_number,
            'duration_seconds' => (int) ($episode->duration_seconds ?? 0),
            'duration'         => SeriesPresenter::formatDuration($episode->duration_seconds),
            'is_premium'       => (bool) $episode->is_premium,
            'play_count'       => (int) ($episode->play_count ?? 0),
            'has_audio'        => (bool) ($episode->audio_path || $episode->storage_audio_url),
            'audio_path'       => $episode->audio_path,
            'transcript'       => $episode->transcript,
            'published_at'     => optional($episode->published_at)->toIso8601String(),
            'publish_at'       => optional($episode->publish_at)->toIso8601String(),
        ];
    }

    public static function user(User $user): array
    {
        $active = $user->activeSubscription();

        return [
            'id'          => $user->id,
            'username'    => $user->username,
            'email'       => $user->email,
            'avatar_url'  => $user->avatar_url,
            'is_admin'    => (bool) $user->is_admin,
            'is_banned'   => (bool) ($user->is_banned ?? false),
            'is_premium'  => $user->isPremium(),
            'devices'     => (int) ($user->devices_count ?? 0),
            'listens'     => (int) ($user->listening_histories_count ?? 0),
            'created_at'  => optional($user->created_at)->toIso8601String(),
            'subscription'=> $active ? [
                'plan_name' => $active->plan?->name,
                'end_at'    => optional($active->end_at)->toIso8601String(),
            ] : null,
        ];
    }

    public static function order(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'amount'         => (float) $order->amount,
            'status'         => $order->status,
            'payment_method' => $order->payment_method,
            'paid_at'        => optional($order->paid_at)->toIso8601String(),
            'created_at'     => optional($order->created_at)->toIso8601String(),
            'user'           => $order->user ? [
                'id'       => $order->user->id,
                'username' => $order->user->username,
                'email'    => $order->user->email,
            ] : null,
            'plan'           => $order->plan ? [
                'id'   => $order->plan->id,
                'code' => $order->plan->code,
                'name' => $order->plan->name,
            ] : null,
        ];
    }

    public static function comment(Comment $comment): array
    {
        return [
            'id'         => $comment->id,
            'content'    => $comment->content,
            'is_pinned'  => (bool) $comment->is_pinned,
            'created_at' => optional($comment->created_at)->toIso8601String(),
            'user'       => $comment->user ? [
                'id'       => $comment->user->id,
                'username' => $comment->user->username,
                'email'    => $comment->user->email,
            ] : null,
            'series'     => $comment->series ? [
                'id'    => $comment->series->id,
                'title' => $comment->series->title,
            ] : null,
            'episode'    => $comment->episode ? [
                'id'             => $comment->episode->id,
                'title'          => $comment->episode->title,
                'episode_number' => $comment->episode->episode_number,
            ] : null,
        ];
    }

    public static function plan(Plan $plan): array
    {
        return [
            'id'            => $plan->id,
            'code'          => $plan->code,
            'name'          => $plan->name,
            'price'         => (float) $plan->price,
            'duration_days' => (int) $plan->duration_days,
            'description'   => $plan->description,
            'is_active'     => (bool) $plan->is_active,
            'orders_count'  => (int) ($plan->orders_count ?? 0),
        ];
    }

    public static function communityPost(CommunityPost $post): array
    {
        return [
            'id'             => $post->id,
            'content'        => $post->content,
            'tag'            => $post->tag,
            'tag_label'      => CommunityPost::tagLabel($post->tag),
            'likes_count'    => (int) ($post->likes_count ?? 0),
            'comments_count' => (int) ($post->comments_count ?? 0),
            'created_at'     => optional($post->created_at)->toIso8601String(),
            'user'           => $post->user ? [
                'id'       => $post->user->id,
                'username' => $post->user->username,
                'email'    => $post->user->email,
            ] : null,
            'series'         => $post->series ? [
                'id'    => $post->series->id,
                'title' => $post->series->title,
            ] : null,
        ];
    }

    public static function communityComment(CommunityPostComment $comment): array
    {
        return [
            'id'         => $comment->id,
            'content'    => $comment->content,
            'created_at' => optional($comment->created_at)->toIso8601String(),
            'user'       => $comment->user ? [
                'id'       => $comment->user->id,
                'username' => $comment->user->username,
            ] : null,
            'post'       => $comment->post ? [
                'id'      => $comment->post->id,
                'content' => \Illuminate\Support\Str::limit($comment->post->content, 80),
            ] : null,
        ];
    }

    public static function rating(Rating $rating): array
    {
        return [
            'id'         => $rating->id,
            'rating'     => (int) $rating->rating,
            'content'    => $rating->content,
            'created_at' => optional($rating->created_at)->toIso8601String(),
            'user'       => $rating->user ? [
                'id'       => $rating->user->id,
                'username' => $rating->user->username,
            ] : null,
            'series'     => $rating->series ? [
                'id'    => $rating->series->id,
                'title' => $rating->series->title,
            ] : null,
        ];
    }

    public static function crawlJob(CrawlJob $job): array
    {
        return [
            'id'                => $job->id,
            'status'            => $job->status,
            'total_series'      => (int) ($job->total_series ?? 0),
            'total_episodes'    => (int) ($job->total_episodes ?? 0),
            'inserted_series'   => (int) ($job->inserted_series ?? 0),
            'inserted_episodes' => (int) ($job->inserted_episodes ?? 0),
            'error_message'     => $job->error_message,
            'started_at'        => optional($job->started_at)->toIso8601String(),
            'finished_at'       => optional($job->finished_at)->toIso8601String(),
        ];
    }

    public static function uploadJob(UploadJob $job): array
    {
        return [
            'id'            => $job->id,
            'status'        => $job->status,
            'retry_count'   => (int) ($job->retry_count ?? 0),
            'error_message' => $job->error_message,
            'created_at'    => optional($job->created_at)->toIso8601String(),
            'started_at'    => optional($job->started_at)->toIso8601String(),
            'finished_at'   => optional($job->finished_at)->toIso8601String(),
            'episode'       => $job->episode ? [
                'id'           => $job->episode->id,
                'title'        => $job->episode->title,
                'series_title' => $job->episode->series?->title,
            ] : null,
        ];
    }
}

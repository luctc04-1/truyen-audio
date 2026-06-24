<?php

namespace App\Modules\Series\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Series;
use App\Models\User;
use App\Modules\Series\Support\UserPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentController extends BaseController
{
    public function index(Request $request, string $seriesId): JsonResponse
    {
        if (! Series::whereKey($seriesId)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        $query = Comment::query()
            ->forSeries($seriesId)
            ->topLevel()
            ->with($this->commentEagerLoads($user))
            ->withCount('likes')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($user) {
            $query->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $paginator = $query->paginate($perPage);

        $totalAll = Comment::query()
            ->forSeries($seriesId)
            ->count();

        return $this->success([
            'items' => $paginator->getCollection()
                ->map(fn (Comment $comment) => $this->formatComment($comment))
                ->values(),
            'pagination' => $this->paginationMeta($paginator, [
                'total_all' => $totalAll,
            ]),
        ], 'Danh sách bình luận');
    }

    public function store(Request $request, string $seriesId): JsonResponse
    {
        if (! Series::whereKey($seriesId)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $data = $request->validate([
            'content'   => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|uuid',
        ]);

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parent = Comment::query()->find($parentId);

            if (
                ! $parent
                || $parent->series_id !== $seriesId
                || $parent->episode_id !== null
                || $parent->parent_id !== null
            ) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Bình luận gốc không hợp lệ.'],
                ]);
            }
        }

        $user = $request->user();

        $comment = Comment::create([
            'user_id'    => $user->id,
            'series_id'  => $seriesId,
            'episode_id' => null,
            'parent_id'  => $parentId,
            'content'    => trim($data['content']),
        ]);

        $this->hydrateComment($comment, $user, likedByMe: false);

        return $this->success(
            $this->formatComment($comment, includeReplies: ! $parentId),
            'Gửi bình luận thành công',
            201
        );
    }

    public function toggleLike(Request $request, string $id): JsonResponse
    {
        $comment = Comment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        $existing = CommentLike::query()
            ->where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'user_id'    => $user->id,
                'comment_id' => $comment->id,
            ]);
            $liked = true;
        }

        return $this->success([
            'comment_id'  => $comment->id,
            'like_count'  => $comment->likes()->count(),
            'liked_by_me' => $liked,
        ], $liked ? 'Đã thích bình luận' : 'Đã bỏ thích');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $comment = Comment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            return $this->error('Bạn chỉ có thể sửa bình luận của mình', 403);
        }

        $data = $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update([
            'content'    => trim($data['content']),
            'updated_at' => now(),
        ]);

        $this->hydrateComment($comment, $user);

        return $this->success(
            $this->formatComment($comment, includeReplies: $comment->parent_id === null),
            'Cập nhật bình luận thành công'
        );
    }

    public function pin(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_admin) {
            return $this->error('Chỉ admin mới được ghim bình luận', 403);
        }

        $comment = Comment::query()
            ->whereNull('parent_id')
            ->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $data = $request->validate([
            'is_pinned' => 'required|boolean',
        ]);

        $comment->update(['is_pinned' => $data['is_pinned']]);
        $this->hydrateComment($comment, $user);

        return $this->success(
            $this->formatComment($comment),
            $data['is_pinned'] ? 'Đã ghim bình luận' : 'Đã bỏ ghim bình luận'
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $comment = Comment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        if ($comment->user_id !== $user->id && ! $user->is_admin) {
            return $this->error('Bạn không có quyền xóa bình luận này', 403);
        }

        $comment->delete();

        return $this->success(null, 'Đã xóa bình luận');
    }

    private function hydrateComment(Comment $comment, User $user, ?bool $likedByMe = null): void
    {
        $comment->load($this->commentEagerLoads($user));
        $comment->loadCount('likes');

        if ($likedByMe !== null) {
            $comment->setAttribute('liked_by_me', $likedByMe);

            return;
        }

        $comment->loadExists([
            'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
        ]);
    }

    /** @return array<string, mixed> */
    private function commentEagerLoads(?User $user): array
    {
        return [
            'user',
            'replies' => function ($query) use ($user) {
                $query->with('user')->withCount('likes')->orderBy('created_at');

                if ($user) {
                    $query->withExists([
                        'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
                    ]);
                }
            },
        ];
    }

    private function formatComment(Comment $comment, bool $includeReplies = true): array
    {
        $comment->loadMissing('user');

        $data = [
            'id'          => $comment->id,
            'content'     => $comment->content,
            'is_pinned'   => $comment->is_pinned,
            'like_count'  => (int) $comment->likes_count,
            'liked_by_me' => (bool) ($comment->liked_by_me ?? false),
            'created_at'  => $comment->created_at?->toIso8601String(),
            'updated_at'  => $comment->updated_at?->toIso8601String(),
            'is_edited'   => $comment->updated_at !== null,
            'user'        => $comment->user
                ? UserPresenter::forPublic($comment->user)
                : null,
            'replies'     => [],
        ];

        if ($includeReplies && $comment->relationLoaded('replies')) {
            $data['replies'] = $comment->replies
                ->map(fn (Comment $reply) => $this->formatComment($reply, includeReplies: false))
                ->values()
                ->all();
        }

        return $data;
    }
}

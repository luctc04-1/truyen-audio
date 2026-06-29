<?php

namespace App\Modules\Community\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostCommentLike;
use App\Models\CommunityPostLike;
use App\Models\User;
use App\Modules\Series\Support\UserPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommunityController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tag = $request->input('tag');
        $perPage = min((int) $request->input('per_page', 15), 50);

        $query = CommunityPost::query()
            ->with(['user', 'series:id,title,slug,cover_url'])
            ->withCount(['likes', 'comments'])
            ->forTag($tag)
            ->orderByDesc('created_at');

        if ($user) {
            $query->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $paginator = $query->paginate($perPage);

        return $this->success([
            'items' => $paginator->getCollection()
                ->map(fn (CommunityPost $post) => $this->formatPost($post))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
            'tags' => CommunityPost::TAGS,
        ], 'Danh sách bài viết');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content'   => 'required|string|min:1|max:5000',
            'tag'       => ['required', 'string', Rule::in(array_keys(CommunityPost::TAGS))],
            'series_id' => 'nullable|uuid|exists:series,id',
        ]);

        $user = $request->user();

        $post = CommunityPost::create([
            'user_id'   => $user->id,
            'content'   => trim($data['content']),
            'tag'       => $data['tag'],
            'series_id' => $data['series_id'] ?? null,
        ]);

        $this->hydratePost($post, $user, likedByMe: false);

        return $this->success(
            $this->formatPost($post),
            'Đăng bài thành công',
            201
        );
    }

    public function toggleLike(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $user = $request->user();

        $existing = CommunityPostLike::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CommunityPostLike::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $liked = true;
        }

        return $this->success([
            'post_id'     => $post->id,
            'like_count'  => $post->likes()->count(),
            'liked_by_me' => $liked,
        ], $liked ? 'Đã thích bài viết' : 'Đã bỏ thích');
    }

    public function comments(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        $query = CommunityPostComment::query()
            ->where('post_id', $post->id)
            ->topLevel()
            ->with($this->commentEagerLoads($user))
            ->withCount('likes')
            ->orderBy('created_at');

        if ($user) {
            $query->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $paginator = $query->paginate($perPage);

        return $this->success([
            'items' => $paginator->getCollection()
                ->map(fn (CommunityPostComment $comment) => $this->formatComment($comment))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ], 'Danh sách bình luận');
    }

    public function storeComment(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $data = $request->validate([
            'content'   => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|uuid',
        ]);

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parent = CommunityPostComment::query()->find($parentId);

            if (
                ! $parent
                || $parent->post_id !== $post->id
                || $parent->parent_id !== null
            ) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Bình luận gốc không hợp lệ.'],
                ]);
            }
        }

        $user = $request->user();

        $comment = CommunityPostComment::create([
            'user_id'   => $user->id,
            'post_id'   => $post->id,
            'parent_id' => $parentId,
            'content'   => trim($data['content']),
        ]);

        $this->hydrateComment($comment, $user, likedByMe: false);

        return $this->success(
            $this->formatComment($comment, includeReplies: ! $parentId),
            'Gửi bình luận thành công',
            201
        );
    }

    public function toggleCommentLike(Request $request, string $id): JsonResponse
    {
        $comment = CommunityPostComment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        $existing = CommunityPostCommentLike::query()
            ->where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CommunityPostCommentLike::create([
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

    private function hydratePost(CommunityPost $post, User $user, ?bool $likedByMe = null): void
    {
        $post->load(['user', 'series:id,title,slug,cover_url']);
        $post->loadCount(['likes', 'comments']);

        if ($likedByMe !== null) {
            $post->setAttribute('liked_by_me', $likedByMe);

            return;
        }

        $post->loadExists([
            'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
        ]);
    }

    private function hydrateComment(CommunityPostComment $comment, User $user, ?bool $likedByMe = null): void
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

    private function formatPost(CommunityPost $post): array
    {
        $post->loadMissing(['user', 'series']);

        $series = $post->series;

        return [
            'id'            => $post->id,
            'content'       => $post->content,
            'tag'           => $post->tag,
            'tag_label'     => CommunityPost::tagLabel($post->tag),
            'like_count'    => (int) ($post->likes_count ?? 0),
            'comment_count' => (int) ($post->comments_count ?? 0),
            'liked_by_me'   => (bool) ($post->liked_by_me ?? false),
            'created_at'    => $post->created_at?->toIso8601String(),
            'user'          => $post->user
                ? UserPresenter::forPublic($post->user)
                : null,
            'series'        => $series ? [
                'id'        => $series->id,
                'title'     => $series->title,
                'slug'      => $series->slug,
                'cover_url' => $series->cover_url,
            ] : null,
        ];
    }

    private function formatComment(CommunityPostComment $comment, bool $includeReplies = true): array
    {
        $comment->loadMissing('user');

        $data = [
            'id'          => $comment->id,
            'content'     => $comment->content,
            'like_count'  => (int) ($comment->likes_count ?? 0),
            'liked_by_me' => (bool) ($comment->liked_by_me ?? false),
            'created_at'  => $comment->created_at?->toIso8601String(),
            'user'        => $comment->user
                ? UserPresenter::forPublic($comment->user)
                : null,
            'replies'     => [],
        ];

        if ($includeReplies && $comment->relationLoaded('replies')) {
            $data['replies'] = $comment->replies
                ->map(fn (CommunityPostComment $reply) => $this->formatComment($reply, includeReplies: false))
                ->values()
                ->all();
        }

        return $data;
    }
}

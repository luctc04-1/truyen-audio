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
        $post = CommunityPost::query()->withCount('likes')->find($id);

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
            'like_count'  => $liked ? $post->likes_count + 1 : max(0, $post->likes_count - 1),
            'liked_by_me' => $liked,
        ], $liked ? 'Đã thích bài viết' : 'Đã bỏ thích');
    }

    public function comments(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $user    = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50);
        $page    = max(1, (int) $request->input('page', 1));

        // Fetch all comments for the post in ONE query (flat), build tree in PHP
        $all = CommunityPostComment::query()
            ->where('post_id', $post->id)
            ->with('user')
            ->withCount('likes')
            ->when($user, fn ($q) => $q->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', $user->id),
            ]))
            ->orderBy('created_at')
            ->get();

        // Build tree in O(n) using an id→comment map
        $map = [];
        foreach ($all as $comment) {
            $comment->setRelation('replies', collect());
            $map[$comment->id] = $comment;
        }

        $topLevel = collect();
        foreach ($all as $comment) {
            if ($comment->parent_id === null) {
                $topLevel->push($comment);
            } else {
                $parent = $map[$comment->parent_id] ?? null;
                if ($parent) {
                    $parent->replies->push($comment);
                }
            }
        }

        // Paginate top-level in PHP
        $total = $topLevel->count();
        $items = $topLevel->forPage($page, $perPage);

        return $this->success([
            'items' => $items
                ->map(fn (CommunityPostComment $comment) => $this->formatComment($comment))
                ->values(),
            'pagination' => [
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
                'per_page'     => $perPage,
                'total'        => $total,
            ],
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

        // Load only what's needed: a new comment always has 0 likes and no replies
        $comment->load('user');
        $comment->likes_count = 0;
        $comment->setAttribute('liked_by_me', false);
        $comment->setRelation('replies', collect());

        return $this->success(
            $this->formatComment($comment, includeReplies: ! $parentId),
            'Gửi bình luận thành công',
            201
        );
    }

    public function updatePost(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $user = $request->user();

        if ($post->user_id !== $user->id) {
            return $this->error('Bạn không có quyền chỉnh sửa bài viết này', 403);
        }

        $data = $request->validate([
            'content'   => 'required|string|min:1|max:5000',
            'tag'       => ['required', 'string', Rule::in(array_keys(CommunityPost::TAGS))],
            'series_id' => 'nullable|uuid|exists:series,id',
        ]);

        $post->update([
            'content'   => trim($data['content']),
            'tag'       => $data['tag'],
            'series_id' => $data['series_id'] ?? null,
        ]);

        return $this->success(
            $this->formatPost($post),
            'Chỉnh sửa bài viết thành công'
        );
    }

    public function destroyPost(Request $request, string $id): JsonResponse
    {
        $post = CommunityPost::query()->find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $user = $request->user();

        if ($post->user_id !== $user->id) {
            return $this->error('Bạn không có quyền xóa bài viết này', 403);
        }

        $post->delete();

        return $this->success(null, 'Đã xóa bài viết');
    }

    public function updateComment(Request $request, string $id): JsonResponse
    {
        $comment = CommunityPostComment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            return $this->error('Bạn không có quyền chỉnh sửa bình luận này', 403);
        }

        $data = $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update(['content' => trim($data['content'])]);

        return $this->success([
            'id'      => $comment->id,
            'content' => $comment->content,
        ], 'Chỉnh sửa bình luận thành công');
    }

    public function destroyComment(Request $request, string $id): JsonResponse
    {
        $comment = CommunityPostComment::query()->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            return $this->error('Bạn không có quyền xóa bình luận này', 403);
        }

        $isTopLevel = $comment->parent_id === null;
        $comment->delete();

        return $this->success([
            'is_top_level' => $isTopLevel,
        ], 'Đã xóa bình luận');
    }

    public function toggleCommentLike(Request $request, string $id): JsonResponse
    {
        $comment = CommunityPostComment::query()->withCount('likes')->find($id);

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
            'like_count'  => $liked ? $comment->likes_count + 1 : max(0, $comment->likes_count - 1),
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
                ->map(fn (CommunityPostComment $reply) => $this->formatComment($reply, includeReplies: true))
                ->values()
                ->all();
        }

        return $data;
    }
}

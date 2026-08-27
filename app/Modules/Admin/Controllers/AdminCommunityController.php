<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Comment;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCommunityController extends BaseController
{
    /**
     * Thống kê tổng quan cộng đồng & kiểm duyệt.
     */
    public function stats(): JsonResponse
    {
        $totalPosts = CommunityPost::count();
        $totalSeriesComments = Comment::count();
        $totalPostComments = CommunityPostComment::count();

        // Thống kê bài viết theo thẻ tag
        $postsByTag = CommunityPost::select('tag', DB::raw('count(*) as count'))
            ->groupBy('tag')
            ->pluck('count', 'tag')
            ->toArray();

        // Top 5 người dùng tích cực đăng bài/bình luận nhất
        $topUsers = CommunityPost::select('user_id', DB::raw('count(*) as post_count'))
            ->with(['user:id,username,email,avatar_url,is_admin'])
            ->groupBy('user_id')
            ->orderByDesc('post_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'user' => $item->user,
                    'post_count' => $item->post_count,
                ];
            });

        return $this->success([
            'total_posts' => $totalPosts,
            'total_series_comments' => $totalSeriesComments,
            'total_post_comments' => $totalPostComments,
            'posts_by_tag' => $postsByTag,
            'top_users' => $topUsers,
        ]);
    }

    /**
     * Danh sách bài viết cộng đồng (kèm lọc tag, tìm kiếm, phân trang).
     */
    public function posts(Request $request): JsonResponse
    {
        $query = CommunityPost::query()
            ->with([
                'user:id,username,email,avatar_url,is_admin,created_at',
                'series:id,title,slug,cover_url,category',
            ])
            ->withCount(['likes', 'comments']);

        if ($request->filled('tag') && $request->input('tag') !== 'all') {
            $query->where('tag', $request->input('tag'));
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('content', 'ilike', "%{$keyword}%")
                  ->orWhereHas('user', function ($uq) use ($keyword) {
                      $uq->where('username', 'ilike', "%{$keyword}%")
                        ->orWhere('email', 'ilike', "%{$keyword}%");
                  })
                  ->orWhereHas('series', function ($sq) use ($keyword) {
                      $sq->where('title', 'ilike', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
            'tags' => CommunityPost::TAGS,
        ]);
    }

    /**
     * Chi tiết 1 bài viết cộng đồng kèm danh sách bình luận con.
     */
    public function postDetail(string $id): JsonResponse
    {
        $post = CommunityPost::with([
            'user:id,username,email,avatar_url,is_admin,created_at',
            'series:id,title,slug,cover_url,category',
            'likes.user:id,username,avatar_url',
            'comments' => function ($q) {
                $q->with('user:id,username,email,avatar_url,is_admin')->orderBy('created_at', 'asc');
            },
        ])
        ->withCount(['likes', 'comments'])
        ->find($id);

        if (!$post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        return $this->success($post);
    }

    /**
     * Xóa 1 bài viết cộng đồng.
     */
    public function destroyPost(string $id): JsonResponse
    {
        $post = CommunityPost::find($id);

        if (!$post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        $post->delete();

        return $this->success(null, 'Đã xóa bài viết cộng đồng');
    }

    /**
     * Xóa hàng loạt bài viết cộng đồng.
     */
    public function batchDestroyPosts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid',
        ]);

        $deletedCount = CommunityPost::whereIn('id', $validated['ids'])->delete();

        return $this->success([
            'deleted_count' => $deletedCount,
        ], "Đã xóa thành công {$deletedCount} bài viết");
    }

    /**
     * Danh sách bình luận (bình luận truyện hoặc bình luận bài viết cộng đồng).
     */
    public function comments(Request $request): JsonResponse
    {
        $type = $request->input('type', 'series'); // 'series' or 'community'

        if ($type === 'community') {
            $query = CommunityPostComment::with([
                'user:id,username,email,avatar_url,is_admin',
                'post:id,content,tag,created_at',
            ]);
        } else {
            $query = Comment::with([
                'user:id,username,email,avatar_url,is_admin',
                'series:id,title,slug,cover_url',
            ]);
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('content', 'ilike', "%{$keyword}%")
                  ->orWhereHas('user', function ($uq) use ($keyword) {
                      $uq->where('username', 'ilike', "%{$keyword}%")
                        ->orWhere('email', 'ilike', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('series_id') && $type === 'series') {
            $query->where('series_id', $request->input('series_id'));
        }

        if ($request->filled('post_id') && $type === 'community') {
            $query->where('post_id', $request->input('post_id'));
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    /**
     * Xóa 1 bình luận.
     */
    public function destroyComment(Request $request, string $id): JsonResponse
    {
        $type = $request->input('type', 'series');

        if ($type === 'community') {
            $comment = CommunityPostComment::find($id);
        } else {
            $comment = Comment::find($id);
        }

        if (!$comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $comment->delete();

        return $this->success(null, 'Đã xóa bình luận thành công');
    }

    /**
     * Xóa hàng loạt bình luận.
     */
    public function batchDestroyComments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid',
            'type' => 'nullable|string|in:series,community',
        ]);

        $type = $validated['type'] ?? 'series';

        if ($type === 'community') {
            $deletedCount = CommunityPostComment::whereIn('id', $validated['ids'])->delete();
        } else {
            $deletedCount = Comment::whereIn('id', $validated['ids'])->delete();
        }

        return $this->success([
            'deleted_count' => $deletedCount,
        ], "Đã xóa thành công {$deletedCount} bình luận");
    }
}

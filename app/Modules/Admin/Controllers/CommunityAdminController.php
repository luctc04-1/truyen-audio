<?php

namespace App\Modules\Admin\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityAdminController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = CommunityPost::query()
            ->with(['user:id,username,email', 'series:id,title'])
            ->withCount(['likes', 'comments']);

        if ($tag = trim((string) $request->input('tag'))) {
            $query->where('tag', $tag);
        }

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('content', 'ilike', "%{$escaped}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'ilike', "%{$escaped}%"));
            });
        }

        $perPage   = min((int) $request->input('per_page', 15), 50);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (CommunityPost $post) => AdminPresenter::communityPost($post))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
            'tags'       => CommunityPost::TAGS,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $post = CommunityPost::find($id);

        if (! $post) {
            return $this->error('Không tìm thấy bài viết', 404);
        }

        CommunityPostComment::where('post_id', $post->id)->delete();
        $post->likes()->delete();
        $post->delete();

        return $this->success(null, 'Đã xóa bài viết');
    }

    public function comments(Request $request): JsonResponse
    {
        $query = CommunityPostComment::query()
            ->with(['user:id,username,email', 'post:id,content']);

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('content', 'ilike', "%{$escaped}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'ilike', "%{$escaped}%"));
            });
        }

        if ($postId = $request->input('post_id')) {
            $query->where('post_id', $postId);
        }

        $perPage   = min((int) $request->input('per_page', 15), 50);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (CommunityPostComment $comment) => AdminPresenter::communityComment($comment))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function destroyComment(string $id): JsonResponse
    {
        $comment = CommunityPostComment::find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $comment->delete();

        return $this->success(null, 'Đã xóa bình luận cộng đồng');
    }
}

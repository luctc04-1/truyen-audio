<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Comment;
use App\Models\CommunityPostComment;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCommentController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->input('type', 'series'); // 'series' or 'community'

        if ($type === 'community') {
            $query = CommunityPostComment::with(['user:id,username,email,avatar_url', 'post:id,title']);
        } else {
            $query = Comment::with(['user:id,username,email,avatar_url', 'series:id,title,slug']);
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where('content', 'ilike', "%{$keyword}%");
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
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

        return $this->success(null, 'Đã xóa bình luận');
    }
}

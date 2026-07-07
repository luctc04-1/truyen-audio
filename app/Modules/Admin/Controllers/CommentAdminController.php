<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Comment;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentAdminController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::query()
            ->with([
                'user:id,username,email',
                'series:id,title',
                'episode:id,title,episode_number',
            ]);

        if (! $request->boolean('include_replies')) {
            $query->topLevel();
        }

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('content', 'ilike', "%{$escaped}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'ilike', "%{$escaped}%"));
            });
        }

        $perPage   = min((int) $request->input('per_page', 20), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Comment $comment) => AdminPresenter::comment($comment))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $comment->delete();

        return $this->success(null, 'Đã xóa bình luận');
    }

    public function pin(Request $request, string $id): JsonResponse
    {
        $comment = Comment::with([
            'user:id,username,email',
            'series:id,title',
            'episode:id,title,episode_number',
        ])->find($id);

        if (! $comment) {
            return $this->error('Không tìm thấy bình luận', 404);
        }

        $data = $request->validate([
            'is_pinned' => 'required|boolean',
        ]);

        $comment->is_pinned = $data['is_pinned'];
        $comment->save();

        return $this->success(
            AdminPresenter::comment($comment),
            $data['is_pinned'] ? 'Đã ghim bình luận' : 'Đã bỏ ghim bình luận'
        );
    }
}

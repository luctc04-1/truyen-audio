/**
 * Tìm bình luận (hoặc reply) trong danh sách lồng nhau.
 */
export const findNestedComment = (list, id) => {
    for (const item of list || []) {
        if (item.id === id) return item;
        const reply = (item.replies || []).find((r) => r.id === id);
        if (reply) return reply;
    }
    return null;
};

/**
 * Toggle like optimistic — cập nhật UI trước, rollback khi API lỗi.
 */
export const optimisticToggleLike = async ({
    target,
    id,
    pendingSet,
    apiCall,
    onError,
}) => {
    if (!target || pendingSet.has(id)) return;

    const snapshot = {
        liked_by_me: target.liked_by_me,
        like_count: target.like_count,
    };

    target.liked_by_me = !target.liked_by_me;
    target.like_count = Math.max(
        0,
        (target.like_count ?? 0) + (target.liked_by_me ? 1 : -1),
    );
    pendingSet.add(id);

    try {
        const result = await apiCall();
        target.like_count = result.like_count;
        target.liked_by_me = result.liked_by_me;
    } catch (error) {
        target.liked_by_me = snapshot.liked_by_me;
        target.like_count = snapshot.like_count;
        onError?.(error);
    } finally {
        pendingSet.delete(id);
    }
};

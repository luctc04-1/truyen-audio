import ApiService from './ApiService';
import { extractApiPayload } from '@/utils/helpers';

class ReviewService {
    getRatings(seriesId, params = {}) {
        return ApiService.get(`/series/${seriesId}/ratings`, params).then(extractApiPayload);
    }

    submitRating(seriesId, { rating, content = null }) {
        return ApiService.post(`/series/${seriesId}/ratings`, { rating, content }).then(extractApiPayload);
    }

    getComments(seriesId, params = {}) {
        return ApiService.get(`/series/${seriesId}/comments`, params).then(extractApiPayload);
    }

    submitComment(seriesId, content, parentId = null) {
        const payload = { content };
        if (parentId) payload.parent_id = parentId;
        return ApiService.post(`/series/${seriesId}/comments`, payload).then(extractApiPayload);
    }

    toggleLike(commentId) {
        return ApiService.post(`/comments/${commentId}/like`).then(extractApiPayload);
    }

    pinComment(commentId, isPinned) {
        return ApiService.patch(`/comments/${commentId}/pin`, { is_pinned: isPinned }).then(extractApiPayload);
    }

    updateComment(commentId, content) {
        return ApiService.patch(`/comments/${commentId}`, { content }).then(extractApiPayload);
    }

    deleteComment(commentId) {
        return ApiService.delete(`/comments/${commentId}`).then(extractApiPayload);
    }
}

export default new ReviewService();

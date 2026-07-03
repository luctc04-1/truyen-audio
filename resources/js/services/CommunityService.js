import ApiService from './ApiService';
import { extractApiPayload } from '@/utils/helpers';

class CommunityService {
    getPosts(params = {}) {
        return ApiService.get('/community/posts', params).then(extractApiPayload);
    }

    createPost({ content, tag, series_id = null }) {
        const payload = { content, tag };
        if (series_id) payload.series_id = series_id;
        return ApiService.post('/community/posts', payload).then(extractApiPayload);
    }

    updatePost(postId, { content, tag, series_id = null }) {
        const payload = { content, tag };
        if (series_id) payload.series_id = series_id;
        return ApiService.patch(`/community/posts/${postId}`, payload).then(extractApiPayload);
    }

    deletePost(postId) {
        return ApiService.delete(`/community/posts/${postId}`);
    }

    updateComment(commentId, content) {
        return ApiService.patch(`/community/community-comments/${commentId}`, { content }).then(extractApiPayload);
    }

    deleteComment(commentId) {
        return ApiService.delete(`/community/community-comments/${commentId}`);
    }

    toggleLike(postId) {
        return ApiService.post(`/community/posts/${postId}/like`).then(extractApiPayload);
    }

    getComments(postId, params = {}) {
        return ApiService.get(`/community/posts/${postId}/comments`, params).then(extractApiPayload);
    }

    submitComment(postId, content, parentId = null) {
        const payload = { content };
        if (parentId) payload.parent_id = parentId;
        return ApiService.post(`/community/posts/${postId}/comments`, payload).then(extractApiPayload);
    }

    toggleCommentLike(commentId) {
        return ApiService.post(`/community/community-comments/${commentId}/like`).then(extractApiPayload);
    }
}

export default new CommunityService();

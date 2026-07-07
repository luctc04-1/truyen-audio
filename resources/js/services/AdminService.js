import ApiService from './ApiService';

class AdminService {
    getDashboard() {
        return ApiService.get('/admin/dashboard');
    }

    getSeries(params = {}) {
        return ApiService.get('/admin/series', params);
    }

    getSeriesById(id) {
        return ApiService.get(`/admin/series/${id}`);
    }

    createSeries(data) {
        return ApiService.post('/admin/series', data);
    }

    updateSeries(id, data) {
        return ApiService.patch(`/admin/series/${id}`, data);
    }

    uploadSeriesCover(id, file) {
        const form = new FormData();
        form.append('cover', file);
        return ApiService.postForm(`/admin/series/${id}/cover`, form);
    }

    deleteSeries(id) {
        return ApiService.delete(`/admin/series/${id}`);
    }

    getEpisodes(params = {}) {
        return ApiService.get('/admin/episodes', params);
    }

    createEpisode(data) {
        return ApiService.post('/admin/episodes', data);
    }

    updateEpisode(id, data) {
        return ApiService.patch(`/admin/episodes/${id}`, data);
    }

    bulkEpisodes(action, ids) {
        return ApiService.post('/admin/episodes/bulk', { action, ids });
    }

    uploadEpisodeAudio(id, file) {
        const form = new FormData();
        form.append('audio', file);
        return ApiService.postForm(`/admin/episodes/${id}/audio`, form);
    }

    deleteEpisode(id) {
        return ApiService.delete(`/admin/episodes/${id}`);
    }

    getCategories() {
        return ApiService.get('/admin/categories');
    }

    renameCategory(from, to) {
        return ApiService.post('/admin/categories/rename', { from, to });
    }

    getUsers(params = {}) {
        return ApiService.get('/admin/users', params);
    }

    updateUser(id, data) {
        return ApiService.patch(`/admin/users/${id}`, data);
    }

    grantUserVip(id, planId) {
        return ApiService.post(`/admin/users/${id}/grant-vip`, { plan_id: planId });
    }

    revokeUserVip(id) {
        return ApiService.post(`/admin/users/${id}/revoke-vip`);
    }

    getOrders(params = {}) {
        return ApiService.get('/admin/orders', params);
    }

    updateOrder(id, data) {
        return ApiService.patch(`/admin/orders/${id}`, data);
    }

    getPlans() {
        return ApiService.get('/admin/plans');
    }

    createPlan(data) {
        return ApiService.post('/admin/plans', data);
    }

    updatePlan(id, data) {
        return ApiService.patch(`/admin/plans/${id}`, data);
    }

    deletePlan(id) {
        return ApiService.delete(`/admin/plans/${id}`);
    }

    getComments(params = {}) {
        return ApiService.get('/admin/comments', params);
    }

    deleteComment(id) {
        return ApiService.delete(`/admin/comments/${id}`);
    }

    pinComment(id, isPinned) {
        return ApiService.patch(`/admin/comments/${id}/pin`, { is_pinned: isPinned });
    }

    getRatings(params = {}) {
        return ApiService.get('/admin/ratings', params);
    }

    deleteRating(id) {
        return ApiService.delete(`/admin/ratings/${id}`);
    }

    getCommunityPosts(params = {}) {
        return ApiService.get('/admin/community', params);
    }

    getCommunityComments(params = {}) {
        return ApiService.get('/admin/community/comments', params);
    }

    deleteCommunityPost(id) {
        return ApiService.delete(`/admin/community/${id}`);
    }

    deleteCommunityComment(id) {
        return ApiService.delete(`/admin/community/comments/${id}`);
    }

    getSettings() {
        return ApiService.get('/admin/settings');
    }

    updateSettings(data) {
        return ApiService.patch('/admin/settings', data);
    }

    getJobs() {
        return ApiService.get('/admin/jobs');
    }

    getSyncStatus() {
        return ApiService.get('/admin/sync/status');
    }

    syncAll() {
        return ApiService.post('/admin/sync/all');
    }

    syncSeries() {
        return ApiService.post('/admin/sync/series');
    }

    syncEpisodes(seriesId = null) {
        return ApiService.post('/admin/sync/episodes', seriesId ? { series_id: seriesId } : {});
    }
}

export default new AdminService();

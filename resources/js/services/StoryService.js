import ApiService from './ApiService';

/**
 * Story / Series Service
 * Gọi API series từ database
 */
class StoryService {
    async getStories(params = {}) {
        return ApiService.get('/series', params);
    }

    async getStory(id) {
        return ApiService.get(`/series/${id}`);
    }

    async getEpisodes(seriesId) {
        return ApiService.get(`/series/${seriesId}/episodes`);
    }

    async getEpisode(id) {
        return ApiService.get(`/episodes/${id}`);
    }

    async getHotStories(limit = 10) {
        return ApiService.get('/series', { is_hot: 1, sort: 'hot', per_page: limit });
    }

    async getRecentEpisodes(limit = 5) {
        return ApiService.get('/episodes/recent', { limit });
    }
}

export default new StoryService();

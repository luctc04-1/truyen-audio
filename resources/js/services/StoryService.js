import ApiService from './ApiService';

/**
 * Story Service
 * Handles all story-related API calls
 */

class StoryService {
    /**
     * Get all stories
     *
     * @param {Object} params
     * @returns {Promise}
     */
    async getStories(params = {}) {
        return ApiService.get('/stories', params);
    }

    /**
     * Get story by ID
     *
     * @param {number} id
     * @returns {Promise}
     */
    async getStory(id) {
        return ApiService.get(`/stories/${id}`);
    }

    /**
     * Create a new story
     *
     * @param {Object} data
     * @returns {Promise}
     */
    async createStory(data) {
        return ApiService.post('/stories', data);
    }

    /**
     * Update story
     *
     * @param {number} id
     * @param {Object} data
     * @returns {Promise}
     */
    async updateStory(id, data) {
        return ApiService.put(`/stories/${id}`, data);
    }

    /**
     * Delete story
     *
     * @param {number} id
     * @returns {Promise}
     */
    async deleteStory(id) {
        return ApiService.delete(`/stories/${id}`);
    }
}

export default new StoryService();

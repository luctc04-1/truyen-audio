import ApiService from './ApiService';

/**
 * Audio Service
 * Handles all audio-related API calls
 */

class AudioService {
    /**
     * Get all audio files
     *
     * @param {Object} params
     * @returns {Promise}
     */
    async getAudioFiles(params = {}) {
        return ApiService.get('/audio', params);
    }

    /**
     * Get audio file by ID
     *
     * @param {number} id
     * @returns {Promise}
     */
    async getAudioFile(id) {
        return ApiService.get(`/audio/${id}`);
    }

    /**
     * Upload audio file
     *
     * @param {FormData} formData
     * @returns {Promise}
     */
    async uploadAudio(formData) {
        return fetch('/api/audio/upload', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            },
            body: formData,
        }).then(response => response.json());
    }

    /**
     * Delete audio file
     *
     * @param {number} id
     * @returns {Promise}
     */
    async deleteAudio(id) {
        return ApiService.delete(`/audio/${id}`);
    }
}

export default new AudioService();

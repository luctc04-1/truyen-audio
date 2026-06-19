/**
 * API Service
 * Handles all API calls
 */

class ApiService {
    constructor() {
        this.baseURL = '/api';
        this.headers = {
            'Content-Type': 'application/json',
        };
    }

    /**
     * Get auth token from local storage
     *
     * @returns {string|null}
     */
    getToken() {
        return localStorage.getItem('auth_token');
    }

    /**
     * Set auth headers
     *
     * @returns {Object}
     */
    getHeaders() {
        const token = this.getToken();
        const headers = { ...this.headers };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    }

    /**
     * Make a GET request
     *
     * @param {string} endpoint
     * @param {Object} params
     * @returns {Promise}
     */
    async get(endpoint, params = {}) {
        const url = new URL(this.baseURL + endpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: this.getHeaders(),
            });

            return this.handleResponse(response);
        } catch (error) {
            console.error('GET request failed:', error);
            throw error;
        }
    }

    /**
     * Make a POST request
     *
     * @param {string} endpoint
     * @param {Object} data
     * @returns {Promise}
     */
    async post(endpoint, data = {}) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify(data),
            });

            return this.handleResponse(response);
        } catch (error) {
            console.error('POST request failed:', error);
            throw error;
        }
    }

    /**
     * Make a PUT request
     *
     * @param {string} endpoint
     * @param {Object} data
     * @returns {Promise}
     */
    async put(endpoint, data = {}) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'PUT',
                headers: this.getHeaders(),
                body: JSON.stringify(data),
            });

            return this.handleResponse(response);
        } catch (error) {
            console.error('PUT request failed:', error);
            throw error;
        }
    }

    /**
     * Make a DELETE request
     *
     * @param {string} endpoint
     * @returns {Promise}
     */
    async delete(endpoint) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'DELETE',
                headers: this.getHeaders(),
            });

            return this.handleResponse(response);
        } catch (error) {
            console.error('DELETE request failed:', error);
            throw error;
        }
    }

    /**
     * Handle API response
     *
     * @param {Response} response
     * @returns {Promise}
     */
    async handleResponse(response) {
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'API request failed');
        }

        return data;
    }
}

export default new ApiService();

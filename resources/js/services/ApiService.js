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
        Object.entries(params).forEach(([key, value]) => {
            if (value === undefined || value === null || value === '') {
                return;
            }
            url.searchParams.append(key, value);
        });

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: this.getHeaders(),
            });

            return this.handleResponse(response, endpoint);
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

            return this.handleResponse(response, endpoint);
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

            return this.handleResponse(response, endpoint);
        } catch (error) {
            console.error('PUT request failed:', error);
            throw error;
        }
    }

    /**
     * Make a PATCH request
     *
     * @param {string} endpoint
     * @param {Object} data
     * @returns {Promise}
     */
    async patch(endpoint, data = {}) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'PATCH',
                headers: this.getHeaders(),
                body: JSON.stringify(data),
            });

            return this.handleResponse(response, endpoint);
        } catch (error) {
            console.error('PATCH request failed:', error);
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

            return this.handleResponse(response, endpoint);
        } catch (error) {
            console.error('DELETE request failed:', error);
            throw error;
        }
    }

    /**
     * POST multipart/form-data (file uploads)
     */
    async postForm(endpoint, formData) {
        const headers = {};
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'POST',
                headers,
                body: formData,
            });

            return this.handleResponse(response, endpoint);
        } catch (error) {
            console.error('POST form request failed:', error);
            throw error;
        }
    }

    /**
     * Handle API response
     *
     * @param {Response} response
     * @param {string} endpoint
     * @returns {Promise}
     */
    async handleResponse(response, endpoint = '') {
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error(
                `API không trả JSON (${response.status}). Hãy mở qua Laragon hoặc cấu hình VITE_API_PROXY_TARGET.`
            );
        }

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 401) {
                await this.handleUnauthorized();
            } else if (response.status === 403 && endpoint.startsWith('/admin')) {
                await this.handleAdminDenied();
            }

            const error = new Error(data.message || 'API request failed');
            error.status = response.status;
            error.errors = data.errors ?? null;
            throw error;
        }

        return data;
    }

    async handleUnauthorized() {
        const { useAuthStore } = await import('@/stores/authStore');
        const auth = useAuthStore();

        if (!auth.token) {
            return;
        }

        await auth.logout();

        // Bootstrap tự xử lý session; tránh nháy /auth khi reload
        if (!auth.bootstrapped) {
            return;
        }

        const router = (await import('@/router')).default;
        const current = router.currentRoute.value;

        if (current.name !== 'Auth') {
            await router.push({
                name: 'Auth',
                query: { redirect: current.fullPath },
            });
        }
    }

    async handleAdminDenied() {
        const { useAuthStore } = await import('@/stores/authStore');
        const auth = useAuthStore();

        if (!auth.bootstrapped) {
            return;
        }

        const router = (await import('@/router')).default;

        if (router.currentRoute.value.path.startsWith('/admin')) {
            await router.replace({ name: 'Home' });
        }
    }
}

export default new ApiService();

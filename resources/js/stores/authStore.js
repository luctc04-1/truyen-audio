import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));
    const isAuthenticated = computed(() => !!token.value && !!user.value);

    /**
     * Login user
     *
     * @param {string} email
     * @param {string} password
     * @returns {Promise}
     */
    const login = async (email, password) => {
        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            if (!response.ok) {
                throw new Error('Login failed');
            }

            const data = await response.json();
            token.value = data.token;
            user.value = data.user;
            localStorage.setItem('auth_token', token.value);

            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    };

    /**
     * Logout user
     */
    const logout = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('auth_token');
    };

    /**
     * Set user
     *
     * @param {Object} userData
     */
    const setUser = (userData) => {
        user.value = userData;
    };

    return {
        user,
        token,
        isAuthenticated,
        login,
        logout,
        setUser,
    };
});

/**
 * Auth API helpers
 */
import ApiService from '@/services/ApiService';

const AuthService = {
    login(email, password) {
        return ApiService.post('/auth/login', { email, password });
    },

    register({ username, email, password, password_confirmation }) {
        return ApiService.post('/auth/register', {
            username,
            email,
            password,
            password_confirmation,
        });
    },

    google(idToken) {
        return ApiService.post('/auth/google', { id_token: idToken });
    },

    me() {
        return ApiService.get('/auth/me');
    },

    logout() {
        return ApiService.post('/auth/logout');
    },
};

export default AuthService;

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

    updateProfile(data) {
        return ApiService.patch('/auth/me', data);
    },

    getHistory() {
        return ApiService.get('/auth/me/history');
    },

    getFollowedSeries() {
        return ApiService.get('/auth/me/follows/series');
    },

    recordProgress({ episodeId, listenedSeconds, completed = false }) {
        return ApiService.post('/auth/me/progress', {
            episode_id:       episodeId,
            listened_seconds: listenedSeconds,
            completed,
        });
    },

    logout() {
        return ApiService.post('/auth/logout');
    },
};

export default AuthService;

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import AuthService from '@/services/AuthService';
import { useToastStore } from '@/stores/toastStore';
import { extractApiPayload } from '@/utils/helpers';
import { getEcho, disconnectEcho } from '@/services/echo';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));
    const loading = ref(false);
    const transitioning = ref(false);
    const bootstrapped = ref(false);

    const isAuthenticated = computed(() => !!token.value && !!user.value);
    const isAdmin = computed(() => !!user.value?.is_admin);
    const isPremium = computed(() => !!user.value?.is_premium);
    const isBusy = computed(() => loading.value || transitioning.value);

    const displayName = computed(() => {
        if (!user.value) return '';
        return user.value.username || user.value.email || 'Người dùng';
    });

    const avatarInitial = computed(() => {
        const name = displayName.value.trim();
        return name ? name.charAt(0).toUpperCase() : 'U';
    });

    const startTransition = () => {
        transitioning.value = true;
    };

    const endTransition = () => {
        transitioning.value = false;
        loading.value = false;
    };

    const resetLoadingIfIdle = () => {
        if (!transitioning.value) {
            loading.value = false;
        }
    };

    const persistSession = (payload) => {
        token.value = payload.token;
        user.value = payload.user;

        if (token.value) {
            localStorage.setItem('auth_token', token.value);
            getEcho(token.value);
        }
    };

    const authenticate = async (request) => {
        loading.value = true;
        try {
            const payload = extractApiPayload(await request());
            persistSession(payload);
            return payload;
        } finally {
            resetLoadingIfIdle();
        }
    };

    const login = (email, password) => authenticate(() => AuthService.login(email, password));

    const register = (form) => authenticate(() => AuthService.register({
        username: form.username,
        email: form.email,
        password: form.password,
        password_confirmation: form.passwordConfirm,
    }));

    const loginWithGoogle = (idToken) => authenticate(() => AuthService.google(idToken));

    const completeNavigation = async (router, redirectPath = '/', toastMessage = null) => {
        await router.replace(redirectPath || '/');
        endTransition();
        if (toastMessage) {
            useToastStore().success(toastMessage);
        }
    };

    const runAuthenticatedFlow = async (router, { action, redirect = '/', toastMessage = 'Đăng nhập thành công!' }) => {
        startTransition();
        try {
            await action();
            await completeNavigation(router, redirect, toastMessage);
        } catch (error) {
            endTransition();
            throw error;
        }
    };

    const runLogoutFlow = async (router, redirect = '/') => {
        startTransition();
        try {
            await logout();
            await completeNavigation(router, redirect, 'Đã đăng xuất');
        } catch {
            endTransition();
        }
    };

    const fetchMe = async () => {
        if (!token.value) {
            return null;
        }

        user.value = extractApiPayload(await AuthService.me());
        getEcho(token.value);
        return user.value;
    };

    const bootstrap = async () => {
        if (bootstrapped.value) {
            return;
        }

        bootstrapped.value = true;

        if (!token.value) {
            return;
        }

        try {
            await fetchMe();
            getEcho(token.value);
        } catch {
            await logout();
        }
    };

    const logout = async () => {
        loading.value = true;
        try {
            if (token.value) {
                await AuthService.logout();
            }
        } catch {
            // Client-side logout even if API fails
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
            disconnectEcho();
            resetLoadingIfIdle();
        }
    };

    return {
        user,
        token,
        loading,
        transitioning,
        isBusy,
        bootstrapped,
        isAuthenticated,
        isAdmin,
        isPremium,
        displayName,
        avatarInitial,
        startTransition,
        endTransition,
        completeNavigation,
        runAuthenticatedFlow,
        runLogoutFlow,
        login,
        register,
        loginWithGoogle,
        fetchMe,
        bootstrap,
        logout,
    };
});

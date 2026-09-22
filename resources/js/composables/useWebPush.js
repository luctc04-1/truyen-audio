import { ref } from 'vue';
import { useToastStore } from '@/stores/toastStore';
import NotificationService from '@/services/NotificationService';

const PUSH_STORAGE_KEY = 'push_notifications_enabled';
const PUSH_ENDPOINT_KEY = 'push_endpoint';

/**
 * Kiểm tra trình duyệt có hỗ trợ Web Push và Notification không
 */
export const isPushSupported = () => {
    return typeof window !== 'undefined' && 'Notification' in window && 'serviceWorker' in navigator;
};

/**
 * Composable quản lý logic Web Push Notifications
 */
export const useWebPush = () => {
    const toast = useToastStore();
    const pushEnabled = ref(false);
    const pushLoading = ref(false);
    const testPushLoading = ref(false);

    /**
     * Đăng ký Service Worker an toàn
     */
    const registerServiceWorker = async () => {
        if (!isPushSupported()) return null;
        try {
            const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            await navigator.serviceWorker.ready;
            return registration;
        } catch (err) {
            console.warn('[WebPush] Service Worker registration failed:', err);
            return null;
        }
    };

    /**
     * Lấy endpoint push của thiết bị hiện tại
     */
    const resolveEndpoint = async (registration) => {
        let endpoint = localStorage.getItem(PUSH_ENDPOINT_KEY);

        if (registration?.pushManager) {
            try {
                const sub = await registration.pushManager.getSubscription();
                if (sub?.endpoint) {
                    endpoint = sub.endpoint;
                }
            } catch {
                // Fallback
            }
        }

        if (!endpoint) {
            endpoint = `browser-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
        }

        localStorage.setItem(PUSH_ENDPOINT_KEY, endpoint);
        return endpoint;
    };

    /**
     * Đồng bộ trạng thái toggle với quyền trình duyệt và bộ nhớ cache
     */
    const syncPushState = () => {
        if (!isPushSupported()) {
            pushEnabled.value = false;
            return;
        }

        if (Notification.permission === 'granted') {
            const pref = localStorage.getItem(PUSH_STORAGE_KEY);
            pushEnabled.value = pref !== 'false';
        } else {
            pushEnabled.value = false;
        }
    };

    /**
     * Lắng nghe người dùng thay đổi quyền thông báo trong cài đặt trình duyệt
     */
    const listenPermissionChanges = () => {
        if (typeof navigator !== 'undefined' && navigator.permissions?.query) {
            navigator.permissions.query({ name: 'notifications' }).then((status) => {
                status.onchange = () => syncPushState();
            }).catch(() => {});
        }
    };

    /**
     * Hiển thị thông báo trên màn hình trình duyệt (Native Notification)
     */
    const showLocalNotification = async (title, options = {}) => {
        const defaultOptions = {
            icon: '/favicon-32x32.png',
            badge: '/favicon-16x16.png',
            ...options,
        };

        try {
            const registration = await registerServiceWorker();
            if (registration?.showNotification) {
                await registration.showNotification(title, defaultOptions);
                return;
            }
        } catch {
            // Fallback
        }

        try {
            new Notification(title, defaultOptions);
        } catch (err) {
            console.warn('[WebPush] Native notification display failed:', err);
        }
    };

    /**
     * Đăng ký kích hoạt thông báo đẩy
     */
    const subscribe = async () => {
        if (!isPushSupported()) {
            toast.warning('Trình duyệt của bạn không hỗ trợ thông báo đẩy.');
            return false;
        }

        if (Notification.permission === 'denied') {
            toast.error('Trình duyệt đang chặn thông báo. Vui lòng vào Cài đặt trang web trên thanh địa chỉ để mở quyền.');
            return false;
        }

        let perm = Notification.permission;
        if (perm !== 'granted') {
            perm = await Notification.requestPermission();
        }

        if (perm !== 'granted') {
            toast.info('Quyền thông báo chưa được cấp.');
            pushEnabled.value = false;
            return false;
        }

        const registration = await registerServiceWorker();
        const endpoint = await resolveEndpoint(registration);

        try {
            await NotificationService.subscribePush({ endpoint });
        } catch {
            // Tiếp tục ngay cả khi API gặp sự cố mạng tạm thời
        }

        localStorage.setItem(PUSH_STORAGE_KEY, 'true');
        pushEnabled.value = true;
        return true;
    };

    /**
     * Hủy nhận thông báo đẩy
     */
    const unsubscribe = async () => {
        const endpoint = localStorage.getItem(PUSH_ENDPOINT_KEY);
        try {
            await NotificationService.unsubscribePush({ endpoint });
        } catch {
            // Bỏ qua lỗi backend nếu có
        } finally {
            localStorage.setItem(PUSH_STORAGE_KEY, 'false');
            pushEnabled.value = false;
        }
        return true;
    };

    /**
     * Chuyển đổi trạng thái bật / tắt thông báo
     */
    const togglePush = async () => {
        if (pushLoading.value) return;

        pushLoading.value = true;
        try {
            if (pushEnabled.value) {
                await unsubscribe();
                toast.info('Đã tắt thông báo đẩy.');
            } else {
                const success = await subscribe();
                if (success) {
                    toast.success('Đã bật thông báo đẩy thành công!');
                }
            }
        } catch (error) {
            console.error('[WebPush] Toggle failed:', error);
            toast.error('Không thể thay đổi trạng thái thông báo');
        } finally {
            pushLoading.value = false;
        }
    };

    /**
     * Gửi thông báo thử nghiệm
     */
    const handleTestPush = async () => {
        if (testPushLoading.value) return;

        if (!isPushSupported()) {
            toast.warning('Trình duyệt của bạn không hỗ trợ thông báo.');
            return;
        }

        testPushLoading.value = true;
        try {
            let perm = Notification.permission;
            if (perm === 'denied') {
                toast.error('Trình duyệt đang chặn thông báo. Vui lòng cấp quyền trong cài đặt trình duyệt.');
                return;
            }

            if (perm !== 'granted') {
                perm = await Notification.requestPermission();
                if (perm !== 'granted') {
                    toast.info('Bạn cần cho phép quyền thông báo để nhận thông báo thử nghiệm.');
                    return;
                }
                localStorage.setItem(PUSH_STORAGE_KEY, 'true');
                pushEnabled.value = true;
            }

            // 1. Hiển thị thông báo màn hình máy tính / điện thoại
            await showLocalNotification('🔔 Thông báo thử nghiệm', {
                body: 'Tuyệt vời! Tính năng thông báo trên TruyenAudio đang hoạt động rất tốt.',
                tag: `test-push-${Date.now()}`,
                data: { url: '/profile' },
            });

            // 2. Gửi về server để ghi nhận vào chuông thông báo & phát realtime
            try {
                await NotificationService.sendTestPush();
            } catch (e) {
                console.warn('[WebPush] Send test push to backend failed:', e);
            }

            toast.success('Đã gửi thông báo thử nghiệm!');
        } catch (err) {
            console.error('[WebPush] Test push error:', err);
            toast.error('Không thể gửi thông báo thử');
        } finally {
            testPushLoading.value = false;
        }
    };

    return {
        pushEnabled,
        pushLoading,
        testPushLoading,
        isPushSupported,
        syncPushState,
        listenPermissionChanges,
        showLocalNotification,
        subscribe,
        unsubscribe,
        togglePush,
        handleTestPush,
    };
};

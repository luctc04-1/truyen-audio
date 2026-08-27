import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import NotificationService from '@/services/NotificationService';
import { useToastStore } from '@/stores/toastStore';
import { getEcho } from '@/services/echo';

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(false);
    const loadingMore = ref(false);
    const page = ref(1);
    const lastPage = ref(1);
    const isPanelOpen = ref(false);
    const activeFilter = ref('all'); // 'all' | 'unread'
    let currentUserId = null;
    let realtimeToken = null;

    const hasMore = computed(() => page.value < lastPage.value);
    const displayUnreadBadge = computed(() => {
        if (unreadCount.value <= 0) return '';
        return unreadCount.value > 99 ? '99+' : String(unreadCount.value);
    });

    const filteredNotifications = computed(() => {
        if (activeFilter.value === 'unread') {
            return notifications.value.filter(item => !item.is_read);
        }
        return notifications.value;
    });

    const fetchUnreadCount = async () => {
        try {
            const data = await NotificationService.getUnreadCount();
            if (data && typeof data.unread_count === 'number') {
                unreadCount.value = data.unread_count;
            }
        } catch {
            // Silently ignore when guest/unauthenticated
        }
    };

    const fetchNotifications = async (reset = true) => {
        if (reset) {
            page.value = 1;
            loading.value = true;
        } else {
            loadingMore.value = true;
        }

        try {
            const res = await NotificationService.getNotifications({
                page: page.value,
                per_page: 15,
                unread_only: activeFilter.value === 'unread' ? 1 : 0,
            });

            if (res) {
                const items = res.items || [];
                if (reset) {
                    notifications.value = items;
                } else {
                    const existingIds = new Set(notifications.value.map(n => n.id));
                    const uniqueNewItems = items.filter(n => !existingIds.has(n.id));
                    notifications.value.push(...uniqueNewItems);
                }

                if (typeof res.unread_count === 'number') {
                    unreadCount.value = res.unread_count;
                }

                if (res.pagination) {
                    page.value = res.pagination.current_page || 1;
                    lastPage.value = res.pagination.last_page || 1;
                }
            }
        } catch (error) {
            console.error('Lỗi khi tải thông báo:', error);
        } finally {
            loading.value = false;
            loadingMore.value = false;
        }
    };

    const loadMore = async () => {
        if (loadingMore.value || !hasMore.value) return;
        page.value += 1;
        await fetchNotifications(false);
    };

    const setFilter = (filter) => {
        if (activeFilter.value === filter) return;
        activeFilter.value = filter;
        fetchNotifications(true);
    };

    const markAsRead = async (id) => {
        const item = notifications.value.find(n => n.id === id);
        if (item && !item.is_read) {
            item.is_read = true;
            item.read_at = new Date().toISOString();
            if (unreadCount.value > 0) unreadCount.value -= 1;
        }

        try {
            const data = await NotificationService.markAsRead(id);
            if (data && typeof data.unread_count === 'number') {
                unreadCount.value = data.unread_count;
            }
        } catch (err) {
            console.error('Không thể đánh dấu đã đọc:', err);
        }
    };

    const markAllAsRead = async () => {
        notifications.value.forEach(item => {
            item.is_read = true;
            item.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;

        try {
            await NotificationService.markAllAsRead();
            useToastStore().success('Đã đánh dấu tất cả thông báo là đã đọc');
        } catch (err) {
            console.error('Lỗi khi đánh dấu tất cả đã đọc:', err);
        }
    };

    const deleteNotification = async (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            const wasUnread = !notifications.value[index].is_read;
            notifications.value.splice(index, 1);
            if (wasUnread && unreadCount.value > 0) {
                unreadCount.value -= 1;
            }
        }

        try {
            const res = await NotificationService.deleteNotification(id);
            if (res && typeof res.unread_count === 'number') {
                unreadCount.value = res.unread_count;
            }
        } catch (err) {
            console.error('Lỗi khi xóa thông báo:', err);
        }
    };

    const clearAll = async () => {
        notifications.value = [];
        unreadCount.value = 0;
        try {
            await NotificationService.deleteAll();
            useToastStore().success('Đã xóa tất cả thông báo');
        } catch (err) {
            console.error('Lỗi khi xóa toàn bộ thông báo:', err);
        }
    };

    const togglePanel = (forceState) => {
        isPanelOpen.value = typeof forceState === 'boolean' ? forceState : !isPanelOpen.value;
        if (isPanelOpen.value && notifications.value.length === 0) {
            fetchNotifications(true);
        }
    };

    const closePanel = () => {
        isPanelOpen.value = false;
    };

    const handleIncomingNotification = (event) => {
        if (!event?.notification) return;
        const newNotif = event.notification;
        if (!notifications.value.some(n => n.id === newNotif.id)) {
            notifications.value.unshift(newNotif);
        }
        unreadCount.value = typeof event.unread_count === 'number'
            ? event.unread_count
            : unreadCount.value + 1;

        useToastStore().info(newNotif.title || 'Bạn có thông báo mới!');

        // Hiển thị Native Desktop/Mobile Notification nếu đã được cấp quyền
        if (typeof window !== 'undefined' && 'Notification' in window && Notification.permission === 'granted') {
            try {
                if (navigator.serviceWorker?.controller) {
                    navigator.serviceWorker.ready.then((reg) => {
                        reg.showNotification(newNotif.title || 'Truyện Audio Hay', {
                            body: newNotif.content || 'Bạn có thông báo mới!',
                            icon: '/favicon-32x32.png',
                            badge: '/favicon-16x16.png',
                            tag: newNotif.id || 'truyen-audio-notif',
                            data: {
                                url: newNotif.action_url || '/',
                            },
                        });
                    });
                } else {
                    const n = new Notification(newNotif.title || 'Truyện Audio Hay', {
                        body: newNotif.content || 'Bạn có thông báo mới!',
                        icon: '/favicon-32x32.png',
                    });
                    if (newNotif.action_url) {
                        n.onclick = () => {
                            window.focus();
                            window.location.href = newNotif.action_url;
                        };
                    }
                }
            } catch (err) {
                // Ignore native notification error
            }
        }
    };

    const initRealtime = (userId, token) => {
        if (!userId || !token) return;
        if (currentUserId === userId && realtimeToken === token) return;

        cleanupRealtime();

        currentUserId = userId;
        realtimeToken = token;
        const echo = getEcho(token);
        if (!echo) return;

        try {
            echo.private(`user.${userId}`)
                .listen('.notification.created', handleIncomingNotification)
                .listen('NotificationCreated', handleIncomingNotification);
        } catch (err) {
            console.warn('Không thể đăng ký realtime notifications:', err);
        }
    };

    const cleanupRealtime = () => {
        if (currentUserId && realtimeToken) {
            const echo = getEcho(realtimeToken);
            if (echo) {
                try {
                    echo.leave(`user.${currentUserId}`);
                } catch {
                    // Ignore cleanup error
                }
            }
        }
        currentUserId = null;
        realtimeToken = null;
    };

    const reset = () => {
        cleanupRealtime();
        notifications.value = [];
        unreadCount.value = 0;
        loading.value = false;
        loadingMore.value = false;
        page.value = 1;
        lastPage.value = 1;
        isPanelOpen.value = false;
        activeFilter.value = 'all';
    };

    return {
        notifications,
        filteredNotifications,
        unreadCount,
        displayUnreadBadge,
        loading,
        loadingMore,
        hasMore,
        isPanelOpen,
        activeFilter,
        fetchUnreadCount,
        fetchNotifications,
        loadMore,
        setFilter,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        clearAll,
        togglePanel,
        closePanel,
        initRealtime,
        reset,
    };
});

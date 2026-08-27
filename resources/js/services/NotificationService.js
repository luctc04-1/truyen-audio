import ApiService from './ApiService';
import { extractApiPayload } from '@/utils/helpers';

class NotificationService {
    /**
     * Lấy danh sách thông báo
     * @param {Object} params { page, per_page, unread_only, type }
     */
    async getNotifications(params = {}) {
        const res = await ApiService.get('/notifications', params);
        return extractApiPayload(res);
    }

    /**
     * Lấy số lượng thông báo chưa đọc
     */
    async getUnreadCount() {
        const res = await ApiService.get('/notifications/unread-count');
        return extractApiPayload(res);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     * @param {string} id
     */
    async markAsRead(id) {
        const res = await ApiService.patch(`/notifications/${id}/read`);
        return extractApiPayload(res);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    async markAllAsRead() {
        const res = await ApiService.post('/notifications/read-all');
        return extractApiPayload(res);
    }

    /**
     * Xóa 1 thông báo
     * @param {string} id
     */
    async deleteNotification(id) {
        const res = await ApiService.delete(`/notifications/${id}`);
        return extractApiPayload(res);
    }

    /**
     * Xóa toàn bộ thông báo
     */
    async deleteAll() {
        const res = await ApiService.delete('/notifications');
        return extractApiPayload(res);
    }

    /**
     * Đăng ký Web Push Notification
     */
    async subscribePush(data) {
        const res = await ApiService.post('/notifications/push-subscribe', data);
        return extractApiPayload(res);
    }

    /**
     * Hủy đăng ký Web Push Notification
     */
    async unsubscribePush(data) {
        const res = await ApiService.post('/notifications/push-unsubscribe', data);
        return extractApiPayload(res);
    }
}

export default new NotificationService();

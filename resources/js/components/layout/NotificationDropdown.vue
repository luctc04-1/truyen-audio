<template>
  <div class="notification-wrapper" ref="wrapperRef">
    <!-- Bell Button with Badge -->
    <button 
      class="icon-btn notif-bell-btn" 
      :class="{ active: notifStore.isPanelOpen, 'has-unread': notifStore.unreadCount > 0 }"
      title="Thông báo"
      aria-label="Thông báo"
      @click="handleToggle"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="bell-icon">
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
      </svg>
      
      <!-- Unread Badge -->
      <span v-if="notifStore.unreadCount > 0" class="notif-badge">
        {{ notifStore.displayUnreadBadge }}
      </span>
      <span v-if="notifStore.unreadCount > 0" class="notif-ping"></span>
    </button>

    <!-- Dropdown Panel -->
    <Transition name="notif-dropdown">
      <div v-if="notifStore.isPanelOpen" class="notif-dropdown">
        <!-- Header -->
        <div class="notif-header">
          <div class="notif-title-row">
            <div class="title-with-badge">
              <h3 class="notif-title">Thông báo</h3>
              <span v-if="notifStore.unreadCount > 0" class="unread-pill">
                {{ notifStore.unreadCount }} mới
              </span>
            </div>
            <div class="header-actions-group">
              <button 
                v-if="notifStore.unreadCount > 0"
                class="action-text-btn" 
                title="Đánh dấu tất cả đã đọc"
                @click="notifStore.markAllAsRead"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Đã đọc tất cả</span>
              </button>
            </div>
          </div>

          <!-- Filter Tabs -->
          <div class="notif-tabs">
            <button 
              class="tab-btn" 
              :class="{ active: notifStore.activeFilter === 'all' }"
              @click="notifStore.setFilter('all')"
            >
              Tất cả
            </button>
            <button 
              class="tab-btn" 
              :class="{ active: notifStore.activeFilter === 'unread' }"
              @click="notifStore.setFilter('unread')"
            >
              Chưa đọc {{ notifStore.unreadCount > 0 ? `(${notifStore.unreadCount})` : '' }}
            </button>
          </div>
        </div>

        <!-- Body / Notifications List -->
        <div class="notif-body">
          <!-- Loading State -->
          <div v-if="notifStore.loading" class="notif-loading">
            <div class="spinner"></div>
            <span>Đang tải thông báo...</span>
          </div>

          <!-- Empty State -->
          <div v-else-if="notifStore.filteredNotifications.length === 0" class="notif-empty">
            <div class="empty-icon-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                <line x1="2" y1="2" x2="22" y2="22"/>
              </svg>
            </div>
            <p class="empty-title">Không có thông báo nào</p>
            <p class="empty-desc">
              {{ notifStore.activeFilter === 'unread' ? 'Bạn đã đọc hết toàn bộ thông báo!' : 'Khi có phản hồi bình luận hoặc sự kiện mới, thông báo sẽ xuất hiện ở đây.' }}
            </p>
          </div>

          <!-- List Items -->
          <div v-else class="notif-list">
            <div 
              v-for="item in notifStore.filteredNotifications" 
              :key="item.id"
              class="notif-item"
              :class="{ 'is-unread': !item.is_read }"
              @click="handleClickNotification(item)"
            >
              <!-- Icon Container -->
              <div class="item-icon-wrap" :class="`icon-type-${item.type || 'system'}`">
                <!-- VIP Icon -->
                <svg v-if="item.type === 'vip'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/>
                  <path d="M5 21h14"/>
                </svg>

                <!-- Comment Icon -->
                <svg v-else-if="item.type === 'comment'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>

                <!-- Community Icon -->
                <svg v-else-if="item.type === 'community'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>

                <!-- Episode / Story Icon -->
                <svg v-else-if="item.type === 'story' || item.type === 'episode'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                  <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                </svg>

                <!-- System / Default Icon -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                  <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
              </div>

              <!-- Content Area -->
              <div class="item-content">
                <div class="item-top">
                  <h4 class="item-title">{{ item.title }}</h4>
                  <span class="item-time">{{ formatTimeAgo(item.created_at) }}</span>
                </div>
                <p class="item-desc">{{ item.content }}</p>
              </div>

              <!-- Actions (Delete button) -->
              <div class="item-actions" @click.stop>
                <button 
                  class="item-delete-btn" 
                  title="Xóa thông báo này"
                  @click="notifStore.deleteNotification(item.id)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="6"/>
                  </svg>
                </button>
              </div>

              <!-- Unread Dot Indicator -->
              <div v-if="!item.is_read" class="unread-dot" title="Chưa đọc"></div>
            </div>
          </div>

          <!-- Load More Button -->
          <div v-if="notifStore.hasMore && !notifStore.loading" class="notif-footer">
            <button 
              class="load-more-btn" 
              :disabled="notifStore.loadingMore"
              @click="notifStore.loadMore"
            >
              <span v-if="notifStore.loadingMore">Đang tải thêm...</span>
              <span v-else>Xem thêm thông báo cũ hơn</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useNotificationStore } from '@/stores/notificationStore';

const router = useRouter();
const authStore = useAuthStore();
const notifStore = useNotificationStore();
const wrapperRef = ref(null);

const handleToggle = () => {
  if (!authStore.isAuthenticated) {
    router.push({ path: '/auth', query: { redirect: router.currentRoute.value.fullPath } });
    return;
  }
  notifStore.togglePanel();
};

const handleClickNotification = (item) => {
  if (!item.is_read) {
    notifStore.markAsRead(item.id);
  }
  notifStore.closePanel();

  if (item.action_url) {
    router.push(item.action_url);
    return;
  }

  // Route navigation fallback based on reference
  if (item.reference_type === 'series' && item.reference_id) {
    router.push(`/story/${item.reference_id}`);
  } else if (item.reference_type === 'community_post') {
    router.push('/community');
  } else if (item.reference_type === 'vip' || item.reference_type === 'order') {
    router.push('/vip');
  }
};

const formatTimeAgo = (dateString) => {
  if (!dateString) return '';
  const now = new Date();
  const date = new Date(dateString);
  const diffInSec = Math.floor((now - date) / 1000);

  if (diffInSec < 60) return 'Vừa xong';
  const diffInMin = Math.floor(diffInSec / 60);
  if (diffInMin < 60) return `${diffInMin} phút trước`;
  const diffInHours = Math.floor(diffInMin / 60);
  if (diffInHours < 24) return `${diffInHours} giờ trước`;
  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInDays < 7) return `${diffInDays} ngày trước`;
  if (diffInDays < 30) return `${Math.floor(diffInDays / 7)} tuần trước`;

  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

const handleOutsideClick = (e) => {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    notifStore.closePanel();
  }
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && notifStore.isPanelOpen) {
    notifStore.closePanel();
  }
};

onMounted(() => {
  document.addEventListener('click', handleOutsideClick);
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick);
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.notification-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.notif-bell-btn {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.notif-bell-btn:hover,
.notif-bell-btn.active {
  background: var(--bg-muted);
  color: var(--text);
}

.notif-bell-btn.active {
  color: var(--primary);
}

.bell-icon {
  width: 20px;
  height: 20px;
}

/* Badge Counter */
.notif-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  min-width: 17px;
  height: 17px;
  padding: 0 4px;
  background: linear-gradient(135deg, #ec4899, #ef4444);
  color: #fff;
  border-radius: 9999px;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
  z-index: 2;
  line-height: 1;
}

.notif-ping {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 17px;
  height: 17px;
  border-radius: 9999px;
  background: #ef4444;
  opacity: 0.75;
  animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
  z-index: 1;
  pointer-events: none;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

/* Dropdown Window */
.notif-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: -50px;
  width: 380px;
  max-width: calc(100vw - 24px);
  max-height: 540px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.04);
  backdrop-filter: blur(20px);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

@media (min-width: 640px) {
  .notif-dropdown {
    right: 0;
    width: 410px;
  }
}

/* Header */
.notif-header {
  padding: 14px 16px 10px;
  border-bottom: 1px solid var(--border);
  background: var(--bg-card);
}

.notif-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.title-with-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.notif-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.unread-pill {
  font-size: 11px;
  font-weight: 600;
  background: var(--primary-light);
  color: var(--primary);
  padding: 2px 8px;
  border-radius: 999px;
  border: 1px solid var(--primary-light-border);
}

.action-text-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px 6px;
  border-radius: var(--radius-sm);
  transition: all 0.2s;
}

.action-text-btn:hover {
  color: var(--primary);
  background: var(--primary-light2);
}

.check-icon {
  width: 14px;
  height: 14px;
}

/* Tabs */
.notif-tabs {
  display: flex;
  gap: 6px;
  background: var(--bg-muted);
  padding: 3px;
  border-radius: var(--radius-sm);
}

.tab-btn {
  flex: 1;
  padding: 5px 10px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  background: transparent;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.tab-btn.active {
  background: var(--bg-card);
  color: var(--text);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Body */
.notif-body {
  overflow-y: auto;
  max-height: 420px;
  display: flex;
  flex-direction: column;
}

.notif-body::-webkit-scrollbar {
  width: 5px;
}

.notif-body::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 4px;
}

/* Loading & Empty */
.notif-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  gap: 12px;
  color: var(--text-muted);
  font-size: 13px;
}

.spinner {
  width: 26px;
  height: 26px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.notif-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
  text-align: center;
}

.empty-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--bg-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  margin-bottom: 12px;
}

.empty-icon-wrap svg {
  width: 26px;
  height: 26px;
}

.empty-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  margin: 0 0 4px;
}

.empty-desc {
  font-size: 12px;
  color: var(--text-muted);
  margin: 0;
  max-width: 260px;
  line-height: 1.5;
}

/* Item */
.notif-list {
  display: flex;
  flex-direction: column;
}

.notif-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.15s ease;
}

.notif-item:hover {
  background: var(--bg-muted);
}

.notif-item.is-unread {
  background: rgba(168, 85, 247, 0.04);
}

.notif-item.is-unread:hover {
  background: rgba(168, 85, 247, 0.08);
}

/* Icons by Type */
.item-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.item-icon-wrap svg {
  width: 18px;
  height: 18px;
}

.icon-type-vip {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(236, 72, 153, 0.2));
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.icon-type-comment {
  background: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--primary-light-border);
}

.icon-type-community {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.25);
}

.icon-type-story,
.icon-type-episode {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.icon-type-system {
  background: var(--bg-muted);
  color: var(--text-muted);
  border: 1px solid var(--border);
}

/* Content */
.item-content {
  flex: 1;
  min-width: 0;
}

.item-top {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 3px;
}

.item-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  margin: 0;
  line-height: 1.4;
}

.item-time {
  font-size: 11px;
  color: var(--text-muted);
  white-space: nowrap;
  flex-shrink: 0;
}

.item-desc {
  font-size: 12px;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Delete Button */
.item-actions {
  opacity: 0;
  transition: opacity 0.2s;
  align-self: center;
}

.notif-item:hover .item-actions {
  opacity: 1;
}

.item-delete-btn {
  background: none;
  border: none;
  padding: 4px;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.item-delete-btn:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.item-delete-btn svg {
  width: 14px;
  height: 14px;
}

/* Unread Dot */
.unread-dot {
  position: absolute;
  top: 14px;
  right: 12px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}

.notif-item:hover .unread-dot {
  opacity: 0;
}

/* Footer / Load More */
.notif-footer {
  padding: 10px 16px;
  border-top: 1px solid var(--border);
  background: var(--bg-card);
  text-align: center;
}

.load-more-btn {
  width: 100%;
  padding: 6px 12px;
  background: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 500;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s;
}

.load-more-btn:hover:not(:disabled) {
  background: var(--primary-light);
  color: var(--primary);
  border-color: var(--primary-border);
}

/* Transitions */
.notif-dropdown-enter-active,
.notif-dropdown-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.notif-dropdown-enter-from,
.notif-dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}
</style>

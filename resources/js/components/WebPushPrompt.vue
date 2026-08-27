<template>
  <Transition name="slide-prompt">
    <div v-if="visible" class="web-push-prompt-container">
      <div class="web-push-prompt-card">
        <!-- Bell Icon -->
        <div class="prompt-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="prompt-bell-icon">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
          </svg>
        </div>

        <!-- Content & Actions -->
        <div class="prompt-main">
          <div class="prompt-text">
            <p class="prompt-title">Bật thông báo Truyện Audio</p>
            <p class="prompt-desc">
              Nhận thông báo về các tập truyện mới nhất và cập nhật hấp dẫn. Bạn có thể tắt bất cứ lúc nào.
            </p>
          </div>

          <div class="prompt-actions">
            <button type="button" class="btn-later" @click="handleLater">
              Để sau
            </button>
            <button type="button" class="btn-subscribe" :disabled="subscribing" @click="handleSubscribe">
              <span v-if="subscribing" class="sub-spinner"></span>
              <span v-else>Bật thông báo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToastStore } from '@/stores/toastStore';
import NotificationService from '@/services/NotificationService';

const visible = ref(false);
const subscribing = ref(false);
const toast = useToastStore();

const DISMISS_DAYS = 3; // Nhắc lại sau 3 ngày nếu bấm 'Để sau'

const isPushSupported = () => {
  return typeof window !== 'undefined' && 'Notification' in window && 'serviceWorker' in navigator;
};

const shouldShowPrompt = () => {
  if (!isPushSupported()) return false;
  if (Notification.permission === 'granted' || Notification.permission === 'denied') {
    return false;
  }

  const dismissedAt = localStorage.getItem('push_prompt_dismissed_at');
  if (dismissedAt) {
    const elapsedDays = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
    if (elapsedDays < DISMISS_DAYS) {
      return false;
    }
  }

  return true;
};

const handleLater = () => {
  localStorage.setItem('push_prompt_dismissed_at', String(Date.now()));
  visible.value = false;
};

const registerServiceWorker = async () => {
  try {
    const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
    await navigator.serviceWorker.ready;
    return registration;
  } catch (err) {
    console.warn('Service Worker registration failed:', err);
    return null;
  }
};

const handleSubscribe = async () => {
  if (!isPushSupported()) return;

  subscribing.value = true;
  try {
    const permission = await Notification.requestPermission();
    
    if (permission === 'granted') {
      const registration = await registerServiceWorker();
      
      // Gửi đăng ký về backend
      try {
        let endpoint = `browser-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
        if (registration?.pushManager) {
          try {
            const existingSub = await registration.pushManager.getSubscription();
            if (existingSub?.endpoint) {
              endpoint = existingSub.endpoint;
            }
          } catch {
            // Fallback to custom endpoint identifier
          }
        }

        await NotificationService.subscribePush({ endpoint });
      } catch {
        // Silently continue
      }

      visible.value = false;
      toast.success('Đã bật thông báo trình duyệt thành công!');

      // Hiển thị thông báo chào mừng
      try {
        const welcomeOptions = {
          body: 'Bạn sẽ nhận được thông báo khi có tập truyện mới và phản hồi bình luận.',
          icon: '/favicon-32x32.png',
          badge: '/favicon-16x16.png',
        };

        if (registration?.showNotification) {
          registration.showNotification('🔔 Đã bật thông báo TruyenAudio!', welcomeOptions);
        } else {
          new Notification('🔔 Đã bật thông báo TruyenAudio!', welcomeOptions);
        }
      } catch {
        // Ignore native notification display error
      }
    } else {
      visible.value = false;
    }
  } catch (error) {
    console.error('Lỗi khi xin quyền thông báo:', error);
    visible.value = false;
  } finally {
    subscribing.value = false;
  }
};

onMounted(() => {
  if (shouldShowPrompt()) {
    setTimeout(() => {
      if (shouldShowPrompt()) {
        visible.value = true;
      }
    }, 2500);
  } else if (isPushSupported() && Notification.permission === 'granted') {
    registerServiceWorker();
  }
});
</script>

<style scoped>
.web-push-prompt-container {
  position: fixed;
  top: 16px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 99999;
  width: calc(100% - 32px);
  max-width: 490px;
  pointer-events: none;
}

.web-push-prompt-card {
  pointer-events: auto;
  background: var(--bg-card, #111113);
  color: var(--text, #fafafa);
  border: 1px solid var(--border, #27272a);
  border-radius: var(--radius-md, 14px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(24px);
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

[data-theme="light"] .web-push-prompt-card {
  background: #ffffff;
  color: #18181b;
  border-color: #e4e4e7;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
}

.prompt-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: var(--primary-light, rgba(168, 85, 247, 0.12));
  color: var(--primary, #a855f7);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid var(--primary-light-border, rgba(168, 85, 247, 0.25));
}

.prompt-bell-icon {
  width: 22px;
  height: 22px;
}

.prompt-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.prompt-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.prompt-title {
  font-size: 14px;
  font-weight: 700;
  margin: 0;
  color: var(--text, #fafafa);
  line-height: 1.3;
}

.prompt-desc {
  font-size: 12.5px;
  color: var(--text-muted, #a1a1aa);
  margin: 0;
  line-height: 1.45;
}

.prompt-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}

.btn-later {
  background: none;
  border: none;
  color: #0284c7;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-later:hover {
  background: rgba(2, 132, 199, 0.08);
  color: #0369a1;
}

.btn-subscribe {
  background: #0284c7;
  color: #ffffff;
  border: none;
  font-size: 13px;
  font-weight: 600;
  padding: 7px 18px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
}

.btn-subscribe:hover:not(:disabled) {
  background: #0369a1;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.5);
  transform: translateY(-1px);
}

.btn-subscribe:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.sub-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Animations */
.slide-prompt-enter-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-prompt-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-prompt-enter-from,
.slide-prompt-leave-to {
  opacity: 0;
  transform: translateY(-24px) scale(0.96);
}
</style>

<template>
  <header class="admin-topbar">
    <div class="topbar-left">
      <button class="icon-btn menu-toggle-btn" type="button" title="Mở menu" @click="$emit('toggle-sidebar')">
        <i class="ri-menu-2-line"></i>
      </button>

      <div class="topbar-breadcrumb">
        <span class="crumb-app">
          <i class="ri-shield-star-line"></i>
          <span>Quản Trị Hệ Thống</span>
        </span>
        <i class="ri-arrow-right-s-line crumb-divider"></i>
        <strong class="crumb-current">{{ currentTitle }}</strong>
      </div>
    </div>

    <div class="topbar-right">
      <!-- Quick System Health Status Pill -->
      <div class="system-health-pill hide-on-mobile">
        <span class="health-dot"></span>
        <span class="health-label">Trực tuyến</span>
        <span class="health-time">{{ currentTime }}</span>
      </div>

      <!-- Quick Add Story Button -->
      <button class="btn btn-primary btn-sm btn-glow" type="button" @click="$emit('quick-add-series')">
        <i class="ri-add-circle-line"></i>
        <span>Thêm truyện</span>
      </button>

      <!-- Admin Profile Droplet -->
      <div class="user-dropdown">
        <div class="admin-user-badge">
          <div class="avatar-wrap">
            <img v-if="auth.user?.avatar_url" :src="auth.user.avatar_url" alt="" />
            <span v-else>{{ auth.avatarInitial }}</span>
            <span class="avatar-online-dot"></span>
          </div>
          <div class="admin-user-meta">
            <strong>{{ auth.displayName }}</strong>
            <div class="admin-role-tag">
              <i class="ri-vip-crown-fill"></i>
              <span>Super Administrator</span>
            </div>
          </div>
        </div>

        <button class="btn-logout" type="button" title="Đăng xuất khỏi phiên" @click="handleLogout">
          <i class="ri-logout-box-r-line"></i>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

defineProps({
  currentTitle: {
    type: String,
    default: 'Tổng quan',
  },
})

defineEmits(['toggle-sidebar', 'quick-add-series'])

const auth = useAuthStore()
const router = useRouter()
const currentTime = ref('')
let timerInterval = null

function updateClock() {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  updateClock()
  timerInterval = setInterval(updateClock, 30000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

async function handleLogout() {
  await auth.runLogoutFlow(router, '/')
}
</script>

<style scoped>
.admin-topbar {
  height: 70px;
  background: var(--admin-topbar-bg, rgba(9, 12, 20, 0.82));
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  border-bottom: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  padding: 0 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 40;
  width: 100%;
  box-sizing: border-box;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}

.menu-toggle-btn {
  display: none;
}

.topbar-breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
}

.crumb-app {
  color: var(--admin-muted, #94a3b8);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.crumb-app i {
  color: #c084fc;
}

.crumb-divider {
  color: var(--admin-faint, #64748b);
  font-size: 14px;
}

.crumb-current {
  color: #f8fafc;
  font-weight: 700;
  font-size: 14.5px;
  letter-spacing: -0.01em;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.system-health-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 12px;
}

.health-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 8px #10b981;
}

.health-label {
  color: var(--admin-muted);
  font-weight: 500;
}

.health-time {
  color: #cbd5e1;
  font-family: var(--admin-font-mono);
  font-weight: 600;
}

.btn-glow {
  box-shadow: 0 4px 18px rgba(168, 85, 247, 0.4);
}

.user-dropdown {
  display: flex;
  align-items: center;
  gap: 14px;
  padding-left: 14px;
  border-left: 1px solid rgba(255, 255, 255, 0.08);
}

.admin-user-badge {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-wrap {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  color: white;
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 14px;
  position: relative;
  box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35);
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  flex-shrink: 0;
}

.avatar-wrap img {
  width: 100%;
  height: 100%;
  border-radius: 11px;
  object-fit: cover;
}

.avatar-online-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #10b981;
  border: 2px solid #06080e;
  box-shadow: 0 0 6px #10b981;
}

.admin-user-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.admin-user-meta strong {
  font-size: 13.5px;
  font-weight: 700;
  color: #f8fafc;
  white-space: nowrap;
}

.admin-role-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10.5px;
  font-weight: 600;
  color: #c084fc;
  white-space: nowrap;
}

.admin-role-tag i {
  font-size: 11px;
  color: #fbbf24;
}

.btn-logout {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--admin-muted, #94a3b8);
  display: grid;
  place-items: center;
  font-size: 17px;
  cursor: pointer;
  transition: all 0.22s var(--ease-spring);
  flex-shrink: 0;
}

.btn-logout:hover {
  background: rgba(244, 63, 94, 0.18);
  color: #fb7185;
  border-color: rgba(244, 63, 94, 0.4);
  transform: scale(1.08);
  box-shadow: 0 0 16px rgba(244, 63, 94, 0.25);
}

.icon-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  display: grid;
  place-items: center;
  cursor: pointer;
  flex-shrink: 0;
}

@media (max-width: 1024px) {
  .menu-toggle-btn {
    display: grid;
  }
}

@media (max-width: 768px) {
  .admin-topbar {
    padding: 0 16px;
  }

  .admin-user-meta,
  .hide-on-mobile {
    display: none;
  }
}
</style>

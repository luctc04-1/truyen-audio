<template>
  <div>
    <!-- Mobile Backdrop Overlay -->
    <div v-if="open" class="sidebar-backdrop" @click="$emit('close-sidebar')"></div>

    <aside class="admin-sidebar" :class="{ open }">
      <!-- Brand Logo Header -->
      <div class="sidebar-brand">
        <div class="brand-badge">
          <i class="ri-headphone-fill"></i>
          <span class="brand-badge-glow"></span>
        </div>
        <div class="brand-text">
          <strong class="brand-name">Truyện Audio</strong>
          <div class="brand-meta">
            <span class="live-dot"></span>
            <small class="brand-version">Pro Admin Suite</small>
          </div>
        </div>
      </div>

      <!-- Navigation Menu (Grouped Domain Architecture) -->
      <nav class="sidebar-nav">
        <div v-for="(group, gIdx) in normalizedGroups" :key="gIdx" class="nav-group">
          <div v-if="group.title" class="nav-section-title">
            <span>{{ group.title }}</span>
          </div>
          <div class="nav-items-list">
            <a
              v-for="item in group.items"
              :key="item.id"
              href="#"
              class="nav-link"
              :class="{ active: activeSection === item.id }"
              @click.prevent="$emit('change-section', item.id)"
            >
              <span class="active-indicator"></span>
              <div class="nav-icon-wrap" :class="{ 'icon-hot': item.isHot }">
                <i :class="item.icon"></i>
              </div>
              <span class="nav-label">{{ item.label }}</span>
              <span v-if="item.isHot" class="hot-badge-mini">HOT</span>
              <span v-else-if="item.count" class="nav-count">{{ item.count }}</span>
              <i class="ri-arrow-right-s-line nav-arrow"></i>
            </a>
          </div>
        </div>
      </nav>

      <!-- Bottom Action & System Status Card -->
      <div class="sidebar-footer">
        <div class="system-status-box">
          <div class="status-top">
            <span class="pulse-indicator"></span>
            <strong>Hệ thống trực tuyến</strong>
          </div>
          <small>PostgreSQL & Audio Node OK</small>
        </div>

        <router-link to="/" class="btn-return-app" title="Xem giao diện người dùng">
          <i class="ri-home-5-line"></i>
          <span>Về trang chủ Web</span>
        </router-link>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  activeSection: {
    type: String,
    required: true,
  },
  menuGroups: {
    type: Array,
    default: () => [],
  },
  menuItems: {
    type: Array,
    default: () => [],
  },
  open: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['change-section', 'close-sidebar'])

const normalizedGroups = computed(() => {
  if (props.menuGroups && props.menuGroups.length > 0) {
    return props.menuGroups
  }
  return [{ title: 'Phân Hệ Quản Trị', items: props.menuItems }]
})
</script>

<style scoped>
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 2px 6px 18px 6px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-badge {
  width: 44px;
  height: 44px;
  border-radius: 13px;
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  display: grid;
  place-items: center;
  color: white;
  font-size: 22px;
  position: relative;
  box-shadow: 0 4px 18px rgba(168, 85, 247, 0.45);
  flex-shrink: 0;
}

.brand-badge-glow {
  position: absolute;
  inset: -2px;
  border-radius: 15px;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(236, 72, 153, 0.4));
  filter: blur(6px);
  z-index: -1;
  opacity: 0.7;
}

.brand-name {
  display: block;
  font-size: 15px;
  font-weight: 800;
  color: #f8fafc;
  letter-spacing: -0.02em;
}

.brand-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.live-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 8px #10b981;
}

.brand-version {
  font-size: 11px;
  color: var(--admin-muted, #94a3b8);
  font-weight: 600;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}

.nav-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-section-title {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: var(--admin-faint, #64748b);
  padding: 4px 12px 6px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-items-list {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 9px 12px;
  border-radius: 11px;
  color: var(--admin-muted, #94a3b8);
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  position: relative;
  overflow: hidden;
  transition: all 0.22s var(--ease-spring);
}

.active-indicator {
  position: absolute;
  left: 0;
  top: 20%;
  bottom: 20%;
  width: 3.5px;
  border-radius: 0 4px 4px 0;
  background: #c084fc;
  box-shadow: 0 0 10px #c084fc;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.nav-icon-wrap {
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  font-size: 17px;
  color: var(--admin-faint, #64748b);
  transition: color 0.2s ease, transform 0.2s ease;
}

.icon-hot {
  color: #f59e0b;
}

.nav-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-arrow {
  font-size: 14px;
  opacity: 0;
  transform: translateX(-4px);
  transition: all 0.2s ease;
}

.nav-link:hover {
  color: #f8fafc;
  background: rgba(255, 255, 255, 0.06);
}

.nav-link:hover .nav-icon-wrap {
  color: #c084fc;
  transform: scale(1.12);
}

.nav-link:hover .icon-hot {
  color: #fbbf24;
}

.nav-link:hover .nav-arrow {
  opacity: 0.7;
  transform: translateX(0);
}

.nav-link.active {
  color: #ffffff;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.24) 0%, rgba(236, 72, 153, 0.16) 100%);
  border: 1px solid rgba(168, 85, 247, 0.45);
  box-shadow: 0 4px 20px rgba(168, 85, 247, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.nav-link.active .active-indicator {
  opacity: 1;
}

.nav-link.active .nav-icon-wrap {
  color: #c084fc;
}

.nav-link.active .nav-arrow {
  opacity: 1;
  color: #c084fc;
  transform: translateX(0);
}

.hot-badge-mini {
  font-size: 9.5px;
  font-weight: 800;
  padding: 1.5px 6px;
  border-radius: 4px;
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  color: #ffffff;
  box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
}

.nav-count {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(168, 85, 247, 0.22);
  color: #c084fc;
  border: 1px solid rgba(168, 85, 247, 0.3);
}

.sidebar-footer {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.system-status-box {
  background: rgba(255, 255, 255, 0.025);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 12px 14px;
}

.status-top {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 2px;
}

.status-top strong {
  font-size: 12.5px;
  color: #f8fafc;
}

.pulse-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 10px #10b981;
  animation: pulseGlow 2s infinite;
}

.system-status-box small {
  font-size: 11px;
  color: var(--admin-faint, #64748b);
  display: block;
}

.btn-return-app {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 11px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.22s ease;
}

.btn-return-app:hover {
  background: rgba(168, 85, 247, 0.16);
  border-color: rgba(168, 85, 247, 0.38);
  color: #ffffff;
  transform: translateY(-1px);
}
</style>

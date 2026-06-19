<template>
  <header class="header">
    <div class="container header-inner">
      <nav class="nav">
        <router-link to="/" :class="['nav-link', { active: $route.path === '/' }]">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          </svg>
          <span>Trang chủ</span>
        </router-link>
        
        <router-link to="/library" :class="['nav-link', { active: $route.path === '/library' }]">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 7v14"/>
            <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
          </svg>
          <span>Kho truyện</span>
        </router-link>
        
        <router-link to="/community" :class="['nav-link', { active: $route.path === '/community' }]">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span>Cộng đồng</span>
        </router-link>
        
        <router-link to="/vip" :class="['nav-link', { active: $route.path === '/vip' }]">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/>
            <path d="M5 21h14"/>
          </svg>
          <span>VIP</span>
        </router-link>
      </nav>
      
      <div class="header-actions">
        <button class="icon-btn" title="Thông báo">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
          </svg>
        </button>
        <router-link to="/profile">
          <button class="icon-btn" title="Tài khoản">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </button>
        </router-link>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  position: sticky;
  top: 0;
  z-index: 100;
  height: var(--nav-height);
  background: rgba(9, 9, 11, 0.85);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
}

.header-inner {
  display: flex;
  align-items: center;
  width: 100%;
  gap: 8px;
}

.nav {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  gap: 2px;
  min-width: 0;
}

.nav-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  flex: 1;
  padding: 6px 8px;
  border-radius: var(--radius-sm);
  font-size: 11px;
  font-weight: 500;
  color: var(--text-muted);
  transition: all 0.2s;
  white-space: nowrap;
}

@media (min-width: 640px) {
  .nav-link {
    flex-direction: row;
    flex: unset;
    gap: 6px;
    padding: 6px 12px;
    font-size: 14px;
  }
}

.nav-link svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

@media (min-width: 640px) {
  .nav-link svg {
    width: 16px;
    height: 16px;
  }
}

.nav-link:hover {
  color: var(--text);
  background: var(--bg-muted);
}

.nav-link.active {
  background: var(--primary-light);
  color: var(--primary);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  margin-left: auto;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: all 0.2s;
}

.icon-btn:hover {
  background: var(--bg-muted);
  color: var(--text);
}

.icon-btn svg {
  width: 20px;
  height: 20px;
}
</style>

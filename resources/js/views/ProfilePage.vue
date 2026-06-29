<template>
  <div class="profile-page">
    <div class="container">

      <!-- GUEST STATE -->
      <div v-if="!auth.isAuthenticated" class="guest-card animate-in">
        <div class="guest-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h2 class="guest-title">Chưa đăng nhập</h2>
        <p class="guest-subtitle">Đăng nhập để theo dõi truyện, lưu tiến trình nghe và nhiều hơn nữa.</p>
        <div class="guest-actions">
          <router-link to="/auth" class="btn btn-primary">Đăng nhập</router-link>
          <router-link to="/auth?tab=register" class="btn btn-outline">Tạo tài khoản mới</router-link>
        </div>
      </div>

      <template v-else>
      <div class="page-header">
        <h1 class="page-title">Tài khoản</h1>
        <p class="page-subtitle">Quản lý hồ sơ và cài đặt của bạn</p>
      </div>

      <!-- PROFILE CARD -->
      <div class="card profile-section animate-in">
        <div class="profile-card">
          <div class="profile-avatar-row">
            <div class="profile-avatar">{{ auth.avatarInitial }}</div>
            <div style="flex:1;min-width:0;">
              <div class="profile-name">{{ auth.displayName }}</div>
              <div class="profile-email">{{ auth.user?.email }}</div>
              <div style="margin-top:6px;">
                <span v-if="auth.isPremium" class="badge badge-gradient" style="font-size:11px;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                  Thành viên VIP
                </span>
                <span v-else class="badge" style="font-size:11px;background:var(--bg-muted);color:var(--text-muted);">
                  Tài khoản thường
                </span>
              </div>
            </div>
            <button class="btn btn-outline btn-sm">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              Sửa
            </button>
          </div>

          <!-- Stats -->
          <div class="stat-grid">
            <div class="stat-cell">
              <div class="stat-value text-gradient">127</div>
              <div class="stat-label">Đã nghe</div>
            </div>
            <div class="stat-cell">
              <div class="stat-value text-gradient">43</div>
              <div class="stat-label">Đang theo dõi</div>
            </div>
            <div class="stat-cell">
              <div class="stat-value text-gradient">286h</div>
              <div class="stat-label">Thời gian nghe</div>
            </div>
          </div>
        </div>
      </div>

      <!-- VIP STATUS -->
      <div v-if="auth.isPremium" class="vip-status-card animate-in">
        <div class="vip-status-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="vip-status-label">Gói hiện tại</div>
          <div class="vip-status-value">{{ subscriptionPlanName }}</div>
          <div class="vip-status-expire">{{ subscriptionExpireText }}</div>
        </div>
        <router-link to="/vip" class="vip-renew-btn">Gia hạn</router-link>
      </div>
      <div v-else class="vip-status-card animate-in" style="border-color:rgba(168,85,247,0.2);">
        <div class="vip-status-icon" style="background:rgba(168,85,247,0.12);color:var(--primary);">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="vip-status-label">Gói hiện tại</div>
          <div class="vip-status-value">Tài khoản thường</div>
          <div class="vip-status-expire">Nâng cấp VIP để nghe không giới hạn</div>
        </div>
        <router-link to="/vip" class="vip-renew-btn">Đăng ký VIP</router-link>
      </div>

      <!-- LISTENING HISTORY -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <div style="padding:16px 16px 8px;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:15px;font-weight:700;">🕐 Nghe gần đây</h2>
            <a href="#" style="font-size:12px;color:var(--primary);">Xem tất cả</a>
          </div>

          <div v-for="item in history" :key="item.id" class="history-item" @click="$router.push('/story/1')">
            <div class="history-thumb"><img :src="item.image" :alt="item.title"></div>
            <div class="history-info">
              <div class="history-title">{{ item.title }}</div>
              <div class="history-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ item.meta }}
              </div>
              <div class="history-progress-bar"><div class="history-progress-fill" :style="{ width: item.progress + '%' }"></div></div>
            </div>
            <button class="icon-btn" style="flex-shrink:0;">
              <svg v-if="item.progress < 100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--primary)" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.14"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- BOOKMARKS -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <div style="padding:16px 16px 8px;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:15px;font-weight:700;">🔖 Truyện đang theo dõi</h2>
            <a href="#" style="font-size:12px;color:var(--primary);">Xem tất cả</a>
          </div>
          <div class="story-scroll" style="padding:0 16px 16px;">
            <router-link v-for="bm in bookmarks" :key="bm.id" to="/story/1" class="story-card" style="width:120px;flex-shrink:0;">
              <div class="story-card-thumb">
                <img :src="bm.image" :alt="bm.title">
                <div class="story-card-overlay"></div>
                <div class="story-card-status"><span :class="['badge', bm.completed ? 'badge-success' : '']" style="font-size:10px;padding:1px 6px;">{{ bm.completed ? 'Hoàn' : 'Cập nhật' }}</span></div>
              </div>
              <div class="story-card-title">{{ bm.title }}</div>
              <div class="story-card-meta" style="font-size:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                {{ bm.eps }} tập
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- MENU ITEMS -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <button v-for="(item, idx) in menuItems" :key="item.title" class="profile-menu-item" style="width:100%;">
            <div class="profile-menu-icon" :style="item.iconStyle || ''" v-html="item.icon"></div>
            <div class="profile-menu-text">
              <div class="profile-menu-title">{{ item.title }}</div>
              <div class="profile-menu-sub">{{ item.sub }}</div>
            </div>
            <div class="profile-menu-chevron">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </div>
          </button>
        </div>
      </div>

      <!-- LOGOUT -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <button class="profile-menu-item danger" style="width:100%;" :disabled="auth.isBusy" @click="showLogoutConfirm = true">
            <div class="profile-menu-icon danger">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </div>
            <div class="profile-menu-text">
              <div class="profile-menu-title">Đăng xuất</div>
            </div>
          </button>
        </div>
      </div>

      <!-- App version -->
      <div style="text-align:center;padding:8px 0 16px;font-size:11px;color:var(--text-faint);">
        Truyện Audio v2.4.1 · <a href="#" style="color:var(--text-faint);text-decoration:underline;">Điều khoản</a> · <a href="#" style="color:var(--text-faint);text-decoration:underline;">Chính sách</a>
      </div>

      </template>

    </div>

    <ConfirmDialog
      v-model="showLogoutConfirm"
      variant="danger"
      icon="logout"
      title="Đăng xuất"
      message="Bạn có chắc muốn đăng xuất?"
      confirm-text="Đăng xuất"
      cancel-text="Huỷ"
      @confirm="confirmLogout"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const auth = useAuthStore()
const router = useRouter()
const showLogoutConfirm = ref(false)

const subscriptionPlanName = computed(() =>
  auth.user?.subscription?.plan_name ?? 'Gói VIP'
)

const subscriptionExpireText = computed(() => {
  const endAt = auth.user?.subscription?.end_at
  if (!endAt) return ''

  const end = new Date(endAt)
  if (Number.isNaN(end.getTime())) return ''

  const pad = (n) => String(n).padStart(2, '0')
  const formatted = `${pad(end.getDate())}/${pad(end.getMonth() + 1)}/${end.getFullYear()}`
  const daysLeft = Math.max(0, Math.ceil((end.getTime() - Date.now()) / 86400000))

  return `Hết hạn: ${formatted} · Còn ${daysLeft} ngày`
})
const history = [
  { id: 1, title: 'Nuôi Vợ Hào Môn Mà Không Hay Biết', image: 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=100&q=80', meta: 'Tập 6 · Còn 32 phút', progress: 74 },
  { id: 2, title: 'Vợ Tôi Giữ Gìn Cho Tình Đầu Sắp Trở Về', image: 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=100&q=80', meta: 'Tập 2 · Còn 58 phút', progress: 22 },
  { id: 3, title: 'Chọn Vợ Giữa Chốn Hào Môn', image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100&q=80', meta: 'Tập 12 · Hoàn thành', progress: 100 },
]

const bookmarks = [
  { id: 1, title: 'Nuôi Vợ Hào Môn', image: 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=200&q=80', completed: true, eps: 43 },
  { id: 2, title: 'Vợ Tôi Giữ Gìn', image: 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=200&q=80', completed: false, eps: 32 },
  { id: 3, title: 'Năm Nhất Đại Học', image: 'https://images.unsplash.com/photo-1495364141860-b0d03eccd065?w=200&q=80', completed: true, eps: 23 },
]

const menuItems = [
  {
    title: 'Thông tin cá nhân', sub: 'Cập nhật tên, email, mật khẩu',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
  },
  {
    title: 'Gói VIP của tôi', sub: 'Quản lý và gia hạn gói VIP',
    iconStyle: 'background:rgba(168,85,247,0.1);',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>'
  },
  {
    title: 'Thông báo', sub: 'Cài đặt thông báo tập mới, khuyến mãi',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>'
  },
  {
    title: 'Cài đặt chung', sub: 'Tốc độ nghe, chất lượng âm thanh',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>'
  },
  {
    title: 'Hỗ trợ & Liên hệ', sub: 'Gửi yêu cầu hỗ trợ, phản hồi',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>'
  },
]

const confirmLogout = async () => {
  await auth.runLogoutFlow(router, '/')
}
</script>

<style scoped>
.profile-page { min-height: 100vh; }
.container { max-width: 540px; margin: 0 auto; padding: 24px 16px; }
@media (min-width: 640px) { .container { padding: 32px 24px; } }

.guest-card {
  margin-top: 48px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 32px 24px;
  text-align: center;
}
.guest-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: var(--bg-muted);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
}
.guest-icon svg { width: 34px; height: 34px; }
.guest-title { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
.guest-subtitle { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; }
.guest-actions { display: flex; flex-direction: column; gap: 10px; }
.guest-actions .btn {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 46px;
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
}
.guest-actions .btn-primary { background: var(--gradient-premium); color: #fff; }
.guest-actions .btn-outline {
  background: transparent;
  border: 1px solid var(--border-strong);
  color: var(--text);
}
.guest-actions .btn-outline:hover { background: var(--bg-muted); }

.page-header { margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.animate-in { animation: slideUp 0.5s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

.profile-section { margin-bottom: 20px; }
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }
.profile-card { padding: 16px; }

.profile-avatar-row { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
.profile-avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--gradient-premium); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; flex-shrink: 0; }
.profile-name { font-size: 16px; font-weight: 700; margin-bottom: 3px; }
.profile-email { font-size: 13px; color: var(--text-muted); }

.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: var(--radius-full); font-size: 12px; font-weight: 600; background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border); }
.badge-gradient { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }
.badge-success { background: rgba(34,197,94,0.1); color: var(--success); border-color: var(--success-border); }

.stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--border); border-radius: var(--radius-md); overflow: hidden; }
.stat-cell { background: var(--bg-card); padding: 14px 8px; text-align: center; }
.stat-value { font-size: 20px; font-weight: 700; margin-bottom: 2px; }
.stat-label { font-size: 11px; color: var(--text-muted); }
.text-gradient { background: var(--gradient-premium); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* VIP status */
.vip-status-card { background: linear-gradient(135deg, rgba(168,85,247,0.12), rgba(236,72,153,0.08)); border: 1px solid rgba(168,85,247,0.3); border-radius: var(--radius-lg); padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.vip-status-icon { width: 48px; height: 48px; border-radius: 50%; background: var(--gradient-premium); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vip-status-icon svg { width: 22px; height: 22px; stroke: #fff; }
.vip-status-label { font-size: 12px; color: var(--text-muted); margin-bottom: 3px; }
.vip-status-value { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
.vip-status-expire { font-size: 11px; color: var(--text-muted); }
.vip-renew-btn { margin-left: auto; padding: 6px 14px; border-radius: var(--radius-full); background: var(--gradient-premium); color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; flex-shrink: 0; cursor: pointer; text-decoration: none; }

/* History */
.history-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; transition: background 0.2s; cursor: pointer; border-bottom: 1px solid var(--border); }
.history-item:last-child { border-bottom: none; }
.history-item:hover { background: var(--bg-muted); }
.history-thumb { width: 44px; height: 44px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; }
.history-thumb img { width: 100%; height: 100%; object-fit: cover; }
.history-info { flex: 1; min-width: 0; }
.history-title { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.history-meta { font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.history-progress-bar { height: 3px; border-radius: 2px; background: var(--border); margin-top: 5px; }
.history-progress-fill { height: 100%; border-radius: 2px; background: var(--gradient-premium); }
.icon-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); transition: all 0.2s; cursor: pointer; }
.icon-btn svg { width: 16px; height: 16px; }
.icon-btn:hover { background: var(--bg-muted); }

/* Story scroll bookmark */
.story-scroll { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 4px; -webkit-overflow-scrolling: touch; }
.story-card { display: flex; flex-direction: column; gap: 6px; text-decoration: none; transition: transform 0.2s; }
.story-card:hover { transform: translateY(-3px); }
.story-card-thumb { position: relative; aspect-ratio: 3/4; border-radius: var(--radius-sm); overflow: hidden; }
.story-card-thumb img { width: 100%; height: 100%; object-fit: cover; }
.story-card-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.7)); }
.story-card-status { position: absolute; top: 6px; left: 6px; }
.story-card-title { font-size: 12px; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.story-card:hover .story-card-title { color: var(--primary); }
.story-card-meta { display: flex; align-items: center; gap: 4px; font-size: 10px; color: var(--text-muted); }
.story-card-meta svg { width: 10px; height: 10px; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); font-weight: 500; transition: all 0.2s; cursor: pointer; font-family: inherit; }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); padding: 6px 14px; font-size: 13px; }
.btn-outline:hover { background: var(--bg-muted); }
.btn-sm { height: 34px; padding: 0 14px; font-size: 13px; }

/* Menu items */
.profile-menu-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; transition: background 0.2s; cursor: pointer; border-bottom: 1px solid var(--border); background: transparent; text-align: left; }
.profile-menu-item:last-child { border-bottom: none; }
.profile-menu-item:hover { background: var(--bg-muted); }
.profile-menu-item.danger:hover { background: rgba(239,68,68,0.06); }
.profile-menu-icon { width: 36px; height: 36px; border-radius: var(--radius-sm); background: var(--bg-muted); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.profile-menu-icon :deep(svg) { width: 18px; height: 18px; }
.profile-menu-icon.danger { background: rgba(239,68,68,0.1); }
.profile-menu-icon.danger :deep(svg) { stroke: var(--red); }
.profile-menu-text { flex: 1; min-width: 0; }
.profile-menu-title { font-size: 14px; font-weight: 500; }
.profile-menu-item.danger .profile-menu-title { color: var(--red); }
.profile-menu-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.profile-menu-chevron svg { width: 16px; height: 16px; color: var(--text-faint); }
</style>

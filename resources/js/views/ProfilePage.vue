<template>
  <div class="profile-page">
    <div class="container">

      <div class="page-header">
        <h1 class="page-title">Tài khoản</h1>
        <p class="page-subtitle">Quản lý hồ sơ và cài đặt của bạn</p>
      </div>

      <!-- PROFILE CARD -->
      <div class="card profile-section animate-in">
        <div class="profile-card">

          <!-- Edit form -->
          <template v-if="editing">
            <div class="edit-form">
              <div class="edit-avatar-row">
                <div class="profile-avatar">
                  <img v-if="editAvatarUrl" :src="editAvatarUrl" alt="avatar" class="avatar-img" @error="editAvatarUrl = ''" />
                  <span v-else>{{ auth.avatarInitial }}</span>
                </div>
                <!-- <div class="edit-avatar-input">
                  <label class="field-label">URL ảnh đại diện (tuỳ chọn)</label>
                  <input
                    v-model="editAvatarUrl"
                    type="url"
                    placeholder="https://..."
                    class="text-input"
                    :disabled="saving"
                  />
                </div> -->
              </div>

              <div class="edit-field">
                <label class="field-label">Tên hiển thị</label>
                <input
                  v-model="editUsername"
                  type="text"
                  placeholder="Tên của bạn"
                  maxlength="255"
                  class="text-input"
                  :disabled="saving"
                  @keyup.enter="saveProfile"
                />
              </div>

              <div class="edit-field">
                <label class="field-label">Email</label>
                <input
                  :value="auth.user?.email"
                  type="email"
                  class="text-input"
                  disabled
                />
                <span class="field-hint">Email không thể thay đổi</span>
              </div>

              <div class="edit-actions">
                <button class="btn btn-outline btn-sm" :disabled="saving" @click="cancelEdit">Huỷ</button>
                <button class="btn btn-primary btn-sm" :disabled="saving || !editUsername.trim()" @click="saveProfile">
                  <ButtonSpinner v-if="saving" variant="light" :size="13" />
                  <span v-else>Lưu thay đổi</span>
                </button>
              </div>
            </div>
          </template>

          <!-- Normal view -->
          <template v-else>
            <div class="profile-avatar-row">
              <div class="profile-avatar">
                <img v-if="auth.user?.avatar_url && !avatarBroken" :src="auth.user.avatar_url" alt="avatar" class="avatar-img" @error="avatarBroken = true" />
                <span v-else>{{ auth.avatarInitial }}</span>
              </div>
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
              <!-- <button class="btn btn-outline btn-sm" @click="startEdit">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                Sửa
              </button> -->
            </div>
          </template>

          <!-- Stats -->
          <div class="stat-grid">
            <div class="stat-cell">
              <div class="stat-value text-gradient">{{ stats.episodes_listened ?? '—' }}</div>
              <div class="stat-label">Đã nghe</div>
            </div>
            <div class="stat-cell">
              <div class="stat-value text-gradient">{{ stats.follow_count ?? '—' }}</div>
              <div class="stat-label">Đang theo dõi</div>
            </div>
            <div class="stat-cell">
              <div class="stat-value text-gradient">{{ totalListenHours }}</div>
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
      <div v-else class="vip-status-card animate-in" style="border-color:var(--primary-light-border);">
        <div class="vip-status-icon" style="background:var(--primary-light);color:var(--primary);">
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
          <div class="section-header">
            <h2 class="section-title">🕐 Nghe gần đây</h2>
            <router-link to="/history" class="section-link">Xem tất cả</router-link>
          </div>

          <div v-if="historyLoading" class="history-loading">
            <div v-for="n in 3" :key="n" class="history-skeleton">
              <div class="skeleton-thumb"></div>
              <div class="skeleton-body">
                <div class="skeleton-line w-3/4"></div>
                <div class="skeleton-line w-1/2"></div>
                <div class="skeleton-bar"></div>
              </div>
            </div>
          </div>

          <div v-else-if="!history.length" class="section-empty">
            Chưa có lịch sử nghe nào.
          </div>

          <template v-else>
            <div
              v-for="item in history"
              :key="item.id"
              class="history-item"
              @click="item.series_id && $router.push(`/story/${item.series_id}`)"
            >
              <div class="history-thumb">
                <img v-if="item.cover_url" :src="item.cover_url" :alt="item.series_title" />
                <div v-else class="thumb-fallback">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                </div>
              </div>
              <div class="history-info">
                <div class="history-title">{{ item.series_title || item.episode_title }}</div>
                <div class="history-meta">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  {{ historyMeta(item) }}
                </div>
                <div class="history-progress-bar">
                  <div class="history-progress-fill" :style="{ width: item.progress + '%' }"></div>
                </div>
              </div>
              <button class="icon-btn" style="flex-shrink:0;" @click.stop="item.series_id && $router.push(`/story/${item.series_id}`)">
                <svg v-if="!item.completed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--primary)" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.14"/></svg>
              </button>
            </div>
          </template>
        </div>
      </div>

      <!-- BOOKMARKS / FOLLOWED SERIES -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <div class="section-header">
            <h2 class="section-title">🔖 Truyện đang theo dõi</h2>
            <router-link to="/follows" class="section-link">Xem tất cả</router-link>
          </div>

          <div v-if="followedLoading" class="story-scroll" style="padding:12px 16px 16px;">
            <div v-for="n in 4" :key="n" class="bookmark-skeleton"></div>
          </div>

          <div v-else-if="!followed.length" class="section-empty">
            Chưa theo dõi truyện nào. <router-link to="/" class="section-link-inline">Khám phá ngay</router-link>
          </div>

          <div v-else class="story-scroll" style="padding:0 16px 16px;">
            <router-link
              v-for="item in followed"
              :key="item.id"
              :to="`/story/${item.id}`"
              class="story-card"
              style="width:120px;flex-shrink:0;"
            >
              <div class="story-card-thumb">
                <img :src="item.cover_url || fallbackCover" :alt="item.title" />
                <div class="story-card-overlay"></div>
                <div class="story-card-status">
                  <span :class="['badge', item.is_complete ? 'badge-success' : '']" style="font-size:10px;padding:1px 6px;">
                    {{ item.is_complete ? 'Hoàn thành' : 'Đang cập nhật' }}
                  </span>
                </div>
              </div>
              <div class="story-card-title">{{ item.title }}</div>
              <div class="story-card-meta" style="font-size:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                {{ item.total_episodes || item.latest_episode_number || 0 }} tập
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- SETTINGS -->
      <div class="profile-section">
        <div class="card" style="overflow:hidden;">
          <div class="profile-menu-item settings-header">
            <div class="profile-menu-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div class="profile-menu-text">
              <div class="profile-menu-title">Cài đặt</div>
              <div class="profile-menu-sub">Giao diện, tuỳ chỉnh</div>
            </div>
          </div>

          <div class="settings-panel">

              <!-- Thông báo đẩy -->
              <div class="settings-group">
                <div class="settings-row">
                  <div class="settings-row-left">
                    <div class="settings-icon settings-icon--primary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </div>
                    <div>
                      <div class="settings-label">Thông báo đẩy</div>
                      <div class="settings-hint">Nhận thông báo khi có truyện mới cập nhật</div>
                    </div>
                  </div>
                  <div class="toggle-switch" :class="{ active: pushEnabled }" @click="pushEnabled = !pushEnabled">
                    <div class="toggle-slider"></div>
                  </div>
                </div>
                <button class="btn-test-push" style="margin-top:10px;margin-left:44px;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                  Gửi push thử
                </button>
              </div>

              <div class="settings-divider"></div>

              <!-- Giao diện -->
              <div class="settings-group">
                <div class="settings-row-left" style="margin-bottom:12px;">
                  <div class="settings-icon settings-icon--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                  </div>
                  <div>
                    <div class="settings-label" style="margin-bottom:2px;">Giao diện</div>
                    <div class="settings-hint">Chọn chế độ hiển thị</div>
                  </div>
                </div>
                <div class="theme-options">
                  <button class="theme-option" :class="{ active: theme === 'light' }" @click="applyTheme('light')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    <span>Sáng</span>
                  </button>
                  <button class="theme-option" :class="{ active: theme === 'dark' }" @click="applyTheme('dark')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <span>Tối</span>
                  </button>
                  <button class="theme-option" :class="{ active: theme === 'system' }" @click="applyTheme('system')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>Hệ thống</span>
                  </button>
                </div>
              </div>

          </div>
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
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import { useRouter } from 'vue-router'
import AuthService from '@/services/AuthService'
import { extractApiPayload, SERIES_FALLBACK_COVER } from '@/utils/helpers'
import { applyThemeToDocument } from '@/utils/theme'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()

const fallbackCover = SERIES_FALLBACK_COVER

// ── UI state ──────────────────────────────────────────────────────────────
const showLogoutConfirm = ref(false)
const pushEnabled = ref(false)
const theme = ref(localStorage.getItem('theme') || 'dark')
const avatarBroken = ref(false)

// ── Data ──────────────────────────────────────────────────────────────────
const history = ref([])
const historyLoading = ref(true)
const followed = ref([])
const followedLoading = ref(true)

// ── Stats (from auth.user.stats, populated by /api/auth/me) ───────────────
const stats = computed(() => auth.user?.stats ?? {})

const totalListenHours = computed(() => {
  const secs = stats.value.total_listen_seconds
  if (secs == null) return '—'
  const hours = Math.round(secs / 3600)
  return hours + 'h'
})

// ── Subscription ───────────────────────────────────────────────────────────
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

// ── History helpers ─────────────────────────────────────────────────────────
const historyMeta = (item) => {
  if (item.completed) return `Tập ${item.episode_number} · Hoàn thành`
  if (item.duration_seconds > 0) {
    const remaining = Math.max(0, item.duration_seconds - item.listened_seconds)
    const mins = Math.ceil(remaining / 60)
    return `Tập ${item.episode_number} · Còn ${mins} phút`
  }
  return `Tập ${item.episode_number}`
}

// ── Load data ──────────────────────────────────────────────────────────────
const loadList = async (fetcher, target, loadingRef) => {
  loadingRef.value = true
  try {
    const res = extractApiPayload(await fetcher())
    target.value = res?.items ?? []
  } catch {
    target.value = []
  } finally {
    loadingRef.value = false
  }
}

onMounted(() => {
  auth.fetchMe().catch(() => {})
  loadList(AuthService.getHistory, history, historyLoading)
  loadList(AuthService.getFollowedSeries, followed, followedLoading)
})

// ── Edit profile ───────────────────────────────────────────────────────────
const editing = ref(false)
const saving = ref(false)
const editUsername = ref('')
const editAvatarUrl = ref('')

const startEdit = () => {
  editUsername.value = auth.user?.username ?? ''
  editAvatarUrl.value = auth.user?.avatar_url ?? ''
  editing.value = true
}

const cancelEdit = () => {
  editing.value = false
}

const saveProfile = async () => {
  if (!editUsername.value.trim() || saving.value) return
  saving.value = true
  try {
    const payload = {}
    if (editUsername.value.trim() !== auth.user?.username) {
      payload.username = editUsername.value.trim()
    }
    const newAvatar = editAvatarUrl.value.trim() || null
    if (newAvatar !== (auth.user?.avatar_url ?? null)) {
      payload.avatar_url = newAvatar
    }

    if (Object.keys(payload).length === 0) {
      editing.value = false
      return
    }

    const res = extractApiPayload(await AuthService.updateProfile(payload))
    if (res) {
      auth.user = { ...auth.user, ...res }
      avatarBroken.value = false
    }
    editing.value = false
    toast.success('Cập nhật hồ sơ thành công')
  } catch (error) {
    toast.error(error.message || 'Cập nhật thất bại')
  } finally {
    saving.value = false
  }
}

// ── Logout ─────────────────────────────────────────────────────────────────
const confirmLogout = async () => {
  await auth.runLogoutFlow(router, '/')
}

// ── Theme ──────────────────────────────────────────────────────────────────
const applyTheme = (newTheme) => {
  theme.value = newTheme
  localStorage.setItem('theme', newTheme)
  applyThemeToDocument(newTheme)
}

if (theme.value === 'system') {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (theme.value === 'system') {
      applyThemeToDocument('system')
    }
  })
}

applyTheme(theme.value)
</script>

<style scoped>
.profile-page { min-height: 100vh; }
.container { max-width: 540px; margin: 0 auto; padding: 24px 16px; }
@media (min-width: 640px) { .container { padding: 32px 24px; } }

.page-header { margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.animate-in { animation: slideUp 0.4s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

.profile-section { margin-bottom: 20px; }
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }
.profile-card { padding: 16px; }

/* ── Avatar ── */
.profile-avatar-row { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
.profile-avatar {
  width: 56px; height: 56px; border-radius: 50%;
  background: var(--gradient-premium); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 22px; flex-shrink: 0; overflow: hidden;
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; }
.profile-name { font-size: 16px; font-weight: 700; margin-bottom: 3px; }
.profile-email { font-size: 13px; color: var(--text-muted); }

/* ── Edit form ── */
.edit-form { margin-bottom: 16px; }
.edit-avatar-row { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
.edit-avatar-input { flex: 1; min-width: 0; }
.edit-field { margin-bottom: 14px; }
.field-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em; }
.field-hint { font-size: 11px; color: var(--text-faint); margin-top: 4px; display: block; }
.text-input {
  width: 100%; padding: 10px 12px; border-radius: var(--radius-sm);
  border: 1px solid var(--border); background: var(--bg-muted);
  color: var(--text); font-size: 14px; font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.text-input:focus { border-color: var(--primary-focus); box-shadow: 0 0 0 3px var(--primary-light); outline: none; }
.text-input:disabled { opacity: 0.5; cursor: not-allowed; }
.edit-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }

/* ── Badges ── */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: var(--radius-full); font-size: 12px; font-weight: 600; background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border); }
.badge-gradient { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }
.badge-success { background: rgba(34,197,94,0.1); color: var(--success); border-color: var(--success-border); }

/* ── Stats ── */
.stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--border); border-radius: var(--radius-md); overflow: hidden; }
.stat-cell { background: var(--bg-card); padding: 14px 8px; text-align: center; }
.stat-value { font-size: 20px; font-weight: 700; margin-bottom: 2px; }
.stat-label { font-size: 11px; color: var(--text-muted); }
.text-gradient { background: var(--gradient-premium); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* ── VIP card ── */
.vip-status-card { background: linear-gradient(135deg, rgba(168,85,247,0.12), rgba(236,72,153,0.08)); border: 1px solid rgba(168,85,247,0.3); border-radius: var(--radius-lg); padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.vip-status-icon { width: 48px; height: 48px; border-radius: 50%; background: var(--gradient-premium); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vip-status-icon svg { width: 22px; height: 22px; stroke: #fff; }
.vip-status-label { font-size: 12px; color: var(--text-muted); margin-bottom: 3px; }
.vip-status-value { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
.vip-status-expire { font-size: 11px; color: var(--text-muted); }
.vip-renew-btn { margin-left: auto; padding: 6px 14px; border-radius: var(--radius-full); background: var(--gradient-premium); color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; flex-shrink: 0; cursor: pointer; text-decoration: none; }

/* ── Section header ── */
.section-header { padding: 16px 16px 8px; display: flex; align-items: center; justify-content: space-between; }
.section-title { font-size: 15px; font-weight: 700; margin: 0; }
.section-link { font-size: 12px; color: var(--primary); text-decoration: none; }
.section-link:hover { text-decoration: underline; }
.section-link-inline { color: var(--primary); text-decoration: underline; }
.section-empty { padding: 20px 16px; font-size: 13px; color: var(--text-muted); text-align: center; }

/* ── History items ── */
.history-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; transition: background 0.2s; cursor: pointer; border-bottom: 1px solid var(--border); }
.history-item:last-child { border-bottom: none; }
.history-item:hover { background: var(--bg-muted); }
.history-thumb { width: 44px; height: 44px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; background: var(--bg-muted); }
.history-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-faint); }
.thumb-fallback svg { width: 20px; height: 20px; }
.history-info { flex: 1; min-width: 0; }
.history-title { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.history-meta { font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.history-progress-bar { height: 3px; border-radius: 2px; background: var(--border); margin-top: 5px; }
.history-progress-fill { height: 100%; border-radius: 2px; background: var(--gradient-premium); transition: width 0.3s; }

/* ── Story scroll / bookmarks ── */
.story-scroll { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 4px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
.story-scroll::-webkit-scrollbar { display: none; }
.story-card { display: flex; flex-direction: column; gap: 6px; text-decoration: none; transition: transform 0.2s; }
.story-card:hover { transform: translateY(-3px); }
.story-card-thumb { position: relative; aspect-ratio: 3/4; border-radius: var(--radius-sm); overflow: hidden; }
.story-card-thumb img { width: 100%; height: 100%; object-fit: cover; }
.story-card-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 55%, rgba(0,0,0,0.88)); }
.story-card-status { position: absolute; bottom: 8px; left: 8px; z-index: 1; }
.story-card-title { font-size: 12px; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.story-card:hover .story-card-title { color: var(--primary); }
.story-card-meta { display: flex; align-items: center; gap: 4px; font-size: 10px; color: var(--text-muted); }
.story-card-meta svg { width: 10px; height: 10px; }

/* ── Skeleton loaders ── */
.history-loading { padding: 8px 0; }
.history-skeleton { display: flex; align-items: center; gap: 12px; padding: 10px 16px; }
.skeleton-thumb { width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--bg-muted); flex-shrink: 0; animation: pulse 1.5s ease-in-out infinite; }
.skeleton-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.skeleton-line { height: 10px; border-radius: 6px; background: var(--bg-muted); animation: pulse 1.5s ease-in-out infinite; }
.skeleton-line.w-3\/4 { width: 75%; }
.skeleton-line.w-1\/2 { width: 50%; }
.skeleton-bar { height: 3px; border-radius: 2px; background: var(--bg-muted); animation: pulse 1.5s ease-in-out infinite; }
.bookmark-skeleton { width: 120px; height: 170px; border-radius: var(--radius-sm); background: var(--bg-muted); flex-shrink: 0; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }

/* ── Icon btn ── */
.icon-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); transition: background 0.2s; cursor: pointer; background: none; border: none; }
.icon-btn svg { width: 16px; height: 16px; }
.icon-btn:hover { background: var(--bg-muted); }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); font-weight: 500; transition: all 0.2s; cursor: pointer; font-family: inherit; }
.btn-primary { background: var(--gradient-premium); color: #fff; border: none; }
.btn-primary:hover:not(:disabled) { opacity: 0.88; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); }
.btn-outline:hover { background: var(--bg-muted); }
.btn-sm { height: 34px; padding: 0 14px; font-size: 13px; }

/* ── Settings panel (inline accordion) ── */
.settings-panel { padding: 4px 0; border-top: 1px solid var(--border); }
.settings-group { padding: 14px 16px; }
.settings-divider { height: 1px; background: var(--border); }
.settings-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.settings-row-left { display: flex; align-items: flex-start; gap: 12px; flex: 1; min-width: 0; }
.settings-icon { width: 36px; height: 36px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.settings-icon--primary { background: var(--bg-muted); color: var(--primary); }
.settings-label { font-size: 14px; font-weight: 600; line-height: 1.3; }
.settings-hint { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ── Menu ── */
.settings-header { cursor: default; }
.settings-header:hover { background: transparent; }
.profile-menu-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; transition: background 0.2s; cursor: pointer; border-bottom: 1px solid var(--border); background: transparent; text-align: left; }
.profile-menu-item:last-of-type { border-bottom: none; }
.profile-menu-item:hover { background: var(--bg-muted); }
.profile-menu-item.danger:hover { background: rgba(239,68,68,0.06); }
.profile-menu-icon { width: 36px; height: 36px; border-radius: var(--radius-sm); background: var(--bg-muted); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.profile-menu-icon :deep(svg) { width: 18px; height: 18px; }
.profile-menu-icon.danger { background: rgba(239,68,68,0.1); }
.profile-menu-icon.danger :deep(svg) { stroke: var(--red); }
.profile-menu-text { flex: 1; min-width: 0; }
.profile-menu-title { font-size: 14px; font-weight: 500; color: var(--text); }
.profile-menu-item.danger .profile-menu-title { color: var(--red); }
.profile-menu-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ── Theme options ── */
.theme-options { display: flex; gap: 8px; }
.theme-option { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: var(--radius-sm); background: var(--bg-muted); border: 2px solid transparent; color: var(--text-muted); font-size: 12px; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.theme-option:hover { background: var(--border); }
.theme-option.active { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.theme-option svg { width: 20px; height: 20px; }
/* Toggle switch */
.toggle-switch {
  width: 44px;
  height: 24px;
  border-radius: 12px;
  background: var(--border);
  position: relative;
  cursor: pointer;
  transition: background 0.3s;
}
.toggle-switch.active {
  background: var(--primary);
}
.toggle-slider {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: transform 0.3s;
}
.toggle-switch.active .toggle-slider {
  transform: translateX(20px);
}

/* Test push button */
.btn-test-push {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  background: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-test-push:hover {
  background: var(--border);
}
</style>

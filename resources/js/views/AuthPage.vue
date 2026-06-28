<template>
  <div class="auth-page" :class="{ 'auth-page-busy': auth.transitioning }">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">T</div>
        <h1 class="auth-title">Truyện Audio</h1>
        <p class="auth-subtitle">Đăng nhập để theo dõi truyện và lưu tiến trình nghe</p>
      </div>

      <div class="auth-tabs">
        <button
          type="button"
          :class="['auth-tab', { active: tab === 'login' }]"
          @click="switchTab('login')"
        >
          Đăng nhập
        </button>
        <button
          type="button"
          :class="['auth-tab', { active: tab === 'register' }]"
          @click="switchTab('register')"
        >
          Đăng ký
        </button>
      </div>

      <p v-if="errorMessage" class="auth-error">{{ errorMessage }}</p>

      <form v-if="tab === 'login'" class="auth-form" @submit.prevent="handleLogin">
        <label class="field">
          <span class="field-label">Email</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input v-model="loginForm.email" type="email" placeholder="email@example.com" autocomplete="email" required />
          </div>
        </label>

        <label class="field">
          <span class="field-label">Mật khẩu</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              v-model="loginForm.password"
              :type="showLoginPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            />
            <button type="button" class="field-toggle" @click="showLoginPassword = !showLoginPassword">
              <svg v-if="showLoginPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
        </label>

        <button type="button" class="forgot-link">Quên mật khẩu?</button>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          {{ auth.isBusy ? 'Đang xử lý...' : 'Đăng nhập' }}
        </button>
      </form>

      <form v-else class="auth-form" @submit.prevent="handleRegister">
        <label class="field">
          <span class="field-label">Họ tên</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input v-model="registerForm.username" type="text" placeholder="Nguyễn Văn A" autocomplete="name" required />
          </div>
        </label>

        <label class="field">
          <span class="field-label">Email</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input v-model="registerForm.email" type="email" placeholder="email@example.com" autocomplete="email" required />
          </div>
        </label>

        <label class="field">
          <span class="field-label">Mật khẩu</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              v-model="registerForm.password"
              :type="showRegisterPassword ? 'text' : 'password'"
              placeholder="Mật khẩu mạnh"
              autocomplete="new-password"
              required
              minlength="6"
            />
            <button type="button" class="field-toggle" @click="showRegisterPassword = !showRegisterPassword">
              <svg v-if="showRegisterPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
        </label>

        <label class="field">
          <span class="field-label">Xác nhận mật khẩu</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              v-model="registerForm.passwordConfirm"
              :type="showRegisterConfirm ? 'text' : 'password'"
              placeholder="Nhập lại mật khẩu"
              autocomplete="new-password"
              required
              minlength="6"
            />
            <button type="button" class="field-toggle" @click="showRegisterConfirm = !showRegisterConfirm">
              <svg v-if="showRegisterConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
        </label>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          {{ auth.isBusy ? 'Đang xử lý...' : 'Đăng ký' }}
        </button>
      </form>

      <div class="auth-divider"><span>HOẶC</span></div>

      <div class="google-wrap">
        <div v-if="googleClientId" class="google-btn-shell">
          <button type="button" class="btn btn-google" tabindex="-1" aria-hidden="true">
            <GoogleIcon />
            Đăng nhập với Google
          </button>
          <div ref="googleButtonRef" class="google-button-overlay" aria-label="Đăng nhập với Google"></div>
        </div>
        <button
          v-else
          type="button"
          class="btn btn-google"
          disabled
          title="Chưa cấu hình VITE_GOOGLE_CLIENT_ID"
        >
          <GoogleIcon />
          Đăng nhập với Google
        </button>
      </div>

      <router-link to="/" class="auth-back">← Về trang chủ</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useGoogleAuth } from '@/composables/useGoogleAuth'
import GoogleIcon from '@/components/GoogleIcon.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const tab = ref(route.query.tab === 'register' ? 'register' : 'login')
const errorMessage = ref('')

const showLoginPassword = ref(false)
const showRegisterPassword = ref(false)
const showRegisterConfirm = ref(false)

const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ username: '', email: '', password: '', passwordConfirm: '' })

const redirectPath = computed(() => (
  typeof route.query.redirect === 'string' ? route.query.redirect : '/'
))

const runAuthAction = async (action, toastMessage) => {
  errorMessage.value = ''
  try {
    await auth.runAuthenticatedFlow(router, {
      action,
      redirect: redirectPath.value,
      toastMessage,
    })
  } catch (error) {
    errorMessage.value = error.message || 'Thao tác thất bại'
  }
}

const handleGoogleCredential = (response) => runAuthAction(
  () => auth.loginWithGoogle(response.credential)
)

const { buttonRef: googleButtonRef, clientId: googleClientId, mount: mountGoogleButton } = useGoogleAuth(handleGoogleCredential)

const switchTab = (nextTab) => {
  tab.value = nextTab
  errorMessage.value = ''
}

const handleLogin = () => runAuthAction(
  () => auth.login(loginForm.value.email.trim(), loginForm.value.password)
)

const handleRegister = () => {
  if (registerForm.value.password !== registerForm.value.passwordConfirm) {
    errorMessage.value = 'Mật khẩu xác nhận không khớp.'
    return
  }

  runAuthAction(
    () => auth.register(registerForm.value),
    'Đăng ký thành công!'
  )
}

onMounted(async () => {
  if (auth.isAuthenticated) {
    auth.startTransition()
    await auth.completeNavigation(router, redirectPath.value)
    return
  }

  await mountGoogleButton()
})

watch(() => route.query.tab, (value) => {
  tab.value = value === 'register' ? 'register' : 'login'
})
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
}

.auth-page-busy {
  pointer-events: none;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 28px 24px 24px;
  box-shadow: var(--shadow-lg);
}

.auth-brand {
  text-align: center;
  margin-bottom: 24px;
}

.auth-logo {
  width: 44px;
  height: 44px;
  margin: 0 auto 12px;
  border-radius: var(--radius-sm);
  background: var(--gradient-premium);
  color: #09090b;
  font-weight: 800;
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-title {
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 6px;
}

.auth-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.5;
}

.auth-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
  padding: 4px;
  background: var(--bg-muted);
  border-radius: var(--radius-sm);
  margin-bottom: 20px;
}

.auth-tab {
  padding: 10px 12px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-muted);
  transition: all 0.2s;
}

.auth-tab.active {
  background: var(--bg-card);
  color: var(--text);
  box-shadow: var(--shadow-sm);
}

.auth-error {
  margin-bottom: 14px;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  color: #fca5a5;
  font-size: 13px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-size: 13px;
  font-weight: 500;
}

.field-input-wrap {
  position: relative;
}

.field-input-wrap input {
  width: 100%;
  height: 44px;
  padding: 0 40px 0 40px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 14px;
}

.field-input-wrap input::placeholder {
  color: var(--text-faint);
}

.field-input-wrap input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-light);
}

.field-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-faint);
  pointer-events: none;
}

.field-toggle {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-faint);
  border-radius: 6px;
}

.field-toggle:hover {
  color: var(--text-muted);
  background: var(--bg-card);
}

.field-toggle svg {
  width: 16px;
  height: 16px;
}

.forgot-link {
  align-self: flex-start;
  margin-top: -4px;
  font-size: 13px;
  color: var(--primary);
}

.forgot-link:hover {
  color: var(--primary-hover);
}

.btn {
  width: 100%;
  height: 46px;
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 700;
  transition: opacity 0.2s;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--gradient-premium);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.92;
}

.auth-divider {
  position: relative;
  text-align: center;
  margin: 20px 0 16px;
}

.auth-divider::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  top: 50%;
  border-top: 1px solid var(--border);
}

.auth-divider span {
  position: relative;
  padding: 0 12px;
  background: var(--bg-card);
  color: var(--text-faint);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.08em;
}

.google-wrap {
  width: 100%;
}

.google-btn-shell {
  position: relative;
  width: 100%;
  height: 46px;
}

.google-button-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  overflow: hidden;
  opacity: 0.01;
  cursor: pointer;
}

.google-button-overlay :deep(iframe) {
  width: 100% !important;
  height: 46px !important;
  margin: 0 !important;
  pointer-events: auto;
}

.btn-google {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  height: 46px;
  background: var(--bg-muted);
  border: 1px solid var(--border-strong);
  color: var(--text);
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 600;
  pointer-events: none;
}

.btn-google:not(:disabled):hover {
  background: var(--bg-card);
  border-color: var(--border);
}

.google-icon,
.btn-google :deep(.google-icon) {
  flex-shrink: 0;
}

.auth-back {
  display: block;
  margin-top: 20px;
  text-align: center;
  font-size: 13px;
  color: var(--text-muted);
}

.auth-back:hover {
  color: var(--text);
}
</style>

<template>
  <div class="auth-page" :class="{ 'auth-page-busy': auth.transitioning }">
    <div class="auth-card">
      <div class="auth-brand">
        <img :src="logoImg" alt="Truyện Audio Hay" class="auth-logo-img" />
        <h1 class="auth-title">Truyện Audio Hay</h1>
        <p class="auth-subtitle">
          <template v-if="tab === 'forgot'">Nhập email tài khoản để nhận mã OTP</template>
          <template v-else-if="tab === 'verify-otp'">Nhập mã OTP 6 chữ số được gửi tới email</template>
          <template v-else-if="tab === 'reset'">Nhập mật khẩu mới cho tài khoản của bạn</template>
          <template v-else>Đăng nhập để theo dõi truyện và lưu tiến trình nghe</template>
        </p>
      </div>

      <div v-if="tab === 'login' || tab === 'register'" class="auth-tabs">
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
      <div v-else class="auth-step-header">
        <button type="button" class="back-text-btn" @click="switchTab('login')">
          ← Quay lại Đăng nhập
        </button>
      </div>

      <p v-if="errorMessage" class="auth-error">{{ errorMessage }}</p>

      <!-- Form Đăng nhập -->
      <form v-if="tab === 'login'" class="auth-form" novalidate @submit.prevent="handleLogin">
        <label class="field">
          <span class="field-label">Email</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input
              v-model="loginForm.email"
              type="email"
              placeholder="email@example.com"
              autocomplete="email"
              :class="{ 'is-invalid': loginErrors.email }"
              @blur="onLoginBlur('email')"
              @input="onLoginInput('email')"
            />
          </div>
          <span v-if="loginErrors.email" class="field-error">{{ loginErrors.email }}</span>
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
              :class="{ 'is-invalid': loginErrors.password }"
              @blur="onLoginBlur('password')"
              @input="onLoginInput('password')"
            />
            <button type="button" class="field-toggle" @click="showLoginPassword = !showLoginPassword">
              <svg v-if="showLoginPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span v-if="loginErrors.password" class="field-error">{{ loginErrors.password }}</span>
        </label>

        <button type="button" class="forgot-link" @click="openForgotPassword">Quên mật khẩu?</button>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
          Đăng nhập
        </button>
      </form>

      <!-- Form Đăng ký -->
      <form v-else-if="tab === 'register'" class="auth-form" novalidate @submit.prevent="handleRegister">
        <label class="field">
          <span class="field-label">Họ tên</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input
              v-model="registerForm.username"
              type="text"
              placeholder="Nguyễn Văn A"
              autocomplete="name"
              :class="{ 'is-invalid': registerErrors.username }"
              @blur="onRegisterBlur('username')"
              @input="onRegisterInput('username')"
            />
          </div>
          <span v-if="registerErrors.username" class="field-error">{{ registerErrors.username }}</span>
        </label>

        <label class="field">
          <span class="field-label">Email</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input
              v-model="registerForm.email"
              type="email"
              placeholder="email@example.com"
              autocomplete="email"
              :class="{ 'is-invalid': registerErrors.email }"
              @blur="onRegisterBlur('email')"
              @input="onRegisterInput('email')"
            />
          </div>
          <span v-if="registerErrors.email" class="field-error">{{ registerErrors.email }}</span>
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
              :class="{ 'is-invalid': registerErrors.password }"
              @blur="onRegisterBlur('password')"
              @input="onRegisterInput('password')"
            />
            <button type="button" class="field-toggle" @click="showRegisterPassword = !showRegisterPassword">
              <svg v-if="showRegisterPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span v-if="registerErrors.password" class="field-error">{{ registerErrors.password }}</span>
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
              :class="{ 'is-invalid': registerErrors.passwordConfirm }"
              @blur="onRegisterBlur('passwordConfirm')"
              @input="onRegisterInput('passwordConfirm')"
            />
            <button type="button" class="field-toggle" @click="showRegisterConfirm = !showRegisterConfirm">
              <svg v-if="showRegisterConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span v-if="registerErrors.passwordConfirm" class="field-error">{{ registerErrors.passwordConfirm }}</span>
        </label>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
          Đăng ký
        </button>
      </form>

      <!-- BƯỚC 1: Form Quên mật khẩu (Gửi OTP) -->
      <form v-else-if="tab === 'forgot'" class="auth-form" novalidate @submit.prevent="handleForgotPassword">
        <label class="field">
          <span class="field-label">Email tài khoản</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <input
              v-model="forgotForm.email"
              type="email"
              placeholder="email@example.com"
              autocomplete="email"
              :class="{ 'is-invalid': forgotErrors.email }"
              @blur="onForgotBlur('email')"
              @input="onForgotInput('email')"
            />
          </div>
          <span v-if="forgotErrors.email" class="field-error">{{ forgotErrors.email }}</span>
        </label>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
          Gửi mã OTP
        </button>
      </form>

      <!-- BƯỚC 2: Form Nhập mã OTP -->
      <form v-else-if="tab === 'verify-otp'" class="auth-form" novalidate @submit.prevent="handleVerifyOtp">
        <label class="field">
          <div class="otp-label-row">
            <span class="field-label">Mã OTP (6 chữ số)</span>
            <button
              type="button"
              class="resend-otp-btn"
              :disabled="resendCountdown > 0 || auth.isBusy"
              @click="handleResendOtp"
            >
              {{ resendCountdown > 0 ? `Gửi lại sau (${resendCountdown}s)` : 'Gửi lại mã OTP' }}
            </button>
          </div>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <input
              v-model="verifyForm.otp"
              type="text"
              maxlength="6"
              placeholder="123456"
              autocomplete="one-time-code"
              class="otp-input"
              :class="{ 'is-invalid': verifyErrors.otp }"
              @blur="onVerifyBlur('otp')"
              @input="onVerifyInput('otp')"
            />
          </div>
          <span v-if="verifyErrors.otp" class="field-error">{{ verifyErrors.otp }}</span>
        </label>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
          Xác nhận mã OTP
        </button>
      </form>

      <!-- BƯỚC 3: Form Đặt lại mật khẩu mới -->
      <form v-else-if="tab === 'reset'" class="auth-form" novalidate @submit.prevent="handleResetPassword">
        <label class="field">
          <span class="field-label">Mật khẩu mới</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              v-model="resetForm.password"
              :type="showResetPassword ? 'text' : 'password'"
              placeholder="Ít nhất 8 ký tự"
              autocomplete="new-password"
              :class="{ 'is-invalid': resetErrors.password }"
              @blur="onResetBlur('password')"
              @input="onResetInput('password')"
            />
            <button type="button" class="field-toggle" @click="showResetPassword = !showResetPassword">
              <svg v-if="showResetPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span v-if="resetErrors.password" class="field-error">{{ resetErrors.password }}</span>
        </label>

        <label class="field">
          <span class="field-label">Xác nhận mật khẩu mới</span>
          <div class="field-input-wrap">
            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              v-model="resetForm.passwordConfirm"
              :type="showResetConfirm ? 'text' : 'password'"
              placeholder="Nhập lại mật khẩu mới"
              autocomplete="new-password"
              :class="{ 'is-invalid': resetErrors.passwordConfirm }"
              @blur="onResetBlur('passwordConfirm')"
              @input="onResetInput('passwordConfirm')"
            />
            <button type="button" class="field-toggle" @click="showResetConfirm = !showResetConfirm">
              <svg v-if="showResetConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span v-if="resetErrors.passwordConfirm" class="field-error">{{ resetErrors.passwordConfirm }}</span>
        </label>

        <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
          <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
          Đặt lại mật khẩu
        </button>
      </form>

      <template v-if="tab === 'login' || tab === 'register'">
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
      </template>

      <router-link to="/" class="auth-back">← Về trang chủ</router-link>
    </div>
  </div>
</template>

<script setup>
import logoImg from '@/../images/logo.png'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import { useGoogleAuth } from '@/composables/useGoogleAuth'
import GoogleIcon from '@/components/GoogleIcon.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const auth = useAuthStore()
const toast = useToastStore()
const route = useRoute()
const router = useRouter()

const tab = ref(route.query.tab === 'register' ? 'register' : 'login')
const errorMessage = ref('')

const showLoginPassword = ref(false)
const showRegisterPassword = ref(false)
const showRegisterConfirm = ref(false)
const showResetPassword = ref(false)
const showResetConfirm = ref(false)

const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ username: '', email: '', password: '', passwordConfirm: '' })
const forgotForm = ref({ email: '' })
const verifyForm = ref({ otp: '' })
const resetForm = ref({ password: '', passwordConfirm: '' })
const savedResetEmail = ref('')
const savedResetOtp = ref('')

const loginErrors = ref({ email: '', password: '' })
const loginTouched = ref({ email: false, password: false })

const registerErrors = ref({ username: '', email: '', password: '', passwordConfirm: '' })
const registerTouched = ref({ username: false, email: false, password: false, passwordConfirm: false })

const forgotErrors = ref({ email: '' })
const forgotTouched = ref({ email: false })

const verifyErrors = ref({ otp: '' })
const verifyTouched = ref({ otp: false })

const resetErrors = ref({ password: '', passwordConfirm: '' })
const resetTouched = ref({ password: false, passwordConfirm: false })

const resendCountdown = ref(0)
let timerInterval = null

const redirectPath = computed(() => (
  typeof route.query.redirect === 'string' ? route.query.redirect : '/'
))

const resetValidation = () => {
  loginErrors.value = { email: '', password: '' }
  loginTouched.value = { email: false, password: false }
  registerErrors.value = { username: '', email: '', password: '', passwordConfirm: '' }
  registerTouched.value = { username: false, email: false, password: false, passwordConfirm: false }
  forgotErrors.value = { email: '' }
  forgotTouched.value = { email: false }
  verifyErrors.value = { otp: '' }
  verifyTouched.value = { otp: false }
  resetErrors.value = { password: '', passwordConfirm: '' }
  resetTouched.value = { password: false, passwordConfirm: false }
}

const validateEmail = (email) => {
  if (!email || !email.trim()) {
    return 'Vui lòng nhập email.'
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.trim())) {
    return 'Email không đúng định dạng.'
  }
  return ''
}

const validatePassword = (password) => {
  if (!password) {
    return 'Vui lòng nhập mật khẩu.'
  }
  return ''
}

const validateUsername = (username) => {
  if (!username || !username.trim()) {
    return 'Vui lòng nhập họ tên.'
  }
  if (username.trim().length < 2) {
    return 'Họ tên phải có ít nhất 2 ký tự.'
  }
  return ''
}

const validateRegisterPassword = (password) => {
  if (!password) {
    return 'Vui lòng nhập mật khẩu.'
  }
  if (password.length < 8) {
    return 'Mật khẩu phải có ít nhất 8 ký tự.'
  }
  return ''
}

const validatePasswordConfirm = (passwordConfirm, password) => {
  if (!passwordConfirm) {
    return 'Vui lòng nhập lại mật khẩu.'
  }
  if (passwordConfirm !== password) {
    return 'Mật khẩu xác nhận không khớp.'
  }
  return ''
}

const validateOtp = (otp) => {
  if (!otp || !otp.trim()) {
    return 'Vui lòng nhập mã OTP.'
  }
  if (!/^\d{6}$/.test(otp.trim())) {
    return 'Mã OTP phải gồm 6 chữ số.'
  }
  return ''
}

const validateLoginField = (field) => {
  if (field === 'email') {
    loginErrors.value.email = validateEmail(loginForm.value.email)
  } else if (field === 'password') {
    loginErrors.value.password = validatePassword(loginForm.value.password)
  }
}

const validateRegisterField = (field) => {
  if (field === 'username') {
    registerErrors.value.username = validateUsername(registerForm.value.username)
  } else if (field === 'email') {
    registerErrors.value.email = validateEmail(registerForm.value.email)
  } else if (field === 'password') {
    registerErrors.value.password = validateRegisterPassword(registerForm.value.password)
  } else if (field === 'passwordConfirm') {
    registerErrors.value.passwordConfirm = validatePasswordConfirm(
      registerForm.value.passwordConfirm,
      registerForm.value.password
    )
  }
}

const validateForgotField = (field) => {
  if (field === 'email') {
    forgotErrors.value.email = validateEmail(forgotForm.value.email)
  }
}

const validateVerifyField = (field) => {
  if (field === 'otp') {
    verifyErrors.value.otp = validateOtp(verifyForm.value.otp)
  }
}

const validateResetField = (field) => {
  if (field === 'password') {
    resetErrors.value.password = validateRegisterPassword(resetForm.value.password)
  } else if (field === 'passwordConfirm') {
    resetErrors.value.passwordConfirm = validatePasswordConfirm(
      resetForm.value.passwordConfirm,
      resetForm.value.password
    )
  }
}

const onLoginBlur = (field) => {
  loginTouched.value[field] = true
  validateLoginField(field)
}

const onLoginInput = (field) => {
  if (loginTouched.value[field]) {
    validateLoginField(field)
  }
}

const onRegisterBlur = (field) => {
  registerTouched.value[field] = true
  validateRegisterField(field)
}

const onRegisterInput = (field) => {
  if (registerTouched.value[field]) {
    validateRegisterField(field)
  }
  if (field === 'password' && registerTouched.value.passwordConfirm) {
    registerErrors.value.passwordConfirm = validatePasswordConfirm(
      registerForm.value.passwordConfirm,
      registerForm.value.password
    )
  }
}

const onForgotBlur = (field) => {
  forgotTouched.value[field] = true
  validateForgotField(field)
}

const onForgotInput = (field) => {
  if (forgotTouched.value[field]) {
    validateForgotField(field)
  }
}

const onVerifyBlur = (field) => {
  verifyTouched.value[field] = true
  validateVerifyField(field)
}

const onVerifyInput = (field) => {
  if (verifyTouched.value[field]) {
    validateVerifyField(field)
  }
}

const onResetBlur = (field) => {
  resetTouched.value[field] = true
  validateResetField(field)
}

const onResetInput = (field) => {
  if (resetTouched.value[field]) {
    validateResetField(field)
  }
  if (field === 'password' && resetTouched.value.passwordConfirm) {
    resetErrors.value.passwordConfirm = validatePasswordConfirm(
      resetForm.value.passwordConfirm,
      resetForm.value.password
    )
  }
}

const validateLoginForm = () => {
  loginTouched.value.email = true
  loginTouched.value.password = true
  validateLoginField('email')
  validateLoginField('password')
  return !loginErrors.value.email && !loginErrors.value.password
}

const validateRegisterForm = () => {
  registerTouched.value.username = true
  registerTouched.value.email = true
  registerTouched.value.password = true
  registerTouched.value.passwordConfirm = true
  validateRegisterField('username')
  validateRegisterField('email')
  validateRegisterField('password')
  validateRegisterField('passwordConfirm')
  return (
    !registerErrors.value.username &&
    !registerErrors.value.email &&
    !registerErrors.value.password &&
    !registerErrors.value.passwordConfirm
  )
}

const startResendTimer = () => {
  resendCountdown.value = 60
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) {
      clearInterval(timerInterval)
      timerInterval = null
    }
  }, 1000)
}

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
  resetValidation()
}

const openForgotPassword = () => {
  forgotForm.value.email = loginForm.value.email || ''
  switchTab('forgot')
}

const handleLogin = () => {
  if (!validateLoginForm()) return
  runAuthAction(
    () => auth.login(loginForm.value.email.trim(), loginForm.value.password)
  )
}

const handleRegister = () => {
  if (!validateRegisterForm()) return

  runAuthAction(
    () => auth.register(registerForm.value),
    'Đăng ký thành công!'
  )
}

const handleForgotPassword = async () => {
  forgotTouched.value.email = true
  validateForgotField('email')
  if (forgotErrors.value.email) return

  errorMessage.value = ''
  try {
    const email = forgotForm.value.email.trim()
    await auth.forgotPassword(email)
    toast.success('Mã OTP đã được gửi tới email của bạn!')
    savedResetEmail.value = email
    verifyForm.value.otp = ''
    startResendTimer()
    switchTab('verify-otp')
  } catch (error) {
    errorMessage.value = error.message || 'Gửi mã OTP thất bại'
  }
}

const handleVerifyOtp = async () => {
  verifyTouched.value.otp = true
  validateVerifyField('otp')
  if (verifyErrors.value.otp) return

  errorMessage.value = ''
  try {
    const email = savedResetEmail.value
    const otp = verifyForm.value.otp.trim()
    await auth.verifyOtp({ email, otp })
    toast.success('Mã OTP hợp lệ! Vui lòng nhập mật khẩu mới.')
    savedResetOtp.value = otp
    resetForm.value.password = ''
    resetForm.value.passwordConfirm = ''
    switchTab('reset')
  } catch (error) {
    errorMessage.value = error.message || 'Xác nhận OTP thất bại'
  }
}

const handleResendOtp = async () => {
  if (resendCountdown.value > 0) return
  const targetEmail = savedResetEmail.value || forgotForm.value.email.trim()
  if (!targetEmail || validateEmail(targetEmail)) {
    toast.error('Vui lòng nhập email hợp lệ')
    return
  }

  errorMessage.value = ''
  try {
    await auth.forgotPassword(targetEmail)
    toast.success('Đã gửi lại mã OTP mới!')
    startResendTimer()
  } catch (error) {
    errorMessage.value = error.message || 'Gửi lại mã OTP thất bại'
  }
}

const handleResetPassword = async () => {
  resetTouched.value.password = true
  resetTouched.value.passwordConfirm = true
  validateResetField('password')
  validateResetField('passwordConfirm')

  if (resetErrors.value.password || resetErrors.value.passwordConfirm) return

  errorMessage.value = ''
  try {
    await auth.resetPassword({
      email: savedResetEmail.value,
      otp: savedResetOtp.value,
      password: resetForm.value.password,
      passwordConfirm: resetForm.value.passwordConfirm,
    })
    toast.success('Đặt lại mật khẩu thành công! Vui lòng đăng nhập.')
    loginForm.value.email = savedResetEmail.value
    switchTab('login')
  } catch (error) {
    errorMessage.value = error.message || 'Đặt lại mật khẩu thất bại'
  }
}

onMounted(async () => {
  if (auth.isAuthenticated) {
    auth.startTransition()
    await auth.completeNavigation(router, redirectPath.value)
    return
  }

  await mountGoogleButton()
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})

watch(() => route.query.tab, (value) => {
  tab.value = value === 'register' ? 'register' : 'login'
  errorMessage.value = ''
  resetValidation()
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

.auth-logo-img {
  width: 54px;
  height: 54px;
  margin: 0 auto 12px;
  border-radius: var(--radius-md);
  object-fit: contain;
  box-shadow: var(--shadow-md);
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
  color: var(--red, #ef4444);
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

.field-input-wrap input.is-invalid {
  border-color: var(--red, #ef4444);
  background-color: rgba(239, 68, 68, 0.04);
}

.field-input-wrap input.is-invalid:focus {
  border-color: var(--red, #ef4444);
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}

.field-error {
  font-size: 12px;
  color: var(--red, #ef4444);
  margin-top: 2px;
  display: block;
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
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
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

.auth-step-header {
  margin-bottom: 20px;
}

.back-text-btn {
  font-size: 13px;
  font-weight: 500;
  color: var(--primary);
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: color 0.2s;
}

.back-text-btn:hover {
  color: var(--primary-hover);
}

.otp-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.resend-otp-btn {
  font-size: 12px;
  color: var(--primary);
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: opacity 0.2s;
}

.resend-otp-btn:disabled {
  color: var(--text-faint);
  cursor: not-allowed;
}

.otp-input {
  letter-spacing: 4px;
  font-weight: 700;
  font-family: monospace, monospace;
}
</style>

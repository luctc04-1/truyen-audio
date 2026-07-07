<template>
  <AuthShell
    title="Truyện Audio"
    subtitle="Đăng nhập để theo dõi truyện và lưu tiến trình nghe"
    :busy="auth.transitioning"
  >
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
      <AuthTextField ref="loginEmailRef" v-model="loginForm.email" variant="email" />
      <PasswordField
        ref="loginPasswordRef"
        v-model="loginForm.password"
        mode="login"
        autocomplete="current-password"
      />

      <router-link :to="{ name: 'ForgotPassword' }" class="forgot-link">Quên mật khẩu?</router-link>

      <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
        <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
        Đăng nhập
      </button>
    </form>

    <form v-else class="auth-form" @submit.prevent="handleRegister">
      <AuthTextField ref="registerUsernameRef" v-model="registerForm.username" variant="username" />
      <AuthTextField ref="registerEmailRef" v-model="registerForm.email" variant="email" />
      <PasswordField
        ref="registerPasswordRef"
        v-model="registerForm.password"
        mode="strong"
        placeholder="Mật khẩu mạnh"
      />
      <PasswordField
        ref="registerConfirmRef"
        v-model="registerForm.passwordConfirm"
        label="Xác nhận mật khẩu"
        mode="confirm"
        :match="registerForm.password"
        :show-requirements="false"
        placeholder="Nhập lại mật khẩu"
      />

      <button type="submit" class="btn btn-primary" :disabled="auth.isBusy">
        <ButtonSpinner v-if="auth.isBusy" variant="light" :size="16" />
        Đăng ký
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
  </AuthShell>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useGoogleAuth } from '@/composables/useGoogleAuth'
import { firstAuthFieldError } from '@/utils/validation'
import AuthShell from '@/components/AuthShell.vue'
import AuthTextField from '@/components/AuthTextField.vue'
import PasswordField from '@/components/PasswordField.vue'
import GoogleIcon from '@/components/GoogleIcon.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const tab = ref(route.query.tab === 'register' ? 'register' : 'login')
const errorMessage = ref('')

const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ username: '', email: '', password: '', passwordConfirm: '' })

const loginEmailRef = ref(null)
const loginPasswordRef = ref(null)
const registerUsernameRef = ref(null)
const registerEmailRef = ref(null)
const registerPasswordRef = ref(null)
const registerConfirmRef = ref(null)

const allFieldRefs = [
  loginEmailRef,
  loginPasswordRef,
  registerUsernameRef,
  registerEmailRef,
  registerPasswordRef,
  registerConfirmRef,
]

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

const resetFormValidation = () => {
  allFieldRefs.forEach((fieldRef) => fieldRef.value?.reset())
}

const switchTab = (nextTab) => {
  tab.value = nextTab
  errorMessage.value = ''
  resetFormValidation()
}

const submitWithValidation = (fieldRefs, action) => {
  const validationError = firstAuthFieldError(fieldRefs.map((fieldRef) => fieldRef.value))
  if (validationError) {
    errorMessage.value = validationError
    return
  }
  action()
}

const handleLogin = () => submitWithValidation(
  [loginEmailRef, loginPasswordRef],
  () => runAuthAction(() => auth.login(loginForm.value.email.trim(), loginForm.value.password))
)

const handleRegister = () => submitWithValidation(
  [registerUsernameRef, registerEmailRef, registerPasswordRef, registerConfirmRef],
  () => runAuthAction(() => auth.register(registerForm.value), 'Đăng ký thành công!')
)

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

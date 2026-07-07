<template>
  <AuthShell
    title="Đặt lại mật khẩu"
    :subtitle="`Tạo mật khẩu mới cho tài khoản ${email || 'của bạn'}`"
  >
    <p v-if="errorMessage" class="auth-error">{{ errorMessage }}</p>

    <form v-if="!resetDone" class="auth-form" @submit.prevent="handleSubmit">
      <AuthTextField ref="emailRef" v-model="email" variant="email" />
      <PasswordField
        ref="passwordRef"
        v-model="password"
        label="Mật khẩu mới"
        mode="strong"
        placeholder="Mật khẩu mạnh"
      />
      <PasswordField
        ref="confirmRef"
        v-model="passwordConfirm"
        label="Xác nhận mật khẩu"
        mode="confirm"
        :match="password"
        :show-requirements="false"
        placeholder="Nhập lại mật khẩu"
      />

      <button type="submit" class="btn btn-primary" :disabled="submitting">
        <ButtonSpinner v-if="submitting" variant="light" :size="16" />
        Đặt lại mật khẩu
      </button>
    </form>

    <div v-else class="auth-success-block">
      <p class="auth-success">Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay.</p>
      <router-link :to="{ name: 'Auth' }" class="btn btn-primary">Đăng nhập</router-link>
    </div>

    <router-link v-if="!resetDone" :to="{ name: 'Auth' }" class="auth-back">← Quay lại đăng nhập</router-link>
  </AuthShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AuthService from '@/services/AuthService'
import { firstAuthFieldError } from '@/utils/validation'
import AuthShell from '@/components/AuthShell.vue'
import AuthTextField from '@/components/AuthTextField.vue'
import PasswordField from '@/components/PasswordField.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const route = useRoute()

const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const emailRef = ref(null)
const passwordRef = ref(null)
const confirmRef = ref(null)
const submitting = ref(false)
const errorMessage = ref('')
const resetDone = ref(false)
const token = ref('')

onMounted(() => {
  email.value = typeof route.query.email === 'string' ? route.query.email : ''
  token.value = typeof route.query.token === 'string' ? route.query.token : ''

  if (!token.value) {
    errorMessage.value = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'
  }
})

const handleSubmit = async () => {
  errorMessage.value = ''

  if (!token.value) {
    errorMessage.value = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'
    return
  }

  const validationError = firstAuthFieldError([emailRef.value, passwordRef.value, confirmRef.value])
  if (validationError) {
    errorMessage.value = validationError
    return
  }

  submitting.value = true
  try {
    await AuthService.resetPassword({
      email: email.value.trim(),
      token: token.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    })
    resetDone.value = true
  } catch (error) {
    errorMessage.value = error.message || 'Đặt lại mật khẩu thất bại'
  } finally {
    submitting.value = false
  }
}
</script>

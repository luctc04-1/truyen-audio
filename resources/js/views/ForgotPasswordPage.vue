<template>
  <AuthShell
    title="Quên mật khẩu"
    subtitle="Nhập email đăng ký để nhận liên kết đặt lại mật khẩu"
  >
    <p v-if="errorMessage" class="auth-error">{{ errorMessage }}</p>
    <p v-if="successMessage" class="auth-success">{{ successMessage }}</p>

    <form v-if="!successMessage" class="auth-form" @submit.prevent="handleSubmit">
      <AuthTextField ref="emailRef" v-model="email" variant="email" />

      <button type="submit" class="btn btn-primary" :disabled="submitting">
        <ButtonSpinner v-if="submitting" variant="light" :size="16" />
        Gửi liên kết
      </button>
    </form>

    <router-link :to="{ name: 'Auth' }" class="auth-back">← Quay lại đăng nhập</router-link>
  </AuthShell>
</template>

<script setup>
import { ref } from 'vue'
import AuthService from '@/services/AuthService'
import { firstAuthFieldError } from '@/utils/validation'
import AuthShell from '@/components/AuthShell.vue'
import AuthTextField from '@/components/AuthTextField.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const email = ref('')
const emailRef = ref(null)
const submitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''

  const validationError = firstAuthFieldError([emailRef.value])
  if (validationError) {
    errorMessage.value = validationError
    return
  }

  submitting.value = true
  try {
    const res = await AuthService.forgotPassword(email.value.trim())
    successMessage.value = res.message || 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu.'
  } catch (error) {
    errorMessage.value = error.message || 'Không thể gửi email đặt lại mật khẩu'
  } finally {
    submitting.value = false
  }
}
</script>

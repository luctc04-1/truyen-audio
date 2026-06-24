<template>
  <Transition name="auth-overlay-fade">
    <div v-if="auth.transitioning" class="auth-overlay" role="status" aria-label="Đang xử lý">
      <div class="auth-overlay-spinner" aria-hidden="true"></div>
    </div>
  </Transition>
</template>

<script setup>
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
</script>

<style scoped>
.auth-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(9, 9, 11, 0.55);
  backdrop-filter: blur(4px);
}

.auth-overlay-spinner {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.12);
  border-top-color: var(--primary);
  animation: auth-spin 0.75s linear infinite;
}

@keyframes auth-spin {
  to { transform: rotate(360deg); }
}

.auth-overlay-fade-enter-active,
.auth-overlay-fade-leave-active {
  transition: opacity 0.2s ease;
}

.auth-overlay-fade-enter-from,
.auth-overlay-fade-leave-to {
  opacity: 0;
}
</style>

<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div
        v-if="modelValue"
        class="confirm-backdrop"
        @click.self="cancel"
      >
        <div
          class="confirm-card"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="titleId"
        >
          <div v-if="variant === 'danger'" class="confirm-icon danger">
            <svg v-if="icon === 'logout'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" x2="9" y1="12" y2="12" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 6h18" />
              <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
              <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
            </svg>
          </div>

          <h3 :id="titleId" class="confirm-title">{{ title }}</h3>
          <p v-if="message" class="confirm-message">{{ message }}</p>

          <div class="confirm-actions">
            <button type="button" class="btn btn-cancel" :disabled="confirmLoading" @click="cancel">
              {{ cancelText }}
            </button>
            <button
              type="button"
              :class="['btn', variant === 'danger' ? 'btn-danger' : 'btn-primary']"
              :disabled="confirmLoading"
              @click="confirm"
            >
              <ButtonSpinner
                v-if="confirmLoading"
                :variant="variant === 'danger' ? 'danger' : 'light'"
                :size="14"
              />
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useId } from 'vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Xác nhận' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Xác nhận' },
  cancelText: { type: String, default: 'Huỷ' },
  variant: { type: String, default: 'primary' },
  icon: { type: String, default: 'trash' },
  confirmLoading: { type: Boolean, default: false },
  autoClose: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const titleId = useId()

const close = () => emit('update:modelValue', false)

const cancel = () => {
  if (props.confirmLoading) return
  emit('cancel')
  close()
}

const confirm = () => {
  if (props.confirmLoading) return
  emit('confirm')
  if (props.autoClose) close()
}
</script>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  background: rgba(9, 9, 11, 0.72);
  backdrop-filter: blur(6px);
}

.confirm-card {
  width: 100%;
  max-width: 360px;
  padding: 24px;
  border-radius: var(--radius-lg);
  background: var(--bg-card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  text-align: center;
}

.confirm-icon {
  width: 52px;
  height: 52px;
  margin: 0 auto 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-icon.danger {
  background: rgba(239, 68, 68, 0.12);
  color: var(--red);
}

.confirm-icon svg {
  width: 24px;
  height: 24px;
}

.confirm-title {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 8px;
}

.confirm-message {
  font-size: 14px;
  line-height: 1.5;
  color: var(--text-muted);
  margin-bottom: 22px;
}

.confirm-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.btn {
  height: 44px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  transition: opacity 0.2s, background 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-cancel {
  background: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text);
}

.btn-cancel:hover {
  background: var(--bg-card);
  border-color: var(--border-strong);
}

.btn-primary {
  background: var(--gradient-premium);
  color: #fff;
}

.btn-danger {
  background: rgba(239, 68, 68, 0.14);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

.btn-danger:hover {
  background: rgba(239, 68, 68, 0.22);
}

.btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.2s ease;
}

.confirm-fade-enter-active .confirm-card,
.confirm-fade-leave-active .confirm-card {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}

.confirm-fade-enter-from .confirm-card,
.confirm-fade-leave-to .confirm-card {
  transform: scale(0.96) translateY(8px);
  opacity: 0;
}
</style>

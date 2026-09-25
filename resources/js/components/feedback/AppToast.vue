<template>
  <div class="toast-stack" aria-live="polite">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        :class="['toast-item', `toast-${toast.type}`]"
      >
        <span class="toast-icon" aria-hidden="true">
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
        </span>
        <span class="toast-message">{{ toast.message }}</span>
        <button type="button" class="toast-close" @click="toastStore.remove(toast.id)">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toastStore'

const toastStore = useToastStore()
</script>

<style scoped>
.toast-stack {
  position: fixed;
  top: calc(var(--nav-height) + 12px);
  right: 16px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
  width: min(360px, calc(100vw - 32px));
  pointer-events: none;
}

.toast-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: var(--bg-card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  pointer-events: auto;
}

.toast-success {
  border-color: rgba(34, 197, 94, 0.35);
}

.toast-error {
  border-color: rgba(239, 68, 68, 0.35);
}

.toast-info {
  border-color: rgba(168, 85, 247, 0.35);
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-icon svg {
  width: 18px;
  height: 18px;
}

.toast-success .toast-icon svg {
  stroke: var(--success);
}

.toast-error .toast-icon svg {
  stroke: var(--red);
}

.toast-info .toast-icon svg {
  stroke: var(--primary);
}

.toast-message {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
}

.toast-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: var(--radius-sm);
  color: var(--text-faint);
  flex-shrink: 0;
}

.toast-close:hover {
  background: var(--bg-muted);
  color: var(--text-muted);
}

.toast-close svg {
  width: 14px;
  height: 14px;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(16px);
}
</style>

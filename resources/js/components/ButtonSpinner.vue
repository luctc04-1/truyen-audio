<template>
  <span
    :class="['btn-spinner', `btn-spinner--${variant}`]"
    :style="sizeStyle"
    aria-hidden="true"
  ></span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'muted',
    validator: (value) => ['light', 'muted', 'amber', 'danger'].includes(value),
  },
  size: { type: Number, default: 16 },
})

const sizeStyle = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
}))
</script>

<style scoped>
.btn-spinner {
  display: inline-block;
  border-radius: 50%;
  border: 2px solid transparent;
  animation: btn-spinner-spin 0.7s linear infinite;
  flex-shrink: 0;
}

.btn-spinner--light {
  border-color: rgba(255, 255, 255, 0.28);
  border-top-color: #fff;
}

.btn-spinner--muted {
  border-color: rgba(161, 161, 170, 0.35);
  border-top-color: var(--text-muted, #a1a1aa);
}

.btn-spinner--amber {
  border-color: rgba(245, 158, 11, 0.28);
  border-top-color: var(--amber, #f59e0b);
}

.btn-spinner--danger {
  border-color: rgba(252, 165, 165, 0.28);
  border-top-color: #fca5a5;
}

@keyframes btn-spinner-spin {
  to { transform: rotate(360deg); }
}
</style>

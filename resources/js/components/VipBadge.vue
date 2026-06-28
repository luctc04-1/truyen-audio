<template>
  <span :class="['vip-badge', `vip-badge-${size}`, extraClass]">
    <svg
      class="vip-badge-icon"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z" />
      <path d="M5 21h14" />
    </svg>
    <span>VIP</span>
  </span>
</template>

<script setup>
defineProps({
  size: { type: String, default: 'sm' },
  extraClass: { type: String, default: '' },
})
</script>

<style scoped>
.vip-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border-radius: var(--radius-full);
  font-weight: 600;
  color: var(--amber);
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.3);
  flex-shrink: 0;
  line-height: 1;
}

.vip-badge-sm {
  font-size: 10px;
  padding: 2px 8px;
}

.vip-badge-sm .vip-badge-icon {
  width: 10px;
  height: 10px;
}

.vip-badge-md {
  font-size: 11px;
  padding: 3px 9px;
}

.vip-badge-md .vip-badge-icon {
  width: 11px;
  height: 11px;
}

:deep(.np-queue-vip) {
  margin-left: auto;
}
</style>

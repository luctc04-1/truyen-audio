<template>
  <span :class="['admin-badge', `admin-badge-${size}`]" title="Quản trị viên">
    <svg
      class="admin-badge-icon"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  </span>
</template>

<script setup>
defineProps({
  size: { type: String, default: 'sm' },
})
</script>

<style scoped>
.admin-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border-radius: var(--radius-full);
  font-weight: 600;
  color: var(--amber, #f59e0b);
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.3);
  flex-shrink: 0;
  line-height: 1;
}

.admin-badge-sm {
  font-size: 10px;
  padding: 2px 8px;
}

.admin-badge-sm .admin-badge-icon {
  width: 10px;
  height: 10px;
}

.admin-badge-md {
  font-size: 11px;
  padding: 3px 9px;
}

.admin-badge-md .admin-badge-icon {
  width: 11px;
  height: 11px;
}
</style>

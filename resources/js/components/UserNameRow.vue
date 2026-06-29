<template>
  <div class="user-name-row">
    <span class="user-name">{{ user?.username || 'Người dùng' }}</span>
    <AdminBadge v-if="user?.is_admin" size="sm" />
    <VipBadge v-else-if="user?.is_premium" size="sm" />
    <span v-if="tagLabel" class="tag-badge">{{ tagLabel }}</span>
  </div>
</template>

<script setup>
import VipBadge from '@/components/VipBadge.vue'
import AdminBadge from '@/components/AdminBadge.vue'

defineProps({
  user: { type: Object, default: null },
  tagLabel: { type: String, default: '' },
})
</script>

<style scoped>
.user-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.user-name {
  font-weight: 600;
  font-size: 14px;
  color: var(--amber, #f59e0b);
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  background: var(--bg-muted);
  color: var(--text-muted);
  border: 1px solid var(--border);
}
</style>

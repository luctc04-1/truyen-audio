<template>
  <div :class="['user-avatar', `user-avatar-${size}`]">
    <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user.username || 'Avatar'" />
    <span v-else>{{ displayInitial }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user: { type: Object, default: null },
  size: { type: String, default: 'md' },
})

const displayInitial = computed(() => {
  if (props.user?.initial) return props.user.initial
  const name = (props.user?.username || '').trim()
  return name ? name.charAt(0).toUpperCase() : 'U'
})
</script>

<style scoped>
.user-avatar {
  border-radius: 50%;
  background: rgba(168, 85, 247, 0.12);
  color: #a855f7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  flex-shrink: 0;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-avatar-md {
  width: 36px;
  height: 36px;
  font-size: 13px;
}

.user-avatar-sm {
  width: 30px;
  height: 30px;
  font-size: 12px;
}
</style>

<template>
  <div class="series-skeleton-list">
    <div v-for="n in count" :key="n" class="series-skeleton-item">
      <div class="sk sk-cover"></div>
      <div class="sk sk-line sk-w-75"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: { type: Number, default: 4 },
})
</script>

<style scoped>
.series-skeleton-list {
  margin-top: 8px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: var(--bg-card);
}

.series-skeleton-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
}

.series-skeleton-item:last-child {
  border-bottom: none;
}

.sk {
  position: relative;
  overflow: hidden;
  background: var(--bg-muted);
  border-radius: 6px;
  flex-shrink: 0;
}

.sk::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
  animation: shimmer 1.3s infinite;
}

.sk-cover {
  width: 36px;
  height: 48px;
}

.sk-line {
  height: 12px;
  flex: 1;
}

.sk-w-75 {
  max-width: 75%;
}

@keyframes shimmer {
  100% { transform: translateX(100%); }
}
</style>

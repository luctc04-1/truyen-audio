<template>
  <div class="skeleton-card">
    <div class="sk sk-thumb"></div>
    <div class="sk sk-line sk-line-lg"></div>
    <div class="sk sk-line sk-line-sm"></div>
  </div>
</template>

<style scoped>
.skeleton-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.sk {
  position: relative;
  overflow: hidden;
  background: var(--bg-muted);
  border-radius: var(--radius-sm);
}
.sk::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
  animation: shimmer 1.3s infinite;
}
.sk-thumb { aspect-ratio: 3 / 4; border-radius: var(--radius-md); }
.sk-line { height: 12px; }
.sk-line-lg { width: 90%; }
.sk-line-sm { width: 55%; }
@keyframes shimmer { 100% { transform: translateX(100%); } }
</style>

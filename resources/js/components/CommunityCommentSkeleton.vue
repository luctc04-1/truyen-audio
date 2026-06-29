<template>
  <div class="comment-skeleton">
    <div class="comment-skeleton-header">
      <div class="sk sk-avatar"></div>
      <div class="comment-skeleton-meta">
        <div class="sk sk-line sk-w-35"></div>
        <div class="sk sk-line sk-w-20 sk-line-sm"></div>
      </div>
    </div>
    <div class="sk sk-line sk-w-full"></div>
    <div class="sk sk-line sk-w-70"></div>
  </div>
</template>

<style scoped>
.comment-skeleton {
  padding: 12px 0;
}

.comment-skeleton-header {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 10px;
}

.comment-skeleton-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 2px;
}

.sk {
  position: relative;
  overflow: hidden;
  background: var(--bg-muted);
  border-radius: 6px;
}

.sk::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
  animation: shimmer 1.3s infinite;
}

.sk-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  flex-shrink: 0;
}

.sk-line {
  height: 11px;
  margin-bottom: 8px;
}

.sk-line-sm {
  height: 9px;
  margin-bottom: 0;
}

.sk-w-full { width: 100%; }
.sk-w-70 { width: 70%; margin-bottom: 0; }
.sk-w-35 { width: 35%; }
.sk-w-20 { width: 20%; }

@keyframes shimmer {
  100% { transform: translateX(100%); }
}
</style>

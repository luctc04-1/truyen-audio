<template>
  <div class="post-skeleton">
    <div class="post-skeleton-header">
      <div class="sk sk-avatar"></div>
      <div class="post-skeleton-meta">
        <div class="sk sk-line sk-w-45"></div>
        <div class="sk sk-line sk-w-25 sk-line-sm"></div>
      </div>
    </div>
    <div class="sk sk-line sk-w-full"></div>
    <div class="sk sk-line sk-w-88"></div>
    <div class="sk sk-line sk-w-60"></div>
    <div class="post-skeleton-actions">
      <div class="sk sk-pill sk-w-20"></div>
      <div class="sk sk-pill sk-w-20"></div>
    </div>
  </div>
</template>

<style scoped>
.post-skeleton {
  padding: 16px;
  border-bottom: 1px solid var(--border);
}

.post-skeleton:last-child {
  border-bottom: none;
}

.post-skeleton-header {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 14px;
}

.post-skeleton-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 2px;
}

.post-skeleton-actions {
  display: flex;
  gap: 16px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid var(--border);
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
  width: 36px;
  height: 36px;
  border-radius: 50%;
  flex-shrink: 0;
}

.sk-line {
  height: 12px;
}

.sk-line-sm {
  height: 10px;
}

.sk-pill {
  height: 18px;
  border-radius: var(--radius-full);
}

.sk-w-full { width: 100%; margin-bottom: 8px; }
.sk-w-88 { width: 88%; margin-bottom: 8px; }
.sk-w-60 { width: 60%; }
.sk-w-45 { width: 45%; }
.sk-w-25 { width: 25%; }
.sk-w-20 { width: 52px; }

@keyframes shimmer {
  100% { transform: translateX(100%); }
}
</style>

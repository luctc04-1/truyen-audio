<template>
  <article class="stat-card" :class="`stat-${tone}`">
    <!-- Glowing Aura Backdrop -->
    <div class="stat-glow"></div>
    <div class="stat-sheen"></div>

    <div class="stat-content">
      <div class="stat-top">
        <div class="stat-icon-wrap">
          <i :class="icon"></i>
        </div>
        <div v-if="trend !== undefined" class="stat-trend-pill" :class="trend >= 0 ? 'trend-up' : 'trend-down'">
          <i :class="trend >= 0 ? 'ri-arrow-right-up-line' : 'ri-arrow-right-down-line'"></i>
          <span>{{ Math.abs(trend) }}%</span>
        </div>
      </div>

      <div class="stat-main">
        <span class="stat-label">{{ label }}</span>
        <h3 class="stat-value">{{ value }}</h3>
        <p v-if="subtext" class="stat-subtext">{{ subtext }}</p>
      </div>
    </div>

    <!-- Mini SVG Background Sparkline Wave -->
    <svg class="stat-wave" viewBox="0 0 200 60" preserveAspectRatio="none">
      <defs>
        <linearGradient :id="`grad-${tone}`" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" :stop-color="toneColor" stop-opacity="0.32" />
          <stop offset="100%" :stop-color="toneColor" stop-opacity="0.0" />
        </linearGradient>
      </defs>
      <path
        :d="wavePath"
        fill="none"
        :stroke="toneColor"
        stroke-width="2.2"
        stroke-linecap="round"
      />
      <path
        :d="`${wavePath} L 200 60 L 0 60 Z`"
        :fill="`url(#grad-${tone})`"
      />
    </svg>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, default: 'ri-bar-chart-fill' },
  tone: { type: String, default: 'purple' }, // purple, emerald, blue, amber
  trend: { type: Number, default: undefined },
  subtext: { type: String, default: '' },
})

const toneColor = computed(() => {
  switch (props.tone) {
    case 'emerald': return '#10b981'
    case 'blue': return '#3b82f6'
    case 'amber': return '#f59e0b'
    case 'purple':
    default: return '#a855f7'
  }
})

const wavePath = computed(() => {
  switch (props.tone) {
    case 'emerald': return 'M 0 45 Q 40 20 80 35 T 160 15 T 200 10'
    case 'blue': return 'M 0 40 Q 50 15 100 38 T 160 20 T 200 15'
    case 'amber': return 'M 0 50 Q 40 35 90 25 T 150 30 T 200 18'
    case 'purple':
    default: return 'M 0 42 Q 45 10 90 32 T 150 12 T 200 8'
  }
})
</script>

<style scoped>
.stat-card {
  background: var(--admin-card-bg, rgba(14, 18, 30, 0.72));
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  border-radius: 18px;
  padding: 24px 26px;
  position: relative;
  overflow: hidden;
  box-shadow: var(--admin-glass-shadow, 0 16px 40px -8px rgba(0, 0, 0, 0.6));
  transition: transform 0.28s var(--ease-spring), border-color 0.25s ease, box-shadow 0.25s ease;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
}

.stat-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255, 255, 255, 0.22);
  box-shadow: 0 20px 48px rgba(0, 0, 0, 0.55), 0 0 30px rgba(168, 85, 247, 0.2);
}

.stat-glow {
  position: absolute;
  top: -45px;
  right: -45px;
  width: 140px;
  height: 140px;
  border-radius: 50%;
  filter: blur(45px);
  opacity: 0.25;
  transition: opacity 0.35s ease;
  pointer-events: none;
}

.stat-purple .stat-glow { background: #a855f7; }
.stat-emerald .stat-glow { background: #10b981; }
.stat-blue .stat-glow { background: #3b82f6; }
.stat-amber .stat-glow { background: #f59e0b; }

.stat-card:hover .stat-glow {
  opacity: 0.55;
}

.stat-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-icon-wrap {
  width: 50px;
  height: 50px;
  border-radius: 15px;
  display: grid;
  place-items: center;
  font-size: 24px;
  flex-shrink: 0;
  transition: transform 0.28s var(--ease-spring);
}

.stat-card:hover .stat-icon-wrap {
  transform: scale(1.1) rotate(-3deg);
}

.stat-purple .stat-icon-wrap { background: rgba(168, 85, 247, 0.18); color: #c084fc; box-shadow: 0 0 20px rgba(168, 85, 247, 0.25); }
.stat-emerald .stat-icon-wrap { background: rgba(16, 185, 129, 0.18); color: #34d399; box-shadow: 0 0 20px rgba(16, 185, 129, 0.25); }
.stat-blue .stat-icon-wrap { background: rgba(59, 130, 246, 0.18); color: #60a5fa; box-shadow: 0 0 20px rgba(59, 130, 246, 0.25); }
.stat-amber .stat-icon-wrap { background: rgba(245, 158, 11, 0.18); color: #fbbf24; box-shadow: 0 0 20px rgba(245, 158, 11, 0.25); }

.stat-trend-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 11px;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 700;
  font-family: var(--admin-font-mono);
}

.trend-up {
  background: rgba(16, 185, 129, 0.18);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.15);
}

.trend-down {
  background: rgba(244, 63, 94, 0.18);
  color: #fb7185;
  border: 1px solid rgba(244, 63, 94, 0.3);
}

.stat-label {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--admin-muted, #94a3b8);
  display: block;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 30px;
  font-weight: 800;
  color: #f8fafc;
  margin: 0 0 4px 0;
  letter-spacing: -0.035em;
  font-family: var(--admin-font);
}

.stat-subtext {
  margin: 0;
  font-size: 12px;
  color: var(--admin-faint, #64748b);
  font-weight: 500;
}

.stat-wave {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 56px;
  opacity: 0.7;
  pointer-events: none;
  z-index: 1;
  transition: opacity 0.35s ease;
}

.stat-card:hover .stat-wave {
  opacity: 1;
}
</style>

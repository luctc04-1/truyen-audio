<template>
  <router-link :to="`/story/${story.id}`" class="story-card">
    <div class="story-card-thumb">
      <img
        :src="imgSrc"
        :alt="story.title"
        loading="lazy"
        @error="onImgError"
      />
      <div class="story-card-overlay"></div>

      <div class="story-card-status">
        <VipBadge v-if="story.is_premium" />
        <span v-else-if="story.status === 'completed'" class="badge badge-success">Hoàn thành</span>
        <span v-else class="badge">Đang cập nhật</span>
      </div>
    </div>

    <div class="story-card-title">{{ story.title }}</div>
    <div class="story-card-meta">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>
      </svg>
      {{ story.plays }} <span>•</span> {{ story.episodeCount }} tập
    </div>
  </router-link>
</template>

<script setup>
import { ref, watchEffect } from 'vue'
import VipBadge from '@/components/VipBadge.vue'

const FALLBACK = 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80'

const props = defineProps({
  story: {
    type: Object,
    required: true,
  },
})

const imgSrc = ref(props.story.image || FALLBACK)

watchEffect(() => {
  imgSrc.value = props.story.image || FALLBACK
})

const onImgError = () => {
  imgSrc.value = FALLBACK
}
</script>

<style scoped>
.story-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-decoration: none;
  transition: transform 0.2s;
}

.story-card:hover {
  transform: translateY(-4px);
}

.story-card-thumb {
  position: relative;
  aspect-ratio: 3 / 4;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--bg-muted);
}

.story-card-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.story-card:hover .story-card-thumb img {
  transform: scale(1.06);
}

.story-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 55%, rgba(0, 0, 0, 0.88));
}

.story-card-status {
  position: absolute;
  bottom: 8px;
  left: 8px;
  z-index: 1;
}

.story-card-title {
  font-size: 14px;
  font-weight: 600;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.story-card:hover .story-card-title {
  color: var(--primary);
}

.story-card-meta {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-muted);
}

.story-card-meta svg { width: 14px; height: 14px; }

.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 10px;
  border-radius: var(--radius-full);
  font-size: 11px;
  font-weight: 600;
  background: var(--bg-muted);
  color: var(--text-muted);
  border: 1px solid var(--border);
}

.badge-success {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  border-color: var(--success-border);
}
</style>

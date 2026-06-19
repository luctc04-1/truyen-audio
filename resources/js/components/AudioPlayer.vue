<template>
  <div v-if="storyStore.currentStory" class="audio-player">
    <div class="player-progress" @click="handleProgressClick">
      <div class="player-progress-fill" :style="{ width: progressPercent + '%' }"></div>
    </div>
    <img :src="storyStore.currentStory.image" :alt="storyStore.currentStory.title" class="player-thumb" />
    <div class="player-info">
      <div class="player-title">{{ storyStore.currentStory.title }}</div>
      <div class="player-sub">{{ storyStore.currentStory.author }}</div>
    </div>
    <div class="player-controls">
      <button class="icon-btn" title="Trước">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      </button>
      <button class="player-play-btn" @click="togglePlay" title="Phát/Dừng">
        <svg v-if="storyStore.isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
      </button>
      <button class="icon-btn" title="Tiếp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 5 7 7-7 7"/><path d="M5 12h14"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useStoryStore } from '../stores/storyStore'
import { computed } from 'vue'

const storyStore = useStoryStore()

const progressPercent = computed(() => {
  if (storyStore.duration === 0) return 0
  return (storyStore.currentTime / storyStore.duration) * 100
})

const togglePlay = () => {
  if (storyStore.isPlaying) {
    storyStore.pause()
  } else {
    storyStore.resume()
  }
}

const handleProgressClick = (e) => {
  const bar = e.currentTarget
  const percent = e.offsetX / bar.offsetWidth
  storyStore.setCurrentTime(percent * storyStore.duration)
}
</script>

<style scoped>
.audio-player {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 90;
  background: rgba(17, 17, 19, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid var(--border);
  padding: 10px 16px;
  height: var(--player-height);
  display: flex;
  align-items: center;
  gap: 12px;
}

.player-progress {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--border);
  cursor: pointer;
}

.player-progress-fill {
  height: 100%;
  background: var(--gradient-premium);
  border-radius: 0 2px 2px 0;
  transition: width 0.1s;
}

.player-thumb {
  width: 52px;
  height: 52px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  flex-shrink: 0;
}

.player-info {
  flex: 1;
  min-width: 0;
}

.player-title {
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.player-sub {
  font-size: 11px;
  color: var(--text-muted);
}

.player-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.player-controls .icon-btn {
  width: 40px;
  height: 40px;
}

.player-play-btn {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background: var(--gradient-premium);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, transform 0.15s;
  flex-shrink: 0;
}

.player-play-btn:hover {
  opacity: 0.85;
  transform: scale(1.05);
}

.player-play-btn svg {
  width: 16px;
  height: 16px;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: all 0.2s;
  flex-shrink: 0;
}

.icon-btn:hover {
  background: var(--bg-muted);
  color: var(--text);
}

.icon-btn svg {
  width: 18px;
  height: 18px;
}
</style>

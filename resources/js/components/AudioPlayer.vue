<template>
  <div v-if="audio.currentEpisode" class="audio-player">
    <div class="player-progress" @click="handleProgressClick">
      <div class="player-progress-fill" :style="{ width: audio.progress + '%' }"></div>
    </div>

    <button class="player-media" @click="audio.expand()">
      <img :src="cover" :alt="title" class="player-thumb" />
      <div class="player-info">
        <div class="player-title" :title="fullDisplayTitle">{{ displayTitle }}</div>
        <div class="player-time" :class="{ buffering: audio.isBuffering }">
          {{ formatTime(audio.currentTime) }} / {{ formatTime(audio.duration) }}
        </div>
      </div>
    </button>

    <div class="player-controls">
      <button class="player-speed" title="Tốc độ" @click.stop="audio.cycleSpeed()">{{ audio.speedLabel }}</button>
      <button class="icon-btn" title="Tập trước" :disabled="!audio.hasPrev" @click.stop="audio.prev()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
      </button>
      <button class="player-play-btn" @click.stop="audio.togglePlay()" :title="audio.isPlaying ? 'Tạm dừng' : 'Phát'">
        <svg v-if="audio.isBuffering" class="spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        <svg v-else-if="audio.isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
      </button>
      <button class="icon-btn" title="Tập tiếp" :disabled="!audio.hasNext" @click.stop="audio.next()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
      </button>
      <button class="icon-btn" :title="audio.muted ? 'Bật tiếng' : 'Tắt tiếng'" @click.stop="audio.toggleMute()">
        <svg v-if="audio.muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
      </button>
      <button class="icon-btn" title="Hẹn giờ" @click.stop="audio.cycleSleepTimer()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </button>
      <button class="icon-btn" title="Đóng" @click.stop="audio.stop()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAudioStore } from '@/stores/audioStore'
import { formatTime, formatEpisodeWithTitle } from '@/utils/helpers'

const audio = useAudioStore()

const title = computed(() => audio.currentEpisode?.title || '')
const storyTitle = computed(() => audio.currentStory?.title || '')

const truncate = (text, max) => {
  if (!text) return ''
  return text.length > max ? `${text.slice(0, max)}…` : text
}

const fullDisplayTitle = computed(() => {
  const line = formatEpisodeWithTitle(audio.currentEpisode?.episode_number, title.value)
  return line || storyTitle.value
})

const displayTitle = computed(() => {
  const line = formatEpisodeWithTitle(audio.currentEpisode?.episode_number, title.value)
  if (line) return truncate(line, 28)
  return truncate(storyTitle.value, 18)
})

const cover = computed(() => audio.currentStory?.image || 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=200&q=80')

const handleProgressClick = (e) => {
  const bar = e.currentTarget
  const percent = (e.offsetX / bar.offsetWidth) * 100
  audio.seekToPercent(percent)
}
</script>

<style scoped>
.audio-player {
  --player-pad-x: 8px;
  --player-pad-y: 7px;
  --thumb-size: 38px;
  --play-size: 34px;
  --ctrl-size: 28px;

  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 90;
  background: rgba(17, 17, 19, 0.97);
  backdrop-filter: blur(20px);
  border-top: 1px solid var(--border);
  padding: var(--player-pad-y) var(--player-pad-x);
  padding-bottom: max(var(--player-pad-y), env(safe-area-inset-bottom, 0px));
  min-height: var(--player-height);
  display: flex;
  align-items: center;
  gap: 6px;
}

@media (min-width: 480px) {
  .audio-player {
    --player-pad-x: 12px;
    --thumb-size: 44px;
    --play-size: 40px;
    --ctrl-size: 34px;
    gap: 8px;
  }
}

@media (min-width: 768px) {
  .audio-player {
    --player-pad-x: 16px;
    --player-pad-y: 10px;
    --thumb-size: 50px;
    --play-size: 44px;
    --ctrl-size: 38px;
    gap: 10px;
  }
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
.player-progress:hover { height: 5px; }
.player-progress-fill {
  height: 100%;
  background: var(--gradient-premium);
  border-radius: 0 2px 2px 0;
  transition: width 0.15s linear;
}

/* Vùng info co lại, ưu tiên chỗ cho controls */
.player-media {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1 1 0;
  min-width: 0;
  max-width: 42%;
  text-align: left;
  background: transparent;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 0;
  overflow: hidden;
}

@media (min-width: 480px) {
  .player-media { max-width: 48%; gap: 10px; }
}

@media (min-width: 768px) {
  .player-media { max-width: none; }
}

.player-thumb {
  width: var(--thumb-size);
  height: var(--thumb-size);
  border-radius: var(--radius-full);
  object-fit: cover;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
}

@media (min-width: 768px) {
  .player-thumb { border-radius: var(--radius-sm); }
}

.player-info {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.player-title {
  font-size: 11px;
  font-weight: 600;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--primary);
}

@media (min-width: 480px) {
  .player-title { font-size: 12px; }
}

@media (min-width: 768px) {
  .player-title { font-size: 13px; }
}

.player-media:hover .player-title { opacity: 0.9; }

.player-story {
  font-size: 10px;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.25;
}

@media (min-width: 480px) {
  .player-story { font-size: 11px; }
}

.player-time {
  font-size: 10px;
  color: var(--text-muted);
  font-variant-numeric: tabular-nums;
  line-height: 1.25;
  margin-top: 1px;
}
.player-time.buffering { color: var(--primary); }

@media (min-width: 480px) {
  .player-time { font-size: 11px; }
}

/* Luôn hiển thị đủ 7 nút */
.player-controls {
  display: flex;
  align-items: center;
  gap: 1px;
  flex: 0 0 auto;
  margin-left: auto;
}

@media (min-width: 480px) {
  .player-controls { gap: 3px; }
}

@media (min-width: 768px) {
  .player-controls { gap: 5px; }
}

.player-play-btn {
  width: var(--play-size);
  height: var(--play-size);
  border-radius: var(--radius-full);
  background: var(--gradient-premium);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, transform 0.15s;
  flex-shrink: 0;
}
.player-play-btn:hover { opacity: 0.9; transform: scale(1.05); }
.player-play-btn svg { width: 14px; height: 14px; }

@media (min-width: 480px) {
  .player-play-btn svg { width: 16px; height: 16px; }
}

@media (min-width: 768px) {
  .player-play-btn svg { width: 17px; height: 17px; }
}

.player-speed {
  min-width: 28px;
  height: 26px;
  padding: 0 4px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-size: 10px;
  font-weight: 700;
  transition: all 0.2s;
  flex-shrink: 0;
}
.player-speed:hover { border-color: var(--primary); color: var(--text); }

@media (min-width: 480px) {
  .player-speed { min-width: 34px; height: 30px; font-size: 11px; padding: 0 6px; }
}

@media (min-width: 768px) {
  .player-speed { min-width: 40px; height: 32px; font-size: 12px; padding: 0 8px; }
}

.icon-btn {
  width: var(--ctrl-size);
  height: var(--ctrl-size);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: all 0.2s;
  flex-shrink: 0;
}
.icon-btn:hover:not(:disabled) { background: var(--bg-muted); color: var(--text); }
.icon-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.icon-btn svg { width: 14px; height: 14px; }

@media (min-width: 480px) {
  .icon-btn svg { width: 16px; height: 16px; }
}

@media (min-width: 768px) {
  .icon-btn svg { width: 18px; height: 18px; }
}

.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<template>
  <transition name="np-slide">
    <div v-if="audio.expanded && audio.currentEpisode" class="now-playing">
      <div class="np-bg">
        <img v-if="story?.image" :src="story.image" alt="" />
        <div class="np-bg-overlay"></div>
      </div>

      <div class="np-inner">
        <!-- Header -->
        <header class="np-header">
          <button class="np-icon-btn" title="Thu nhỏ" @click="audio.collapse()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="np-header-center">
            <div class="np-label">ĐANG PHÁT</div>
            <div class="np-story">{{ story?.title }}</div>
          </div>
          <button class="np-icon-btn" title="Đóng" @click="audio.stop()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>
        </header>

        <!-- Body -->
        <div class="np-body">
          <div :class="['np-art-wrap', { spinning: audio.isPlaying }]">
            <img class="np-art" :src="story?.image" alt="Album art" />
          </div>

          <div class="np-track">
            <div class="np-track-title">{{ currentEpisodeLine }}</div>
          </div>

          <div class="np-progress" @click="handleSeek">
            <div class="np-progress-fill" :style="{ width: audio.progress + '%' }"></div>
            <div class="np-progress-dot" :style="{ left: audio.progress + '%' }"></div>
          </div>
          <div class="np-time">
            <span>{{ formatTime(audio.currentTime) }}</span>
            <span>{{ formatTime(audio.duration) }}</span>
          </div>

          <div class="np-controls">
            <button class="np-ctrl" title="Lùi 15 giây" @click="audio.skip(-15)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
            </button>
            <button class="np-ctrl" title="Tập trước" :disabled="!audio.hasPrev" @click="audio.prev()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
            </button>
            <button class="np-play" @click="audio.togglePlay()" :disabled="!audio.currentEpisode.audio_url">
              <svg v-if="audio.isBuffering" class="spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
              <svg v-else-if="audio.isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
            </button>
            <button class="np-ctrl" title="Tập tiếp" :disabled="!audio.hasNext" @click="audio.next()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
            </button>
            <button class="np-ctrl" title="Tiến 15 giây" @click="audio.skip(15)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M12 7v5l4 2"/></svg>
            </button>
          </div>

          <div class="np-secondary">
            <button class="np-sec-btn" title="Tốc độ" @click="audio.cycleSpeed()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m6 6 3.5 3.5"/><circle cx="12" cy="14" r="8"/></svg>
              {{ audio.speedLabel }}
            </button>
            <button class="np-sec-btn" :title="audio.muted ? 'Bật tiếng' : 'Tắt tiếng'" @click="audio.toggleMute()">
              <svg v-if="audio.muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
            </button>
            <button :class="['np-sec-btn', { active: audio.sleepTimer > 0 }]" title="Hẹn giờ" @click="audio.cycleSleepTimer()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              {{ audio.sleepTimer > 0 ? audio.sleepTimer + 'p' : 'Hẹn giờ' }}
            </button>
          </div>
        </div>

        <!-- Queue -->
        <div v-if="upcoming.length" class="np-queue">
          <h3 class="np-queue-title">Tập tiếp theo</h3>
          <div
            v-for="ep in upcoming"
            :key="ep.id"
            :class="['np-queue-item', { playing: audio.isCurrent(ep.id), locked: playBlocked(ep) }]"
            @click="selectEpisode(ep)"
          >
            <div class="np-queue-thumb">
              <img :src="story?.image" alt="" />
              <div v-if="!playBlocked(ep)" class="np-queue-overlay">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
              </div>
            </div>
            <div class="np-queue-info">
              <div class="np-queue-name">{{ formatEpisodeWithTitle(ep.episode_number, ep.title) }}</div>
              <div class="np-queue-meta">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ ep.duration }}
              </div>
            </div>
            <VipBadge v-if="ep.is_premium" size="md" extra-class="np-queue-vip" />
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'
import { useAudioStore } from '@/stores/audioStore'
import { usePlayAccess } from '@/composables/usePlayAccess'
import { formatTime, formatEpisodeWithTitle } from '@/utils/helpers'
import VipBadge from '@/components/VipBadge.vue'

const audio = useAudioStore()
const { playBlocked, ensurePlayAccess } = usePlayAccess()

const story = computed(() => audio.currentStory)

const currentEpisodeLine = computed(() =>
  formatEpisodeWithTitle(
    audio.currentEpisode?.episode_number,
    audio.currentEpisode?.title
  )
)

const upcoming = computed(() => {
  const idx = audio.currentIndex
  if (idx < 0) return audio.queue.slice(0, 8)
  return audio.queue.slice(idx, idx + 8)
})

const selectEpisode = (ep) => {
  if (!ensurePlayAccess(ep)) return
  audio.playEpisode(story.value, ep, audio.queue)
}

const handleSeek = (e) => {
  if (!audio.duration) return
  const rect = e.currentTarget.getBoundingClientRect()
  const percent = ((e.clientX - rect.left) / rect.width) * 100
  audio.seekToPercent(percent)
}
</script>

<style scoped>
.now-playing {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: var(--bg);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.np-bg { position: fixed; inset: 0; z-index: 0; }
.np-bg img { width: 100%; height: 100%; object-fit: cover; filter: blur(40px); opacity: 0.25; transform: scale(1.2); }
.np-bg-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(9, 9, 11, 0.6), var(--bg) 70%); }

.np-inner {
  position: relative;
  z-index: 1;
  max-width: 560px;
  margin: 0 auto;
  padding: 16px 20px 48px;
  min-height: 100%;
}

/* Header */
.np-header { display: flex; align-items: center; gap: 12px; padding: 8px 0 16px; }
.np-header-center { flex: 1; text-align: center; min-width: 0; }
.np-label { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; color: var(--primary); }
.np-story { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.np-icon-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-full); color: var(--text-muted); transition: all 0.2s; flex-shrink: 0; }
.np-icon-btn:hover { background: var(--bg-muted); color: var(--text); }
.np-icon-btn svg { width: 22px; height: 22px; }

/* Body */
.np-body { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 12px 0 24px; }

.np-art-wrap { width: 260px; max-width: 260px; aspect-ratio: 1; border-radius: 50%; padding: 7px; margin: 16px 0 28px; }
.np-art-wrap.spinning { animation: rotate 20s linear infinite; }
@keyframes rotate { to { transform: rotate(360deg); } }
.np-art { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 5px solid var(--bg); }

.np-track { margin-bottom: 24px; max-width: 100%; }
.np-track-title { font-size: 20px; font-weight: 700; line-height: 1.3; }

.np-progress { width: 100%; height: 6px; background: var(--border-strong); border-radius: 3px; position: relative; margin-bottom: 8px; cursor: pointer; }
.np-progress-fill { position: absolute; top: 0; left: 0; height: 100%; background: var(--gradient-premium); border-radius: 3px; pointer-events: none; }
.np-progress-dot { position: absolute; top: 50%; width: 14px; height: 14px; background: #fff; border-radius: 50%; transform: translate(-50%, -50%); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5); pointer-events: none; }
.np-time { width: 100%; display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 24px; font-variant-numeric: tabular-nums; }

.np-controls { display: flex; align-items: center; gap: 18px; margin-bottom: 22px; }
.np-ctrl { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; color: var(--text); border-radius: 50%; transition: all 0.2s; }
.np-ctrl:hover:not(:disabled) { background: var(--bg-muted); color: var(--primary); }
.np-ctrl:disabled { opacity: 0.3; cursor: not-allowed; }
.np-ctrl svg { width: 26px; height: 26px; }
.np-play { width: 72px; height: 72px; border-radius: 50%; background: var(--gradient-premium); color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(168, 85, 247, 0.45); transition: transform 0.1s; }
.np-play:active { transform: scale(0.95); }
.np-play:disabled { opacity: 0.5; cursor: not-allowed; }
.np-play svg { width: 32px; height: 32px; }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.np-secondary { display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; }
.np-sec-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: var(--radius-full); border: 1px solid var(--border); color: var(--text-muted); font-size: 12px; font-weight: 600; transition: all 0.2s; }
.np-sec-btn:hover { border-color: var(--primary); color: var(--text); }
.np-sec-btn.active { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.np-sec-btn svg { width: 15px; height: 15px; }

/* Queue */
.np-queue { margin-top: 8px; }
.np-queue-title { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
.np-queue-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; }
.np-queue-item:last-child { border-bottom: none; }
.np-queue-item.playing { background: rgba(168, 85, 247, 0.06); border-radius: var(--radius-md); padding: 10px 12px; margin: 0 -12px; }
.np-queue-item.locked { opacity: 0.6; cursor: default; }
.np-queue-thumb { width: 52px; height: 52px; border-radius: var(--radius-sm); overflow: hidden; position: relative; flex-shrink: 0; }
.np-queue-thumb img { width: 100%; height: 100%; object-fit: cover; }
.np-queue-overlay { position: absolute; inset: 0; background: rgba(0, 0, 0, 0.35); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
.np-queue-item:hover:not(.locked) .np-queue-overlay { opacity: 1; }
.np-queue-overlay svg { width: 20px; height: 20px; color: #fff; }
.np-queue-info { flex: 1; min-width: 0; }
.np-queue-name { font-size: 14px; font-weight: 600; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.np-queue-meta { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.np-queue-meta svg { width: 12px; height: 12px; }
/* Transition: trượt từ dưới lên */
.np-slide-enter-active, .np-slide-leave-active { transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1); }
.np-slide-enter-from, .np-slide-leave-to { transform: translateY(100%); }
</style>

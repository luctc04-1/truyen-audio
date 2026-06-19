<template>
  <div class="episode-page" style="padding-bottom:100px;">
    <!-- Hero bg with blur -->
    <div style="position:relative;overflow:hidden;min-height:280px;">
      <div class="hero-bg-wrapper">
        <img v-if="story" :src="story.image" alt="" style="width:100%;height:100%;object-fit:cover;filter:blur(28px);opacity:0.2;">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(9,9,11,0.3),var(--bg));"></div>
      </div>

      <div class="container" style="position:relative;padding-top:24px;">
        <!-- Back link -->
        <router-link :to="`/story/${route.params.storyId}`" class="back-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
          {{ story?.title || 'Quay lại truyện' }}
        </router-link>

        <!-- Player card -->
        <div class="ep-player-section animate-in pulse-glow">
          <img class="ep-album-art" :src="story?.image || 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=300&q=80'" alt="Album art">
          <div class="ep-track-title">{{ currentEpisode?.title || 'Tập 1: Nuôi Vợ Hào Môn Mà Không Hay Biết' }}</div>
          <div class="ep-track-series">{{ story?.author || 'Sói Review 2510' }}</div>

          <!-- Progress -->
          <div class="ep-progress-bar" @click="handleSeek">
            <div class="ep-progress-fill" :style="{ width: progress + '%' }"></div>
            <div class="ep-progress-dot" :style="{ left: progress + '%' }"></div>
          </div>
          <div class="ep-time-row">
            <span>{{ formatTime(currentTime) }}</span>
            <span>{{ durationFormatted }}</span>
          </div>

          <!-- Main controls -->
          <div class="ep-main-controls">
            <button class="ep-control-btn" title="Lùi 15 giây" @click="skipTime(-15)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
            </button>
            <button class="ep-control-btn" title="Tập trước" @click="prevEpisode">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            </button>
            <button class="ep-play-btn" @click="togglePlay">
              <svg v-if="isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
            </button>
            <button class="ep-control-btn" title="Tập tiếp" @click="nextEpisode">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 5 7 7-7 7"/><path d="M5 12h14"/></svg>
            </button>
            <button class="ep-control-btn" title="Tiến 15 giây" @click="skipTime(15)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M12 7v5l4 2"/></svg>
            </button>
          </div>

          <!-- Secondary controls -->
          <div class="ep-secondary-controls">
            <button class="ep-speed-btn" @click="cycleSpeed">{{ speeds[speedIdx].toFixed(2).replace('.00','.0') }}x</button>
            <button class="icon-btn" title="Shuffle" style="color:var(--text-muted);">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18h1.4c1.3 0 2.5-.6 3.3-1.7l6.1-8.6c.7-1.1 2-1.7 3.3-1.7H22"/><path d="m18 2 4 4-4 4"/><path d="M2 6h1.9c1.5 0 2.9.9 3.6 2.2"/><path d="M22 18h-5.9c-1.3 0-2.6-.7-3.3-1.8l-.5-.8"/><path d="m18 14 4 4-4 4"/></svg>
            </button>
            <button class="icon-btn" title="Lặp lại" :style="{ color: isLoop ? 'var(--primary)' : 'var(--text-muted)' }" @click="isLoop = !isLoop">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </button>
            <button class="icon-btn" title="Danh sách phát" style="color:var(--text-muted);">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>
          </div>
        </div>

        <!-- Episode list in this story -->
        <div style="margin-top:24px;">
          <h2 style="font-size:16px;font-weight:700;margin-bottom:12px;">Tập tiếp theo</h2>
          <div style="display:flex;flex-direction:column;gap:4px;">
            <div v-for="ep in nextEpisodes" :key="ep.id" :class="['episode-item', { playing: ep.id == route.params.epId }]" @click="$router.push(`/episode/${story?.id || 1}/${ep.id}`)">
              <div class="episode-thumb">
                <img :src="story?.image || 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=100&q=80'" alt="">
                <div class="episode-thumb-overlay"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg></div>
              </div>
              <div class="episode-info">
                <div class="episode-title">{{ ep.title }}</div>
                <div class="episode-meta">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  {{ ep.duration }}
                </div>
              </div>
            </div>

            <!-- Locked episode -->
            <div class="episode-item locked">
              <div class="episode-thumb">
                <img :src="story?.image || 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=100&q=80'" alt="">
              </div>
              <div class="episode-info">
                <div class="episode-title">Tập 7: Nuôi Vợ Hào Môn Mà Không Hay Biết</div>
                <div class="episode-meta">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  1:15:22
                </div>
              </div>
              <div class="ep-locked">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                VIP
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStoryStore } from '../js/stores/storyStore'

const route = useRoute()
const router = useRouter()
const storyStore = useStoryStore()

const story = computed(() => storyStore.getStoryById(route.params.storyId))
const currentEpisode = computed(() => {
  if (!story.value || !story.value.episodes) return null
  return story.value.episodes.find(e => e.id == route.params.epId) || story.value.episodes[0]
})

const isPlaying = ref(true)
const progress = ref(13.1)
const currentTime = ref(522) // 08:42 in seconds
const totalSeconds = ref(3961) // 1:06:01 in seconds
const isLoop = ref(false)

const speeds = [0.75, 1.0, 1.25, 1.5, 2.0]
const speedIdx = ref(1)

const togglePlay = () => { isPlaying.value = !isPlaying.value }
const cycleSpeed = () => { speedIdx.value = (speedIdx.value + 1) % speeds.length }

const updateProgress = () => {
  progress.value = (currentTime.value / totalSeconds.value) * 100
}

const skipTime = (seconds) => {
  currentTime.value = Math.max(0, Math.min(totalSeconds.value, currentTime.value + seconds))
  updateProgress()
}

const handleSeek = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  progress.value = ((e.clientX - rect.left) / rect.width) * 100
  currentTime.value = Math.floor((progress.value / 100) * totalSeconds.value)
}

const formatTime = (secs) => {
  const h = Math.floor(secs / 3600)
  const m = Math.floor((secs % 3600) / 60)
  const s = Math.floor(secs % 60)
  if (h > 0) return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
  return `${m}:${s.toString().padStart(2, '0')}`
}

const durationFormatted = computed(() => formatTime(totalSeconds.value))

const prevEpisode = () => { alert('Đây là tập đầu tiên!') }
const nextEpisode = () => { router.push(`/story/${route.params.storyId}`) }

const nextEpisodes = [
  { id: 2, title: 'Tập 2: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:04:43' },
  { id: 3, title: 'Tập 3: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:19:55' },
  { id: 4, title: 'Tập 4: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:20:02' },
]
</script>

<style scoped>
.episode-page { min-height: 100vh; }
.container { max-width: 680px; margin: 0 auto; padding: 0 16px; }

.hero-bg-wrapper { position: absolute; inset: 0; }
.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 20px; transition: color 0.2s; text-decoration: none; }
.back-link:hover { color: var(--text); }

.animate-in { animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* Player section */
.ep-player-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: var(--shadow-lg); margin-bottom: 32px; }
.ep-album-art { width: 180px; height: 180px; border-radius: var(--radius-md); object-fit: cover; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.6); }
.ep-track-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; line-height: 1.3; }
.ep-track-series { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }

/* Progress bar */
.ep-progress-bar { width: 100%; height: 6px; background: var(--border-strong); border-radius: 3px; position: relative; margin-bottom: 12px; cursor: pointer; }
.ep-progress-fill { position: absolute; top: 0; left: 0; height: 100%; background: var(--primary); border-radius: 3px; pointer-events: none; }
.ep-progress-dot { position: absolute; top: 50%; width: 12px; height: 12px; background: #fff; border-radius: 50%; transform: translate(-50%, -50%); box-shadow: 0 2px 4px rgba(0,0,0,0.5); opacity: 0; transition: opacity 0.2s; pointer-events: none; }
.ep-progress-bar:hover .ep-progress-dot { opacity: 1; }
.ep-time-row { width: 100%; display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 24px; font-variant-numeric: tabular-nums; }

/* Main controls */
.ep-main-controls { display: flex; align-items: center; gap: 20px; margin-bottom: 24px; }
.ep-control-btn { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; color: var(--text); background: transparent; border: none; border-radius: 50%; cursor: pointer; transition: all 0.2s; }
.ep-control-btn:hover { background: var(--bg-muted); color: var(--primary); }
.ep-control-btn svg { width: 24px; height: 24px; }
.ep-play-btn { width: 64px; height: 64px; border-radius: 50%; background: var(--text); color: var(--bg); display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: transform 0.1s; }
.ep-play-btn:active { transform: scale(0.95); }
.ep-play-btn svg { width: 32px; height: 32px; }

/* Secondary controls */
.ep-secondary-controls { display: flex; align-items: center; gap: 16px; }
.ep-speed-btn { padding: 4px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.ep-speed-btn:hover { border-color: var(--primary); color: var(--text); }
.icon-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); background: transparent; border: none; cursor: pointer; transition: all 0.2s; }
.icon-btn:hover { background: var(--bg-muted); color: var(--text) !important; }
.icon-btn svg { width: 18px; height: 18px; }

/* Episode list */
.episode-item { display: flex; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; }
.episode-item:last-child { border-bottom: none; }
.episode-item.playing { background: rgba(168,85,247,0.05); padding: 14px 12px; border-radius: var(--radius-md); margin: 0 -12px; }
.episode-thumb { width: 72px; height: 108px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; position: relative; }
.episode-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.episode-item:hover:not(.locked) .episode-thumb img { transform: scale(1.05); }
.episode-thumb-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.episode-item:hover:not(.locked) .episode-thumb-overlay { opacity: 1; }
.episode-thumb-overlay svg { width: 24px; height: 24px; color: #fff; }
.episode-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.episode-title { font-size: 15px; font-weight: 600; line-height: 1.4; margin-bottom: 6px; }
.episode-meta { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); }
.episode-meta svg { width: 12px; height: 12px; }

.episode-item.locked { opacity: 0.65; cursor: default; }
.episode-item.locked .episode-thumb { opacity: 0.6; }
.ep-locked { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: var(--amber); flex-shrink: 0; padding: 4px 10px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: var(--radius-full); height: fit-content; margin-top: auto; margin-bottom: auto; }
.ep-locked svg { width: 12px; height: 12px; stroke: var(--amber); }
</style>

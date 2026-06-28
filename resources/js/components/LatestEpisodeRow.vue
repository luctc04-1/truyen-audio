<template>
  <div
    :class="['update-row', { locked: playBlocked, playing: isCurrent }]"
    @click="playEpisode"
  >
    <div class="update-thumb-wrap">
      <img
        :src="imgSrc"
        :alt="item.series.title"
        class="update-thumb"
        loading="lazy"
        @error="onImgError"
      />
      <div v-if="!playBlocked" class="update-thumb-overlay">
        <svg v-if="isCurrent && audioStore.isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
          <rect x="6" y="4" width="4" height="16" /><rect x="14" y="4" width="4" height="16" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
          <polygon points="6 3 20 12 6 21 6 3" />
        </svg>
      </div>
    </div>
    <div class="update-body">
      <div class="update-top">
        <router-link
          :to="`/story/${item.series.id}`"
          class="update-series-title"
          @click.stop
        >
          {{ item.series.title }}
        </router-link>
      </div>
      <div class="update-sub">
        {{ episodeLine }}
      </div>
      <div class="update-meta">
        <span class="meta-item">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          {{ item.duration || '0:00' }}
        </span>
        <span class="meta-item">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
          </svg>
          {{ item.play_count }} lượt nghe
        </span>
        <span class="meta-item">{{ formattedDate }}</span>
      </div>
    </div>
    <VipBadge v-if="item.is_premium" class="update-vip" @click.stop />
  </div>
</template>

<script setup>
import { ref, computed, watchEffect } from 'vue'
import { useStoryStore } from '@/stores/storyStore'
import { useAudioStore } from '@/stores/audioStore'
import { usePlayAccess } from '@/composables/usePlayAccess'
import { formatEpisodeWithTitle } from '@/utils/helpers'
import VipBadge from '@/components/VipBadge.vue'

const FALLBACK = 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80'

const props = defineProps({
  item: { type: Object, required: true },
})

const storyStore = useStoryStore()
const audioStore = useAudioStore()
const { auth, playBlocked: isPlayBlocked, ensurePlayAccess } = usePlayAccess()

const imgSrc = ref(props.item.series?.image || FALLBACK)

watchEffect(() => {
  imgSrc.value = props.item.series?.image || FALLBACK
})

const onImgError = () => {
  imgSrc.value = FALLBACK
}

const formattedDate = computed(() => {
  const iso = props.item.published_at
  if (!iso) return ''
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`
})

const isCurrent = computed(() => audioStore.isCurrent(props.item.id))

const playBlocked = computed(() => isPlayBlocked(props.item))

const episodeLine = computed(() =>
  formatEpisodeWithTitle(props.item.episode_number, props.item.series.title)
)

const playEpisode = async () => {
  if (!ensurePlayAccess(props.item)) return

  const seriesId = props.item.series?.id
  if (!seriesId) return

  let story = storyStore.getStoryById(seriesId)
  const needsReload = !story?.episodes?.length
    || (auth.isPremium && story.episodes.some((e) => e.is_premium && !e.audio_url))

  if (needsReload) {
    story = await storyStore.reloadStoryDetail(seriesId)
  }
  if (!story) return

  const episodes = story.episodes ?? []
  const episode = episodes.find((e) => String(e.id) === String(props.item.id))
  if (!episode) return

  audioStore.playEpisode(story, episode, episodes)
}
</script>

<style scoped>
.update-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}

.update-row :deep(.update-vip) {
  flex-shrink: 0;
  margin-left: auto;
}

.update-row:hover {
  background: rgba(168, 85, 247, 0.06);
  border-color: rgba(168, 85, 247, 0.25);
}

.update-row.locked {
  opacity: 0.65;
  cursor: default;
}

.update-row.playing {
  border-color: rgba(168, 85, 247, 0.45);
  background: rgba(168, 85, 247, 0.08);
}

.update-thumb-wrap {
  position: relative;
  width: 72px;
  height: 72px;
  flex-shrink: 0;
  border-radius: 10px;
  overflow: hidden;
}

.update-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  background: var(--bg-muted);
}

.update-row.locked .update-thumb {
  opacity: 0.6;
}

.update-thumb-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.update-row:hover .update-thumb-overlay,
.update-row.playing .update-thumb-overlay {
  opacity: 1;
}

.update-thumb-overlay svg {
  width: 22px;
  height: 22px;
  color: #fff;
}

.update-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.update-top {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  width: 100%;
}

.update-series-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-decoration: none;
  transition: color 0.2s;
}

.update-series-title:hover {
  color: var(--primary);
}

.update-row.playing .update-sub {
  color: var(--primary);
}

.update-sub {
  font-size: 13px;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.2s;
}

.update-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 14px;
  font-size: 12px;
  color: var(--text-muted);
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.meta-item svg {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
}
</style>

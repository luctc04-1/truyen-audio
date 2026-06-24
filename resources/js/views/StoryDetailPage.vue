<template>
  <div class="story-detail-page">
    <!-- DETAIL HERO -->
    <section class="detail-hero">
      <div class="detail-hero-bg">
        <img v-if="story" :src="story.image" :alt="story.title" />
        <div class="detail-hero-bg-overlay"></div>
      </div>
      <div class="container">
        <!-- Back link -->
        <button @click="goBack"
          style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);margin-bottom:20px;transition:color 0.2s;background:none;border:none;cursor:pointer;padding:0;position:relative;z-index:1;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6" />
          </svg>
          {{ backLabel }}
        </button>

        <div v-if="showSkeleton" class="detail-hero-inner detail-skeleton">
          <div class="sk sk-cover"></div>
          <div class="sk-info">
            <div class="sk sk-line" style="width:60%;height:30px;"></div>
            <div class="sk sk-line" style="width:40%;"></div>
            <div class="sk sk-line" style="width:80%;"></div>
            <div class="sk sk-line" style="width:50%;"></div>
          </div>
        </div>

        <div v-else-if="story" class="detail-hero-inner animate-in">
          <div class="detail-cover">
            <img :src="story.image" :alt="story.title" />
          </div>
          <div class="detail-info">
            <div class="detail-badges">
              <span
                v-for="label in storyCategoryDisplay"
                :key="label"
                class="category-pill"
              >{{ label }}</span>
              <VipBadge v-if="story.is_premium" size="md" extra-class="badge-status" />
              <span v-else-if="story.status === 'completed'" class="badge badge-success badge-status">Hoàn thành</span>
              <span v-else class="badge badge-status">Đang cập nhật</span>
            </div>
            <h1 class="detail-title">{{ story.title }}</h1>
            <div class="detail-stats">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3" />
                </svg>
                {{ story.plays }} lượt nghe
              </span>
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                {{ story.episodeCount }} tập
              </span>
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"
                  stroke-width="1">
                  <path
                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.60l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" />
                </svg>
                {{ story.rating }}
              </span>
            </div>
            <div class="detail-actions">
              <button v-if="episodes.length" class="btn btn-lg btn-primary" @click="playFirst">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <polygon points="6 3 20 12 6 21 6 3" />
                </svg>
                Nghe ngay
              </button>
              <button class="btn btn-lg btn-outline" @click="toggleFollow">
                <svg v-if="isFollowed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                  fill="#ef4444">
                  <path
                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                </svg>
                {{ isFollowed ? 'Đang theo dõi' : 'Theo dõi' }}
              </button>
              <button class="btn btn-lg btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="18" cy="5" r="3" />
                  <circle cx="6" cy="12" r="3" />
                  <circle cx="18" cy="19" r="3" />
                  <line x1="8.59" x2="15.42" y1="13.51" y2="17.49" />
                  <line x1="15.41" x2="8.59" y1="6.51" y2="10.49" />
                </svg>
              </button>
            </div>
            <p class="detail-note">Theo dõi bộ truyện để nhận thông báo khi có tập mới ra mắt</p>
          </div>
        </div>
      </div>
    </section>

    <!-- TABS -->
    <section v-if="story || showSkeleton" style="padding-top:0;">
      <div class="container">
        <div v-if="showSkeleton" class="tabs-bar">
          <div class="sk sk-tab"></div>
          <div class="sk sk-tab"></div>
        </div>
        <div v-else class="tabs-bar">
          <button :class="['tab-btn', { active: activeTab === 'episodes' }]" @click="activeTab = 'episodes'">
            Danh sách tập ({{ story?.episodeCount ?? 0 }})
          </button>
          <button :class="['tab-btn', { active: activeTab === 'reviews' }]" @click="activeTab = 'reviews'">
            Đánh giá ({{ ratingCount }})
          </button>
        </div>

        <!-- Episodes tab -->
        <div :class="['tab-content', { active: activeTab === 'episodes' }]">
          <div v-if="showSkeleton" style="display:flex;flex-direction:column;gap:8px;">
            <div v-for="n in 4" :key="'sk-ep-' + n" class="episode-skeleton">
              <div class="sk" style="width:72px;height:108px;border-radius:8px;"></div>
              <div style="flex:1;display:flex;flex-direction:column;gap:8px;justify-content:center;">
                <div class="sk sk-line" style="width:70%;"></div>
                <div class="sk sk-line" style="width:30%;"></div>
              </div>
            </div>
          </div>
          <div v-else-if="!episodes.length" style="padding:24px;text-align:center;color:var(--text-muted);">
            Chưa có tập nào.
          </div>
          <div v-else style="display:flex;flex-direction:column;gap:4px;">
            <div
              v-for="episode in episodes"
              :key="episode.id"
              :class="['episode-item', { locked: playBlocked(episode), playing: audioStore.isCurrent(episode.id) }]"
              :style="playBlocked(episode) ? 'opacity:0.65;cursor:default;' : ''"
              @click="playEpisode(episode)"
            >
              <div class="episode-thumb" :style="playBlocked(episode) ? 'opacity:0.6;' : ''">
                <img :src="story.image" :alt="episode.title" />
                <div v-if="!playBlocked(episode)" class="episode-thumb-overlay">
                  <svg v-if="isEpisodePlaying(episode)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <rect x="6" y="4" width="4" height="16" /><rect x="14" y="4" width="4" height="16" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon points="6 3 20 12 6 21 6 3" />
                  </svg>
                </div>
              </div>
              <div class="episode-info">
                <div class="episode-title">{{ formatEpisodeWithTitle(episode.episode_number, episode.title) }}</div>
                <div class="episode-meta">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  {{ episode.duration || '0:00' }}
                </div>
              </div>
              <VipBadge v-if="episode.is_premium" size="md" />
              <button v-if="!playBlocked(episode)" class="icon-btn episode-play">
                <svg v-if="isEpisodePlaying(episode)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                  <rect x="6" y="4" width="4" height="16" /><rect x="14" y="4" width="4" height="16" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                  <polygon points="6 3 20 12 6 21 6 3" />
                </svg>
              </button>
            </div>

            <div
              v-if="hasPremiumEpisodes && !auth.isPremium"
              style="background:linear-gradient(135deg,rgba(168,85,247,0.1),rgba(236,72,153,0.08));border:1px solid rgba(168,85,247,0.25);border-radius:12px;padding:16px;text-align:center;margin-top:8px;">
              <p style="font-size:13px;color:#a1a1aa;margin-bottom:10px;">🔒 Một số tập chỉ dành cho thành viên VIP</p>
              <router-link to="/vip">
                <button class="btn btn-primary btn-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path
                      d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z" />
                    <path d="M5 21h14" />
                  </svg>
                  Đăng ký VIP từ 37.000đ/tháng
                </button>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Reviews tab -->
        <div :class="['tab-content', { active: activeTab === 'reviews' }]">
          <StoryRatingsTab
            v-if="story"
            :series-id="story.id"
            @count-change="ratingCount = $event"
            @rated="onRated"
          />
        </div>

        <!-- Comments — luôn hiển thị dưới tabs -->
        <StoryCommentsSection v-if="story" :series-id="story.id" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStoryStore } from '@/stores/storyStore'
import { useAudioStore } from '@/stores/audioStore'
import { usePlayAccess } from '@/composables/usePlayAccess'
import { formatEpisodeWithTitle } from '@/utils/helpers'
import VipBadge from '@/components/VipBadge.vue'
import StoryRatingsTab from '@/components/StoryRatingsTab.vue'
import StoryCommentsSection from '@/components/StoryCommentsSection.vue'
import { isFollowed as checkFollowed, toggleFollow as toggleFollowStorage } from '@/utils/follows'

const route = useRoute()
const router = useRouter()
const storyStore = useStoryStore()
const audioStore = useAudioStore()
const { auth, playBlocked, ensurePlayAccess } = usePlayAccess()

// Xác định trang trước dựa vào lịch sử điều hướng của Vue Router
const backRoute = computed(() => {
  const prev = window.history.state?.back ?? ''
  if (prev === '/' || prev.startsWith('/?')) return '/'
  return '/library'
})

const backLabel = computed(() => {
  return backRoute.value === '/' ? 'Trang chủ' : 'Kho truyện'
})

const goBack = () => {
  if (window.history.state?.back) {
    router.back()
  } else {
    router.push('/library')
  }
}
const activeTab = ref('episodes')
const isFollowed = ref(false)
const ratingCount = ref(0)

const onRated = (average) => {
  if (story.value && average != null) {
    storyStore.patchStoryRating(story.value.id, average)
  }
}

const story = computed(() => storyStore.getStoryById(route.params.id))
const storyCategoryLabels = computed(() => {
  const labels = story.value?.category_labels
  if (Array.isArray(labels) && labels.length) return labels
  if (story.value?.genre_label) return [story.value.genre_label]
  return []
})
const storyCategoryDisplay = computed(() =>
  storyStore.mapCategoryLabels(storyCategoryLabels.value)
)
const episodes = computed(() => story.value?.episodes ?? [])
const hasPremiumEpisodes = computed(() => episodes.value.some((e) => e.is_premium))
// Đồng bộ skeleton: thông tin chi tiết + danh sách tập cùng load, cùng hiện.
const hasDetail = computed(() => episodes.value.length > 0)
const showSkeleton = computed(() => storyStore.detailLoading && !hasDetail.value)
const toggleFollow = () => {
  if (!story.value?.id) return
  isFollowed.value = toggleFollowStorage(story.value.id)
}

const isEpisodePlaying = (episode) =>
  audioStore.isCurrent(episode.id) && audioStore.isPlaying

// Click tập -> phát ngay ở mini player (không rời trang).
const playEpisode = (episode) => {
  if (!ensurePlayAccess(episode)) return
  audioStore.playEpisode(story.value, episode, episodes.value)
}

const playFirst = () => {
  const first = auth.isPremium
    ? episodes.value[0]
    : episodes.value.find((e) => !e.is_premium)
  if (first) playEpisode(first)
}

onMounted(() => {
  storyStore.loadStoryDetail(route.params.id)
  isFollowed.value = checkFollowed(route.params.id)
})
watch(() => route.params.id, (id) => {
  if (id) {
    storyStore.loadStoryDetail(id)
    isFollowed.value = checkFollowed(id)
  }
})
watch(() => auth.isPremium, () => {
  if (route.params.id) {
    storyStore.reloadStoryDetail(route.params.id)
  }
})
</script>

<style scoped>
/* ===== CSS Variables ===== */
:root {
  --bg: #09090b;
  --bg-card: #111113;
  --bg-muted: #18181b;
  --border: #27272a;
  --border-strong: #3f3f46;
  --text: #fafafa;
  --text-muted: #a1a1aa;
  --text-faint: #71717a;
  --primary: #a855f7;
  --primary-hover: #9333ea;
  --primary-light: rgba(168, 85, 247, 0.12);
  --primary-light2: rgba(168, 85, 247, 0.06);
  --success: #22c55e;
  --success-border: #16a34a;
  --amber: #f59e0b;
  --red: #ef4444;
  --gradient-premium: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.5);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.5);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-full: 9999px;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
}

@media (min-width: 640px) {
  .container {
    padding: 0 24px;
  }
}

@media (min-width: 1024px) {
  .container {
    padding: 0 32px;
  }
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 20px;
  height: 40px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
  cursor: pointer;
  border: none;
  background: none;
  font-family: inherit;
  outline: none;
}

.btn-sm {
  height: 32px;
  padding: 0 12px;
  font-size: 13px;
}

.btn-lg {
  height: 44px;
  padding: 0 28px;
  font-size: 15px;
}

.btn-primary {
  background: var(--gradient-premium);
  color: #fff;
}

.btn-primary:hover {
  opacity: 0.88;
  transform: translateY(-1px);
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border-strong);
  color: var(--text);
}

.btn-outline:hover {
  background: var(--bg-muted);
  border-color: var(--primary);
}

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

.badge-tag {
  font-size: 10px;
  padding: 1px 7px;
}

.category-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: var(--radius-full);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 12px;
  font-weight: 500;
  border: 1px solid var(--border);
  line-height: 1.25;
}

.badge-status {
  font-size: 10px;
  padding: 2px 8px;
}

.badge-success {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  border-color: var(--success-border);
}

.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: all 0.2s;
  background: none;
  border: none;
  cursor: pointer;
  font-family: inherit;
  outline: none;
}

.icon-btn:hover {
  background: var(--bg-muted);
  color: var(--text);
}

.icon-btn svg {
  width: 20px;
  height: 20px;
}

.detail-hero {
  position: relative;
  overflow: hidden;
  padding: 32px 0;
}

.detail-hero-bg {
  position: absolute;
  inset: 0;
}

.detail-hero-bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(24px);
  opacity: 0.25;
}

.detail-hero-bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(9, 9, 11, 0.5), var(--bg));
}

.detail-hero-inner {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

@media (min-width: 768px) {
  .detail-hero-inner {
    flex-direction: row;
    gap: 40px;
  }
}

.detail-cover {
  width: 180px;
  margin: 0 auto;
  flex-shrink: 0;
}

@media (min-width: 768px) {
  .detail-cover {
    width: 210px;
    margin: 0;
  }
}

.detail-cover img {
  width: 100%;
  aspect-ratio: 2/3;
  object-fit: cover;
  border-radius: var(--radius-xl);
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.6);
}

.detail-info {
  flex: 1;
  text-align: center;
}

@media (min-width: 768px) {
  .detail-info {
    text-align: left;
  }
}

.detail-badges {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 8px;
  margin-bottom: 14px;
}

@media (min-width: 768px) {
  .detail-badges {
    justify-content: flex-start;
  }
}

.detail-title {
  font-size: clamp(22px, 4vw, 36px);
  font-weight: 700;
  margin-bottom: 20px;
  line-height: 1.2;
}

.detail-stats {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 24px;
}

@media (min-width: 768px) {
  .detail-stats {
    justify-content: flex-start;
  }
}

.detail-stats span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.detail-stats svg {
  width: 14px;
  height: 14px;
}

.detail-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-bottom: 12px;
}

@media (min-width: 768px) {
  .detail-actions {
    justify-content: flex-start;
  }
}

.detail-note {
  font-size: 11px;
  color: var(--text-faint);
}

.tabs-bar {
  display: flex;
  gap: 2px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: var(--radius-sm);
  padding: 4px;
  margin-bottom: 20px;
}

.tab-btn {
  flex: 1;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: var(--text-muted);
  transition: all 0.2s;
  white-space: nowrap;
  border: none;
  background: none;
  cursor: pointer;
  font-family: inherit;
  outline: none;
}

.tab-btn:hover {
  color: var(--text);
}

.tab-btn.active {
  background: var(--bg-card);
  color: var(--text);
  box-shadow: var(--shadow-sm);
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.episode-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: var(--radius-md);
  transition: background 0.2s;
  cursor: pointer;
  overflow: hidden;
}

.episode-item:hover {
  background: var(--bg-muted);
}

.episode-item.playing {
  background: var(--primary-light);
}

.episode-thumb {
  width: 52px;
  height: 52px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  flex-shrink: 0;
  position: relative;
}

.episode-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.episode-thumb-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.episode-item:hover .episode-thumb-overlay {
  opacity: 1;
}

.episode-item.playing .episode-thumb-overlay {
  opacity: 1;
}

.episode-thumb-overlay svg {
  width: 20px;
  height: 20px;
  color: #fff;
}

.episode-info {
  flex: 1;
  min-width: 0;
}

.episode-title {
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 4px;
  transition: color 0.2s;
}

.episode-item.playing .episode-title {
  color: var(--primary);
}

.episode-meta {
  font-size: 11px;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
}

.episode-meta svg {
  width: 11px;
  height: 11px;
}

.episode-play {
  display: none;
  width: 36px;
  height: 36px;
}

@media (min-width: 640px) {
  .episode-play {
    display: flex;
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: fadeInUp 0.4s ease both;
}

/* ===== Skeletons ===== */
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
.sk-line { height: 12px; }
.sk-tab { width: 140px; height: 38px; border-radius: var(--radius-md); }
@keyframes shimmer { 100% { transform: translateX(100%); } }

.detail-skeleton { gap: 24px; }
.detail-skeleton .sk-cover {
  width: 200px;
  aspect-ratio: 3 / 4;
  border-radius: var(--radius-lg);
  flex-shrink: 0;
}
.detail-skeleton .sk-info {
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
  padding-top: 12px;
}
.episode-skeleton {
  display: flex;
  gap: 14px;
  padding: 12px 0;
}
</style>
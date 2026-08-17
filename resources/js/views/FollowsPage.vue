<template>
  <div class="follows-page">
    <div class="container">

      <!-- Header -->
      <div class="page-header">
        <button class="back-btn" @click="$router.back()">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <div>
          <h1 class="page-title">Truyện theo dõi</h1>
          <p class="page-subtitle">{{ items.length }} bộ truyện đang theo dõi</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="item-list">
        <div v-for="n in 4" :key="n" class="card-skeleton">
          <div class="sk-thumb"></div>
          <div class="sk-body">
            <div class="sk-line" style="width:75%"></div>
            <div class="sk-line" style="width:50%"></div>
            <div class="sk-line" style="width:35%"></div>
            <div class="sk-actions"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="!items.length" class="empty-state">
        <div class="empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        </div>
        <p class="empty-title">Chưa theo dõi truyện nào</p>
        <p class="empty-sub">Theo dõi truyện yêu thích để không bỏ lỡ tập mới</p>
        <router-link to="/" class="btn-explore">Khám phá truyện</router-link>
      </div>

      <!-- List -->
      <div v-else class="item-list">
        <div v-for="item in items" :key="item.id" class="follow-card">

          <!-- Thumbnail -->
          <div class="thumb-wrap" @click="goToStory(item)">
            <img :src="item.cover_url || SERIES_FALLBACK_COVER" :alt="item.title" />
            <div class="thumb-overlay"></div>
          </div>

          <!-- Info -->
          <div class="item-info">
            <div class="item-title" @click="goToStory(item)">{{ item.title }}</div>
            <div v-if="item.narrator" class="item-narrator">{{ item.narrator }}</div>

            <div class="item-stats">
              <span class="stat">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                {{ item.total_episodes || item.latest_episode_number || 0 }} tập
              </span>
              <span v-if="item.average_rating" class="stat">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                {{ item.average_rating }}
              </span>
            </div>

            <div v-if="item.is_complete" class="badge-complete">Hoàn thành</div>
            <div v-if="item.followed_at" class="item-followed-at">Theo dõi từ {{ item.followed_at }}</div>

            <div class="item-actions">
              <button class="btn-listen" @click="goToStory(item)">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                Nghe
              </button>
              <button class="btn-unfollow" :disabled="unfollowing === item.id" @click="unfollow(item)">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                {{ unfollowing === item.id ? 'Đang xử lý…' : 'Bỏ theo dõi' }}
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AuthService from '@/services/AuthService'
import ApiService from '@/services/ApiService'
import { extractApiPayload, SERIES_FALLBACK_COVER } from '@/utils/helpers'

const router      = useRouter()
const items       = ref([])
const loading     = ref(true)
const unfollowing = ref(null)

const goToStory = (item) => {
  const target = typeof item === 'object' ? (item.slug || item.id) : item
  router.push(`/story/${target}`)
}

onMounted(async () => {
  try {
    const res = extractApiPayload(await AuthService.getFollowedSeries())
    items.value = res?.items ?? []
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
})

const unfollow = async (item) => {
  if (unfollowing.value) return
  unfollowing.value = item.id
  try {
    await ApiService.post(`/series/${item.id}/follow`)
    items.value = items.value.filter(s => s.id !== item.id)
  } catch {
    // silent
  } finally {
    unfollowing.value = null
  }
}
</script>

<style scoped>
.follows-page { min-height: 100vh; }
.container { max-width: 640px; margin: 0 auto; padding: 24px 16px 48px; }

/* ── Header ── */
.page-header { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
.back-btn {
  width: 38px; height: 38px; border-radius: var(--radius-sm);
  background: var(--bg-muted); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; flex-shrink: 0; color: var(--text); transition: all 0.2s;
}
.back-btn:hover { background: var(--border); }
.page-title { font-size: 22px; font-weight: 700; margin: 0 0 3px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Item list ── */
.item-list { display: flex; flex-direction: column; gap: 12px; }

/* ── Follow card ── */
.follow-card {
  display: flex; gap: 14px; padding: 16px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  transition: border-color 0.2s, box-shadow 0.2s;
}
.follow-card:hover {
  border-color: var(--primary-light-border, rgba(168,85,247,0.3));
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* Thumbnail */
.thumb-wrap { width: 72px; height: 96px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; cursor: pointer; position: relative; }
.thumb-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.thumb-wrap:hover img { transform: scale(1.06); }
.thumb-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.5)); }

/* Info */
.item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.item-title { font-size: 14px; font-weight: 700; line-height: 1.35; cursor: pointer; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.item-title:hover { color: var(--primary); }
.item-narrator { font-size: 12px; color: var(--text-muted); }
.item-stats { display: flex; align-items: center; gap: 10px; margin-top: 2px; }
.stat { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); }
.badge-complete {
  display: inline-flex; align-items: center; padding: 2px 8px;
  border-radius: var(--radius-full); font-size: 11px; font-weight: 700;
  background: rgba(34,197,94,0.12); color: var(--success, #22c55e);
  border: 1px solid rgba(34,197,94,0.3); width: fit-content; margin-top: 2px;
}
.item-followed-at { font-size: 11px; color: var(--text-faint); }
.item-actions { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.btn-listen {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 16px; border-radius: var(--radius-full);
  background: var(--gradient-premium); border: none;
  color: #fff; font-size: 12px; font-weight: 700; cursor: pointer;
  transition: opacity 0.2s; white-space: nowrap;
}
.btn-listen:hover { opacity: 0.85; }
.btn-unfollow {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 14px; border-radius: var(--radius-full);
  background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25);
  color: var(--red, #ef4444); font-size: 12px; font-weight: 600; cursor: pointer;
  transition: all 0.2s; white-space: nowrap;
}
.btn-unfollow:hover:not(:disabled) { background: rgba(239,68,68,0.16); }
.btn-unfollow:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Empty ── */
.empty-state { text-align: center; padding: 64px 20px; }
.empty-icon {
  width: 72px; height: 72px; border-radius: 50%;
  background: var(--bg-muted); display: flex; align-items: center;
  justify-content: center; margin: 0 auto 20px; color: var(--text-faint);
}
.empty-title { font-size: 16px; font-weight: 600; margin: 0 0 6px; }
.empty-sub { font-size: 13px; color: var(--text-muted); margin: 0 0 24px; }
.btn-explore {
  display: inline-flex; align-items: center; gap: 6px; padding: 11px 24px;
  border-radius: var(--radius-full); background: var(--gradient-premium);
  color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; transition: opacity 0.2s;
}
.btn-explore:hover { opacity: 0.88; }

/* ── Skeletons ── */
.card-skeleton {
  display: flex; gap: 12px; padding: 14px;
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg);
}
.sk-thumb { width: 80px; height: 108px; border-radius: var(--radius-sm); background: var(--bg-muted); flex-shrink: 0; animation: pulse 1.5s ease-in-out infinite; }
.sk-body { flex: 1; display: flex; flex-direction: column; gap: 8px; padding-top: 4px; }
.sk-line { height: 10px; border-radius: 6px; background: var(--bg-muted); animation: pulse 1.5s ease-in-out infinite; }
.sk-actions { height: 32px; width: 60%; border-radius: var(--radius-full); background: var(--bg-muted); margin-top: 8px; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
</style>

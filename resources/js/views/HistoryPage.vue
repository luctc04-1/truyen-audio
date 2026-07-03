<template>
  <div class="history-page">
    <div class="container">

      <!-- Header -->
      <div class="page-header">
        <button class="back-btn" @click="$router.back()">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <div>
          <h1 class="page-title">Lịch sử nghe</h1>
          <p class="page-subtitle">Các tập đã nghe gần đây</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="item-list">
        <div v-for="n in 5" :key="n" class="card-skeleton">
          <div class="sk-thumb"></div>
          <div class="sk-body">
            <div class="sk-line" style="width:70%"></div>
            <div class="sk-line" style="width:45%"></div>
            <div class="sk-bar"></div>
          </div>
          <div class="sk-btn"></div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="!items.length" class="empty-state">
        <div class="empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <p class="empty-title">Chưa có lịch sử nghe</p>
        <p class="empty-sub">Hãy bắt đầu nghe một tập truyện</p>
        <router-link to="/" class="btn-explore">Khám phá truyện</router-link>
      </div>

      <!-- List -->
      <div v-else class="item-list">
        <div
          v-for="item in items"
          :key="item.id"
          class="history-card"
          @click="goToSeries(item)"
        >
          <!-- Top row: thumb + info + button -->
          <div class="card-row">

            <!-- Thumbnail -->
            <div class="thumb-wrap">
              <img v-if="item.cover_url" :src="item.cover_url" :alt="item.series_title" />
              <div v-else class="thumb-fallback">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
              </div>
              <div v-if="item.completed" class="completed-overlay">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>

            <!-- Info -->
            <div class="card-info">
              <div class="ep-title">{{ item.episode_title || `Tập ${item.episode_number}` }}</div>
              <div class="series-title">{{ item.series_title }}</div>
              <div class="meta-row">
                <span class="meta-chip">
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  {{ formatDate(item.last_listened_at) }}
                </span>
                <span class="meta-chip">
                  {{ item.completed ? 'Hoàn thành' : `${formatTime(item.listened_seconds)} / ${formatTime(item.duration_seconds)}` }}
                </span>
              </div>
            </div>

            <!-- Play button -->
            <button class="play-btn" @click.stop="goToSeries(item)">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
              <span>{{ item.completed ? 'Nghe lại' : 'Tiếp tục' }}</span>
            </button>

          </div>

          <!-- Progress bar — full width at bottom of card -->
          <div class="progress-track">
            <div class="progress-fill" :style="{ width: item.progress + '%' }"></div>
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
import { extractApiPayload, formatTime } from '@/utils/helpers'

const router  = useRouter()
const items   = ref([])
const loading = ref(true)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const goToSeries = (item) => {
  if (item.series_id) router.push(`/story/${item.series_id}`)
}

onMounted(async () => {
  try {
    const res = extractApiPayload(await AuthService.getHistory())
    items.value = res?.items ?? []
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.history-page { min-height: 100vh; }
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

/* ── History card ── */
.history-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.history-card:hover {
  border-color: var(--primary-light-border, rgba(168,85,247,0.3));
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.card-row { display: flex; align-items: center; gap: 14px; padding: 14px 14px 12px; }

/* Thumbnail */
.thumb-wrap {
  width: 62px; height: 62px; border-radius: var(--radius-sm);
  overflow: hidden; flex-shrink: 0; background: var(--bg-muted);
  position: relative;
}
.thumb-wrap img { width: 100%; height: 100%; object-fit: cover; }
.thumb-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-faint); }
.thumb-fallback svg { width: 24px; height: 24px; }
.completed-overlay {
  position: absolute; inset: 0;
  background: rgba(34,197,94,0.75);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
}

/* Info */
.card-info { flex: 1; min-width: 0; }
.ep-title {
  font-size: 13px; font-weight: 600; line-height: 1.35;
  display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 3px;
}
.series-title {
  font-size: 11px; color: var(--text-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px;
}
.meta-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.meta-chip {
  display: inline-flex; align-items: center; gap: 3px;
  padding: 2px 8px; border-radius: var(--radius-full);
  background: var(--bg-muted); font-size: 10px; color: var(--text-muted);
  white-space: nowrap;
}

/* Play button */
.play-btn {
  display: inline-flex; flex-direction: column; align-items: center; gap: 4px;
  padding: 10px 14px; border-radius: var(--radius-md);
  background: var(--primary-light); border: 1px solid var(--primary-light-border, rgba(168,85,247,0.25));
  color: var(--primary); font-size: 11px; font-weight: 700;
  cursor: pointer; flex-shrink: 0; transition: all 0.2s; white-space: nowrap;
}
.play-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.play-btn svg { width: 15px; height: 15px; }

/* Progress bar */
.progress-track { height: 3px; background: var(--border); }
.progress-fill { height: 100%; background: var(--gradient-premium); transition: width 0.4s; }

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
  display: flex; align-items: center; gap: 14px; padding: 14px;
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg);
}
.sk-thumb { width: 62px; height: 62px; border-radius: var(--radius-sm); background: var(--bg-muted); flex-shrink: 0; animation: pulse 1.5s ease-in-out infinite; }
.sk-body { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.sk-line { height: 10px; border-radius: 6px; background: var(--bg-muted); animation: pulse 1.5s ease-in-out infinite; }
.sk-bar { height: 3px; border-radius: 2px; background: var(--bg-muted); animation: pulse 1.5s ease-in-out infinite; }
.sk-btn { width: 60px; height: 44px; border-radius: var(--radius-md); background: var(--bg-muted); flex-shrink: 0; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
</style>

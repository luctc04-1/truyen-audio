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
          <router-link to="/library" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);margin-bottom:20px;transition:color 0.2s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--text-muted)'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Danh sách truyện
          </router-link>

          <div v-if="story" class="detail-hero-inner animate-in">
            <div class="detail-cover">
              <img :src="story.image" :alt="story.title" />
            </div>
            <div class="detail-info">
              <div class="detail-badges">
                <span class="badge">💕 {{ story.genre || 'Ngôn Tình' }}</span>
                <span class="badge">🎬 Audio Dài</span>
                <span class="badge badge-success" v-if="story.status === 'completed'">Hoàn thành</span>
                <span class="badge" v-else>Đang cập nhật</span>
              </div>
              <h1 class="detail-title">{{ story.title }}</h1>
              <p class="detail-producer">Nhà Sản Xuất: <span>{{ story.author }}</span></p>
              <div class="detail-stats">
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                  {{ story.plays }} lượt nghe
                </span>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  {{ story.episodeCount }} tập
                </span>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                  {{ story.rating }}
                </span>
              </div>
              <div class="detail-actions">
                <router-link :to="`/episode/${story.id}/1`">
                  <button class="btn btn-lg btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                    Nghe thử
                  </button>
                </router-link>
                <button class="btn btn-lg btn-outline" @click="toggleFollow" :style="{ borderColor: isFollowed ? 'var(--red)' : '', color: isFollowed ? 'var(--red)' : '' }">
                  <svg v-if="isFollowed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="var(--red)" stroke="var(--red)" stroke-width="1"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                  {{ isFollowed ? 'Đang theo dõi' : 'Theo dõi' }}
                </button>
                <button class="btn btn-lg btn-outline">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                  <span style="display:none">Chia sẻ</span>
                </button>
              </div>
              <p class="detail-note">Theo dõi bộ truyện để nhận thông báo khi có tập mới ra mắt</p>
            </div>
          </div>
        </div>
      </section>

      <!-- TABS -->
    <section v-if="story" style="padding-top:0;">
        <div class="container">
          <!-- Tab bar -->
          <div class="tabs-bar" id="tabs-bar">
            <button :class="['tab-btn', { active: activeTab === 'episodes' }]" @click="activeTab = 'episodes'">
              Danh sách tập ({{ story.episodeCount }})
            </button>
            <button :class="['tab-btn', { active: activeTab === 'reviews' }]" @click="activeTab = 'reviews'">
              Đánh giá (2)
            </button>
          </div>

          <!-- Episodes tab -->
          <div :class="['tab-content', { active: activeTab === 'episodes' }]">
            <div style="display:flex;flex-direction:column;gap:4px;">
              <!-- Free episodes -->
              <div v-for="(episode, index) in story.episodes" :key="episode.id" :class="['episode-item', { playing: index === 0 }]" @click="$router.push(`/episode/${story.id}/${episode.id}`)">
                <div class="episode-thumb">
                  <img :src="story.image" :alt="episode.title" />
                  <div class="episode-thumb-overlay">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                  </div>
                </div>
                <div class="episode-info">
                  <div class="episode-title">{{ episode.title }}</div>
                  <div class="episode-meta">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ episode.duration || '1:06:01' }}
                  </div>
                </div>
                <button class="icon-btn episode-play">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                </button>
              </div>

              <!-- Extra free episodes (placeholder) -->
              <div v-for="ep in extraFreeEps" :key="'f'+ep.id" class="episode-item" @click="$router.push(`/episode/${story.id}/${ep.id}`)">
                <div class="episode-thumb">
                  <img :src="story.image" :alt="ep.title" />
                  <div class="episode-thumb-overlay">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                  </div>
                </div>
                <div class="episode-info">
                  <div class="episode-title">{{ ep.title }}</div>
                  <div class="episode-meta">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ ep.duration }}
                  </div>
                </div>
                <button class="icon-btn episode-play">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                </button>
              </div>

              <!-- VIP locked episodes -->
              <div v-for="ep in vipEps" :key="'v'+ep.id" class="episode-item" style="opacity:0.65;cursor:default;">
                <div class="episode-thumb" style="opacity:0.6;">
                  <img :src="story.image" :alt="ep.title" />
                </div>
                <div class="episode-info">
                  <div class="episode-title">{{ ep.title }}</div>
                  <div class="episode-meta">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ ep.duration }}
                  </div>
                </div>
                <div class="ep-locked">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                  VIP
                </div>
              </div>

              <!-- VIP banner -->
              <div style="background:linear-gradient(135deg,rgba(168,85,247,0.1),rgba(236,72,153,0.08));border:1px solid rgba(168,85,247,0.25);border-radius:var(--radius-md);padding:16px;text-align:center;margin-top:8px;">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">🔒 Tập 7 đến {{ story.episodeCount || 43 }} chỉ dành cho thành viên VIP</p>
                <router-link to="/vip">
                  <button class="btn btn-primary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                    Đăng ký VIP từ 37.000đ/tháng
                  </button>
                </router-link>
              </div>
            </div>
          </div>

          <!-- Reviews tab -->
          <div :class="['tab-content', { active: activeTab === 'reviews' }]">
            <div style="display:flex;flex-direction:column;gap:16px;">
              <div class="card">
                <div style="padding:16px;">
                  <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;flex-shrink:0;">N</div>
                    <div>
                      <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-weight:600;font-size:14px;">Nguyễn Văn A</span>
                        <div class="stars">
                          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" v-for="i in 5" :key="'sa'+i" fill="#f59e0b"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679 5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428-4.618 2.428a.53.53 0 0 1-.77-.56l.881-5.139-3.736-3.638a.53.53 0 0 1 .294-.906l5.165-.755z"/></svg>
                        </div>
                      </div>
                      <div style="font-size:12px;color:var(--text-muted);">2 ngày trước</div>
                    </div>
                  </div>
                  <p style="font-size:14px;line-height:1.6;color:var(--text);">Bộ truyện hay lắm, giọng đọc của Sói Review 2510 rất cuốn hút. Nghe một lần là nghiện ngay! Mong admin ra thêm nhiều tập nữa.</p>
                </div>
              </div>
              <div class="card">
                <div style="padding:16px;">
                  <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;flex-shrink:0;">T</div>
                    <div>
                      <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-weight:600;font-size:14px;">Trần Thị B</span>
                        <div class="stars">
                          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" v-for="i in 4" :key="'sb'+i" fill="#f59e0b"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679 5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428-4.618 2.428a.53.53 0 0 1-.77-.56l.881-5.139-3.736-3.638a.53.53 0 0 1 .294-.906l5.165-.755z"/></svg>
                          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#f59e0b" stroke-width="1.5"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679 5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428-4.618 2.428a.53.53 0 0 1-.77-.56l.881-5.139-3.736-3.638a.53.53 0 0 1 .294-.906l5.165-.755z"/></svg>
                        </div>
                      </div>
                      <div style="font-size:12px;color:var(--text-muted);">5 ngày trước</div>
                    </div>
                  </div>
                  <p style="font-size:14px;line-height:1.6;color:var(--text);">Truyện hay, nội dung cuốn hút. Tuy nhiên một số tập hơi ngắn. Nhìn chung vẫn rất đáng nghe!</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStoryStore } from '../js/stores/storyStore'

const route = useRoute()
const router = useRouter()
const storyStore = useStoryStore()
const activeTab = ref('episodes')
const isFollowed = ref(false)

const story = computed(() => storyStore.getStoryById(route.params.id))
const toggleFollow = () => { isFollowed.value = !isFollowed.value }

const extraFreeEps = [
  { id: 3, title: 'Tập 3: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:19:55' },
  { id: 4, title: 'Tập 4: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:20:02' },
  { id: 5, title: 'Tập 5: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:09:53' },
  { id: 6, title: 'Tập 6: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:07:31' },
]
const vipEps = [
  { id: 7, title: 'Tập 7: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:15:22' },
  { id: 8, title: 'Tập 8: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:11:08' },
]
</script>

<style scoped>
/* No scoped CSS needed - it all relies on global style.css now! */
</style>

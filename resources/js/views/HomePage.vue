<template>
  <div class="home-page">
    <!-- HERO -->
    <section class="hero">
      <div class="hero-bg">
        <img :src="heroCover" alt="Hero" />
        <div class="hero-overlay"></div>
        <div class="hero-overlay2"></div>
      </div>
      <div class="container">
        <div class="hero-content animate-in">
          <div class="hero-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
            </svg>
            Nội dung mới mỗi tuần
          </div>
          <h1 class="hero-title">
            <span class="text-gradient">Truyện Audio</span>
          </h1>
          <p class="hero-desc">Kho truyện audio ngôn tình, audio dài, trinh thám, giang hồ và học đường chất lượng cao.</p>
          <div class="hero-actions">
            <router-link to="/library">
              <button class="btn btn-lg btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                Khám phá ngay
              </button>
            </router-link>
            <router-link to="/vip">
              <button class="btn btn-lg btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                Đăng ký VIP
              </button>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- GENRES -->
    <section class="genre-section">
      <div class="container">
        <div class="genre-scroll">
          <button
            v-for="genre in storyStore.homeGenres"
            :key="genre.id"
            type="button"
            class="genre-pill"
            @click="goLibrary(genre.id)"
          >
            {{ genre.label }}
          </button>
        </div>
      </div>
    </section>

    <!-- API error -->
    <section v-if="storyStore.error && !storyStore.homeLoading" class="container">
      <div class="api-error">
        <p>{{ storyStore.error }}</p>
        <button type="button" class="btn btn-sm btn-outline" @click="storyStore.loadHome({ force: true })">Thử lại</button>
      </div>
    </section>

    <!-- TRENDING -->
    <section class="story-section">
      <div class="container">
        <div class="section-heading">
          <h2>🔥 Xu hướng</h2>
          <router-link to="/library" class="see-all">
            Xem tất cả
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </router-link>
        </div>
        <div class="stories-grid trending-grid">
          <template v-if="storyStore.homeLoading">
            <StoryCardSkeleton v-for="n in 10" :key="'sk-trend-' + n" />
          </template>
          <template v-else>
            <StoryCard v-for="story in storyStore.trendingStories" :key="story.id" :story="story" />
          </template>
        </div>
      </div>
    </section>

    <!-- NEW RELEASES -->
    <section class="story-section">
      <div class="container">
        <div class="section-heading">
          <h2>✨ Mới cập nhật</h2>
          <router-link to="/library" class="see-all">
            Xem tất cả
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </router-link>
        </div>
        <div class="update-list">
          <template v-if="storyStore.homeLoading">
            <div v-for="n in 5" :key="'sk-update-' + n" class="update-skeleton">
              <div class="sk" style="width:72px;height:72px;border-radius:10px;flex-shrink:0;"></div>
              <div style="flex:1;display:flex;flex-direction:column;gap:8px;justify-content:center;">
                <div class="sk sk-line" style="width:60%;"></div>
                <div class="sk sk-line" style="width:80%;"></div>
                <div class="sk sk-line" style="width:45%;"></div>
              </div>
            </div>
          </template>
          <template v-else-if="storyStore.recentEpisodes.length">
            <LatestEpisodeRow
              v-for="ep in storyStore.recentEpisodes"
              :key="ep.id"
              :item="ep"
            />
          </template>
          <p v-else class="empty-hint">Chưa có tập mới.</p>
        </div>
      </div>
    </section>

    <!-- VIP PROMO -->
    <section class="vip-promo-section">
      <div class="container">
        <div class="vip-promo-banner">
          <div class="vip-promo-glow"></div>
          <div class="vip-promo-content">
            <div class="vip-promo-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/>
                <path d="M5 21h14"/>
              </svg>
            </div>
            <h3 class="vip-promo-title">Nâng cấp VIP để mở khóa toàn bộ nội dung</h3>
            <p class="vip-promo-desc">Truy cập không giới hạn vào tất cả các tập premium, không quảng cáo, và nhiều đặc quyền hấp dẫn khác.</p>
            <router-link to="/vip" class="btn btn-lg btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>
              </svg>
              Xem các gói VIP
            </router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default { name: 'HomePage' }
</script>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useStoryStore } from '@/stores/storyStore'
import StoryCard from '@/components/StoryCard.vue'
import StoryCardSkeleton from '@/components/StoryCardSkeleton.vue'
import LatestEpisodeRow from '@/components/LatestEpisodeRow.vue'

const router = useRouter()
const storyStore = useStoryStore()

const stories = computed(() => storyStore.stories)

const heroCover = computed(
  () => storyStore.trendingStories[0]?.image || 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1600&q=80'
)

onMounted(() => {
  storyStore.loadHome()
})

const goLibrary = (genreId) => {
  storyStore.setGenre(genreId)
  router.push({ name: 'Library', query: { category: genreId } })
}
</script>

<style scoped>
.home-page { min-height: 100vh; }

/* ---- HERO ---- */
.hero { position: relative; padding: 64px 0 48px; overflow: hidden; }
.hero-bg { position: absolute; inset: 0; }
.hero-bg img { width: 100%; height: 100%; object-fit: cover; }
.hero-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(9, 9, 11, 0.96) 0%, rgba(9, 9, 11, 0.7) 50%); }
.hero-overlay2 { position: absolute; inset: 0; background: linear-gradient(to top, rgba(9, 9, 11, 1) 0%, transparent 100%); }

.container { max-width: var(--container); margin: 0 auto; padding: 0 16px; position: relative; }
@media (min-width: 640px) { .container { padding: 0 24px; } }
@media (min-width: 1024px) { .container { padding: 0 32px; } }

.hero-content { animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: var(--gradient-premium);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  margin-bottom: 16px;
  border: none;
}
.hero-title { font-size: clamp(32px, 5vw, 52px); font-weight: 800; line-height: 1.15; margin-bottom: 16px; }
@media (min-width: 768px) { .hero-title { font-size: 56px; } }
.text-gradient { background: var(--gradient-premium); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero-desc { font-size: 16px; color: var(--text-muted); margin-bottom: 28px; max-width: 460px; }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 20px; height: 40px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; transition: all 0.2s; white-space: nowrap; cursor: pointer; text-decoration: none; font-family: inherit; }
.btn-lg { height: 44px; padding: 0 28px; font-size: 15px; }
.btn-sm { height: 34px; padding: 0 14px; font-size: 13px; }
.btn-primary { background: var(--gradient-premium); color: #fff; }
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: default; transform: none; }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); }
.btn-outline:hover { background: var(--bg-muted); border-color: var(--primary); }
.spin { width: 18px; height: 18px; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ---- GENRE ---- */
.genre-section { padding: 8px 0 24px; }
.genre-scroll { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
.genre-pill { padding: 8px 16px; border-radius: var(--radius-full); border: 1px solid var(--border); background: transparent; color: var(--text-muted); font-size: 13px; font-weight: 500; white-space: nowrap; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
.genre-pill:hover { border-color: var(--primary); color: var(--primary); }
.genre-pill.active { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }

/* ---- API error ---- */
.api-error { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid rgba(239, 68, 68, 0.35); background: rgba(239, 68, 68, 0.08); color: #fca5a5; font-size: 14px; }

/* ---- SECTIONS ---- */
.story-section { padding: 0 0 36px; }
.section-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.section-heading h2 { font-size: 18px; font-weight: 700; }
.see-all { display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted); transition: color 0.2s; }
.see-all:hover { color: var(--primary); }
.see-all svg { width: 16px; height: 16px; }

.stories-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media (min-width: 640px) { .stories-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .stories-grid { grid-template-columns: repeat(6, 1fr); } }

.trending-grid { grid-template-columns: repeat(2, 1fr); }
@media (min-width: 640px) { .trending-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 768px) { .trending-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .trending-grid { grid-template-columns: repeat(5, 1fr); } }

.update-list { display: flex; flex-direction: column; gap: 10px; }

.update-skeleton {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border);
}

.sk {
  background: linear-gradient(90deg, var(--bg-muted) 25%, rgba(255,255,255,0.06) 50%, var(--bg-muted) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.sk-line { height: 12px; border-radius: 4px; }

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.empty-hint {
  padding: 24px;
  text-align: center;
  color: var(--text-muted);
  font-size: 14px;
}

/* ---- VIP PROMO ---- */
.vip-promo-section {
  padding: 0 0 48px;
}

.vip-promo-banner {
  position: relative;
  overflow: hidden;
  border-radius: var(--radius-lg);
  padding: 28px 24px;
  background: linear-gradient(135deg, var(--primary-light), rgba(236, 72, 153, 0.08));
  border: 1px solid rgba(168, 85, 247, 0.28);
}

@media (min-width: 640px) {
  .vip-promo-banner { padding: 32px 36px; }
}

.vip-promo-glow {
  position: absolute;
  top: -40%;
  right: -10%;
  width: 280px;
  height: 280px;
  background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 70%);
  pointer-events: none;
}

.vip-promo-content {
  position: relative;
  z-index: 1;
  max-width: 560px;
}

.vip-promo-icon {
  margin-bottom: 12px;
  color: var(--primary);
}

.vip-promo-icon svg {
  width: 28px;
  height: 28px;
}

.vip-promo-title {
  font-size: clamp(18px, 3vw, 24px);
  font-weight: 800;
  color: var(--text);
  line-height: 1.3;
  margin-bottom: 10px;
}

.vip-promo-desc {
  font-size: 14px;
  line-height: 1.55;
  color: var(--text-muted);
  margin-bottom: 20px;
  max-width: 480px;
}
</style>

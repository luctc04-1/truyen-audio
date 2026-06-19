<template>
  <div class="home-page">
    <!-- HERO -->
    <section class="hero">
      <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1600&q=80" alt="Hero" />
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
            <span class="text-gradient">truyenaudio25.com</span>
          </h1>
          <p class="hero-desc">Kho truyện audio ngôn tình, audio dài, trinh thám, giang hồ và học đường chất lượng cao.</p>
          <div class="hero-actions">
            <router-link to="/library">
              <button class="btn btn-lg btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                  <polygon points="6 3 20 12 6 21 6 3"/>
                </svg>
                Khám phá ngay
              </button>
            </router-link>
            <router-link to="/vip">
              <button class="btn btn-lg btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/>
                  <path d="M5 21h14"/>
                </svg>
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
          <button v-for="genre in storyStore.allGenres" :key="genre.id" :class="['genre-pill', { active: storyStore.selectedGenre === genre.id }]" @click="storyStore.setGenre(genre.id)">
            {{ genre.label }}
          </button>
        </div>
      </div>
    </section>

    <!-- TRENDING -->
    <section class="trending-section">
      <div class="container">
        <div class="section-heading">
          <h2>🔥 Xu hướng</h2>
          <router-link to="/library" style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text);">
            Xem tất cả
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
          </router-link>
        </div>

        <!-- Mobile scroll -->
        <div class="story-scroll show-sm">
          <StoryCard v-for="story in storyStore.filteredStories.slice(0, 5)" :key="story.id" :story="story" />
        </div>

        <!-- Desktop grid -->
        <div class="stories-grid hide-sm">
          <StoryCard v-for="story in storyStore.filteredStories.slice(0, 5)" :key="story.id" :story="story" />
        </div>
      </div>
    </section>

    <!-- NEW RELEASES -->
    <section class="story-scroll-section">
      <div class="container">
        <div class="section-heading">
          <h2>✨ Mới cập nhật</h2>
          <router-link to="/library" style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text);">
            Xem tất cả
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
          </router-link>
        </div>
        <div class="story-scroll">
          <router-link to="/story/1" class="story-card">
            <div class="story-card-thumb">
              <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=300&q=80" alt="">
              <div class="story-card-overlay"></div>
              <div class="story-card-status"><span class="badge badge-amber" style="font-size:10px;padding:2px 8px;">Mới nhất</span></div>
            </div>
            <div class="story-card-title">Tôi Và Cô Ấy Cùng Yêu Một Người</div>
            <div class="story-card-author">Sói Review 2510</div>
            <div class="story-card-meta">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
              8.2K <span>•</span> 5 tập
            </div>
          </router-link>
          <router-link to="/story/2" class="story-card">
            <div class="story-card-thumb">
              <img src="https://images.unsplash.com/photo-1531266752426-aad472b7bbf4?w=300&q=80" alt="">
              <div class="story-card-overlay"></div>
              <div class="story-card-status"><span class="badge" style="font-size:10px;padding:2px 8px;">Đang cập nhật</span></div>
            </div>
            <div class="story-card-title">Hôn Nhân Không Tình Yêu</div>
            <div class="story-card-author">Sói Review 2510</div>
            <div class="story-card-meta">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
              12.5K <span>•</span> 8 tập
            </div>
          </router-link>
          <router-link to="/story/1" class="story-card">
            <div class="story-card-thumb">
              <img src="https://images.unsplash.com/photo-1530023367847-a683933f4172?w=300&q=80" alt="">
              <div class="story-card-overlay"></div>
              <div class="story-card-status"><span class="badge badge-success" style="font-size:10px;padding:2px 8px;">Hoàn thành</span></div>
            </div>
            <div class="story-card-title">Anh Hùng Cứu Mỹ Nhân</div>
            <div class="story-card-author">Sói Review 2510</div>
            <div class="story-card-meta">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
              22.1K <span>•</span> 15 tập
            </div>
          </router-link>
          <router-link to="/story/2" class="story-card">
            <div class="story-card-thumb">
              <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=300&q=80" alt="">
              <div class="story-card-overlay"></div>
              <div class="story-card-status"><span class="badge" style="font-size:10px;padding:2px 8px;">Đang cập nhật</span></div>
            </div>
            <div class="story-card-title">Đêm Cuối Cùng Bên Em</div>
            <div class="story-card-author">Sói Review 2510</div>
            <div class="story-card-meta">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
              9.7K <span>•</span> 3 tập
            </div>
          </router-link>
          <router-link to="/story/1" class="story-card">
            <div class="story-card-thumb">
              <img src="https://images.unsplash.com/photo-1455390582262-044cdead277a?w=300&q=80" alt="">
              <div class="story-card-overlay"></div>
              <div class="story-card-status"><span class="badge badge-success" style="font-size:10px;padding:2px 8px;">Hoàn thành</span></div>
            </div>
            <div class="story-card-title">Kẻ Phản Bội Và Người Tôi Yêu</div>
            <div class="story-card-author">Sói Review 2510</div>
            <div class="story-card-meta">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
              18.3K <span>•</span> 18 tập
            </div>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useStoryStore } from '../js/stores/storyStore'
import StoryCard from '../js/components/StoryCard.vue'

const storyStore = useStoryStore()
</script>

<style scoped>
.home-page {
  min-height: 100vh;
}

/* ---- HERO ---- */
.hero {
  position: relative;
  padding: 64px 0 48px;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
}

.hero-bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(9, 9, 11, 0.95) 0%, rgba(9, 9, 11, 0.7) 50%);
}

.hero-overlay2 {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(9, 9, 11, 1) 0%, transparent 100%);
}

.container {
  max-width: var(--container);
  margin: 0 auto;
  padding: 0 16px;
  position: relative;
}

@media (min-width: 640px) {
  .container { padding: 0 24px; }
}
@media (min-width: 1024px) {
  .container { padding: 0 32px; }
}

.hero-content {
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(168, 85, 247, 0.12);
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: var(--radius-full);
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 16px;
}

.hero-title {
  font-size: 40px;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 12px;
}

@media (min-width: 768px) {
  .hero-title { font-size: 56px; }
}

.text-gradient {
  background: var(--gradient-premium);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-desc {
  font-size: 16px;
  color: var(--text-muted);
  margin-bottom: 24px;
  max-width: 500px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
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
  text-decoration: none;
}
.btn-lg { height: 44px; padding: 0 28px; font-size: 15px; }
.btn-primary { background: var(--gradient-premium); color: #fff; }
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); }
.btn-outline:hover { background: var(--bg-muted); border-color: var(--primary); }

/* ---- GENRE ---- */
.genre-section { padding: 8px 0 24px; }

.genre-scroll {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 8px;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.genre-pill {
  padding: 8px 16px;
  border-radius: var(--radius-full);
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.genre-pill:hover { border-color: var(--primary); color: var(--primary); }
.genre-pill.active { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }

/* ---- TRENDING ---- */
.trending-section { padding: 0 0 40px; }

.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.section-heading h2 { font-size: 18px; font-weight: 700; }

/* Mobile scroll */
.story-scroll {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 8px;
  -webkit-overflow-scrolling: touch;
}
.story-scroll .story-card {
  width: 140px;
  flex-shrink: 0;
}

/* Desktop grid */
.stories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
}
@media (min-width: 768px) {
  .stories-grid { grid-template-columns: repeat(5, 1fr); }
}

/* show/hide for responsive */
.show-sm { display: flex; }
.hide-sm { display: none; }
@media (min-width: 768px) {
  .show-sm { display: none; }
  .hide-sm { display: grid; }
}

/* ---- NEW RELEASES ---- */
.story-scroll-section { padding: 0 0 40px; }

/* Story card styles (for inline cards in new-releases) */
.story-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-decoration: none;
  transition: transform 0.2s;
  width: 140px;
  flex-shrink: 0;
}
.story-card:hover { transform: translateY(-4px); }
.story-card-thumb {
  position: relative;
  aspect-ratio: 3 / 4;
  border-radius: var(--radius-md);
  overflow: hidden;
}
.story-card-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}
.story-card:hover .story-card-thumb img { transform: scale(1.05); }
.story-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.8));
}
.story-card-status { position: absolute; top: 8px; left: 8px; }
.story-card-title {
  font-size: 14px;
  font-weight: 600;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.story-card:hover .story-card-title { color: var(--primary); }
.story-card-author { font-size: 12px; color: var(--text-muted); }
.story-card-meta {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-muted);
}
.story-card-meta svg { width: 14px; height: 14px; }

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
.badge-success {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  border-color: var(--success-border);
}
.badge-amber {
  background: rgba(245, 158, 11, 0.1);
  color: var(--amber);
  border-color: rgba(245, 158, 11, 0.3);
}
</style>

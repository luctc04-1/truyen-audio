<template>
  <div class="library-page">
    <div class="container">

      <!-- Page header -->
      <div class="page-header">
        <h1 class="page-title">📚 Kho Truyện</h1>
        <p class="page-subtitle">Hàng nghìn tập truyện audio chất lượng cao, cập nhật liên tục</p>
      </div>

      <!-- Search bar -->
      <div class="search-bar" style="margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm truyện, tác giả…" autocomplete="off" />
        <button v-if="searchQuery" class="icon-btn" style="color:var(--text-muted);" @click="searchQuery = ''">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <!-- Genre scroll (mobile) -->
      <div class="genre-scroll" style="margin-bottom:16px;">
        <div v-for="genre in storyStore.allGenres" :key="genre.id" :class="['genre-pill', { active: storyStore.selectedGenre === genre.id }]" @click="storyStore.setGenre(genre.id)">
          {{ genre.label }}
        </div>
      </div>

      <div class="kho-layout">

        <!-- SIDEBAR (Desktop only) -->
        <div class="genre-sidebar">
          <div class="sidebar-label">Thể loại</div>
          <div v-for="genre in storyStore.allGenres" :key="genre.id" :class="['genre-sidebar-item', { active: storyStore.selectedGenre === genre.id }]" @click="storyStore.setGenre(genre.id)">
            <span>{{ genre.label }}</span>
            <span class="genre-sidebar-count">({{ getGenreCount(genre.id) }})</span>
          </div>

          <div class="sidebar-divider"></div>
          <div class="sidebar-label">Trạng thái</div>
          <div :class="['genre-sidebar-item', { active: statusFilter === 'completed' }]" @click="statusFilter = statusFilter === 'completed' ? '' : 'completed'">✅ Hoàn thành</div>
          <div :class="['genre-sidebar-item', { active: statusFilter === 'updating' }]" @click="statusFilter = statusFilter === 'updating' ? '' : 'updating'">🔄 Đang cập nhật</div>

          <div class="sidebar-divider"></div>
          <div class="sidebar-label">Truy cập</div>
          <div class="genre-sidebar-item">🆓 Miễn phí</div>
          <div class="genre-sidebar-item">
            <span style="display:flex;align-items:center;gap:6px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
              VIP
            </span>
          </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="kho-main">

          <!-- Filter row -->
          <div class="filter-row">
            <div class="result-count"><strong>{{ filteredStories.length }}</strong> truyện</div>
            <div style="flex:1;"></div>
            <select v-model="sortBy" class="sort-select">
              <option value="trending">🔥 Xu hướng</option>
              <option value="newest">✨ Mới nhất</option>
              <option value="popular">🎧 Nghe nhiều nhất</option>
              <option value="rating">⭐ Đánh giá cao</option>
              <option value="az">🔤 A → Z</option>
            </select>
            <div class="view-toggle">
              <button :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Dạng lưới">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              </button>
              <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Dạng danh sách">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
              </button>
            </div>
          </div>

          <!-- GRID VIEW -->
          <div v-if="viewMode === 'grid'" class="stories-grid">
            <StoryCard v-for="story in pagedStories" :key="story.id" :story="story" />
            <!-- Placeholder cards if not enough data -->
            <template v-if="pagedStories.length < 10">
              <router-link v-for="item in placeholderCards" :key="'p' + item.id" to="/story/1" class="story-card">
                <div class="story-card-thumb">
                  <img :src="item.image" :alt="item.title">
                  <div class="story-card-overlay"></div>
                  <div class="story-card-status"><span :class="['badge', item.status === 'completed' ? 'badge-success' : '']">{{ item.status === 'completed' ? 'Hoàn thành' : 'Đang cập nhật' }}</span></div>
                </div>
                <div class="story-card-title">{{ item.title }}</div>
                <div class="story-card-author">{{ item.author }}</div>
                <div class="story-card-meta">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                  {{ item.plays }} <span>•</span> {{ item.eps }} tập
                </div>
              </router-link>
            </template>
          </div>

          <!-- LIST VIEW -->
          <div v-else class="card" style="overflow:hidden;">
            <div v-for="story in pagedStories" :key="story.id" class="story-list-item" @click="$router.push(`/story/${story.id}`)">
              <div class="story-list-thumb"><img :src="story.image" :alt="story.title"></div>
              <div class="story-list-info">
                <div>
                  <div class="story-list-title">{{ story.title }}</div>
                  <div class="story-list-author">{{ story.author }}</div>
                  <div class="story-list-tags">
                    <span class="badge" style="font-size:10px;padding:1px 7px;">💕 Ngôn Tình</span>
                    <span :class="['badge', story.status === 'completed' ? 'badge-success' : '']" style="font-size:10px;padding:1px 7px;">{{ story.status === 'completed' ? 'Hoàn thành' : 'Đang cập nhật' }}</span>
                  </div>
                </div>
                <div class="story-list-stat">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                    {{ story.plays }} nghe
                  </span>
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ story.episodeCount }} tập
                  </span>
                  <span style="color:var(--amber);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679 5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428-4.618 2.428a.53.53 0 0 1-.77-.56l.881-5.139-3.736-3.638a.53.53 0 0 1 .294-.906l5.165-.755z"/></svg>
                    {{ story.rating }}
                  </span>
                </div>
              </div>
            </div>
            <!-- Extra list placeholders -->
            <template v-if="pagedStories.length < 5">
              <div v-for="item in placeholderCards.slice(0,5)" :key="'lp' + item.id" class="story-list-item" @click="$router.push('/story/1')">
                <div class="story-list-thumb"><img :src="item.image" :alt="item.title"></div>
                <div class="story-list-info">
                  <div>
                    <div class="story-list-title">{{ item.title }}</div>
                    <div class="story-list-author">{{ item.author }}</div>
                    <div class="story-list-tags">
                      <span class="badge" style="font-size:10px;padding:1px 7px;">💕 Ngôn Tình</span>
                      <span :class="['badge', item.status === 'completed' ? 'badge-success' : '']" style="font-size:10px;padding:1px 7px;">{{ item.status === 'completed' ? 'Hoàn thành' : 'Đang cập nhật' }}</span>
                    </div>
                  </div>
                  <div class="story-list-stat">
                    <span>{{ item.plays }} nghe</span>
                    <span>{{ item.eps }} tập</span>
                    <span style="color:var(--amber);">⭐ 4.9</span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- No results -->
          <div v-if="filteredStories.length === 0" class="no-results">
            <p>Không tìm thấy truyện phù hợp</p>
          </div>

          <!-- Pagination -->
          <div class="pagination">
            <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button v-for="p in visiblePages" :key="p" :class="['page-btn', { active: p === currentPage }]" @click="currentPage = p">{{ p }}</button>
            <span v-if="totalPages > 5" style="color:var(--text-muted);padding:0 4px;">…</span>
            <button v-if="totalPages > 5" :class="['page-btn', { active: currentPage === totalPages }]" @click="currentPage = totalPages">{{ totalPages }}</button>
            <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
          </div>

        </div><!-- /kho-main -->
      </div><!-- /kho-layout -->
    </div><!-- /container -->
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useStoryStore } from '../js/stores/storyStore'
import StoryCard from '../js/components/StoryCard.vue'

const storyStore = useStoryStore()
const searchQuery = ref('')
const sortBy = ref('trending')
const currentPage = ref(1)
const viewMode = ref('grid')
const statusFilter = ref('')
const itemsPerPage = 12

const placeholderCards = [
  { id: 'a', title: 'Chọn Vợ Giữa Chốn Hào Môn', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300&q=80', status: 'completed', plays: '47.8K', eps: 12 },
  { id: 'b', title: 'Năm Nhất Đại Học Tôi Đã Có Bé Gái Và Vợ Tổng Tài', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1495364141860-b0d03eccd065?w=300&q=80', status: 'completed', plays: '44.3K', eps: 23 },
  { id: 'c', title: 'Thanh Mai Của Tôi Là Tiểu Thư Bắc Thành', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=300&q=80', status: 'completed', plays: '31.6K', eps: 23 },
  { id: 'd', title: 'Tôi Và Cô Ấy Cùng Yêu Một Người', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=300&q=80', status: 'updating', plays: '28.9K', eps: 18 },
  { id: 'e', title: 'Hôn Nhân Không Tình Yêu', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1531266752426-aad472b7bbf4?w=300&q=80', status: 'updating', plays: '23.7K', eps: 8 },
  { id: 'f', title: 'Anh Hùng Cứu Mỹ Nhân', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1530023367847-a683933f4172?w=300&q=80', status: 'completed', plays: '22.1K', eps: 15 },
  { id: 'g', title: 'Kẻ Phản Bội Và Người Tôi Yêu', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=300&q=80', status: 'completed', plays: '18.3K', eps: 18 },
  { id: 'h', title: 'Đêm Cuối Cùng Bên Em', author: 'Sói Review 2510', image: 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=300&q=80', status: 'updating', plays: '9.7K', eps: 3 },
]

const filteredStories = computed(() => {
  let stories = storyStore.filteredStories
  if (searchQuery.value) {
    stories = stories.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || s.author.toLowerCase().includes(searchQuery.value.toLowerCase()))
  }
  if (statusFilter.value) {
    stories = stories.filter(s => s.status === statusFilter.value)
  }
  switch (sortBy.value) {
    case 'newest': stories = [...stories].reverse(); break
    case 'popular': stories = [...stories].sort((a, b) => parseInt(b.plays) - parseInt(a.plays)); break
    case 'rating': stories = [...stories].sort((a, b) => b.rating - a.rating); break
    case 'az': stories = [...stories].sort((a, b) => a.title.localeCompare(b.title)); break
  }
  return stories
})

const totalPages = computed(() => Math.max(1, Math.ceil((filteredStories.value.length + placeholderCards.length) / itemsPerPage)))
const visiblePages = computed(() => {
  const pages = []
  for (let i = 1; i <= Math.min(5, totalPages.value); i++) pages.push(i)
  return pages
})
const pagedStories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredStories.value.slice(start, start + itemsPerPage)
})

const getGenreCount = (genreId) => {
  if (genreId === 'all') return storyStore.stories.length
  return storyStore.stories.filter(s => s.genre === genreId).length
}
</script>

<style scoped>
.library-page { min-height: 100vh; }

.container {
  max-width: var(--container);
  margin: 0 auto;
  padding: 32px 16px;
}
@media (min-width: 640px) { .container { padding: 32px 24px; } }
@media (min-width: 1024px) { .container { padding: 32px 32px; } }

.page-header { margin-bottom: 20px; }
.page-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
.page-subtitle { font-size: 14px; color: var(--text-muted); }

/* Search */
.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 0 14px;
  height: 44px;
  transition: border-color 0.2s;
}
.search-bar:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(168,85,247,0.12); }
.search-bar svg { width: 17px; height: 17px; color: var(--text-faint); flex-shrink: 0; }
.search-bar input { flex: 1; background: transparent; border: none; color: var(--text); font-size: 14px; }
.search-bar input::placeholder { color: var(--text-faint); }

.icon-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); transition: all 0.2s; cursor: pointer; flex-shrink: 0; }
.icon-btn svg { width: 16px; height: 16px; }
.icon-btn:hover { background: var(--bg-muted); color: var(--text); }

/* Genre scroll */
.genre-scroll { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
.genre-pill { padding: 8px 16px; border-radius: var(--radius-full); border: 1px solid var(--border); background: transparent; color: var(--text-muted); font-size: 13px; font-weight: 500; white-space: nowrap; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
.genre-pill:hover { border-color: var(--primary); color: var(--primary); }
.genre-pill.active { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }

/* Layout */
.kho-layout { display: grid; gap: 32px; }
@media (min-width: 1024px) { .kho-layout { display: flex; gap: 32px; align-items: flex-start; } }

/* Sidebar */
.genre-sidebar { display: none; }
@media (min-width: 1024px) { .genre-sidebar { display: block; width: 220px; flex-shrink: 0; } }

.sidebar-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-faint); margin-bottom: 10px; padding-left: 4px; }
.sidebar-divider { height: 1px; background: var(--border); margin: 16px 0; }

.genre-sidebar-item { display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
.genre-sidebar-item:hover { background: var(--bg-muted); color: var(--text); }
.genre-sidebar-item.active { background: var(--primary-light); color: var(--primary); }
.genre-sidebar-count { font-size: 12px; opacity: 0.7; }

/* Main */
.kho-main { flex: 1; min-width: 0; }

/* Filter row */
.filter-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.result-count { font-size: 13px; color: var(--text-muted); }
.result-count strong { color: var(--text); }

.sort-select {
  appearance: none;
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  border-radius: var(--radius-sm);
  padding: 6px 32px 6px 12px;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2371717a' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  transition: border-color 0.2s;
}
.sort-select:focus { outline: none; border-color: var(--primary); }

.view-toggle { display: flex; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; }
.view-toggle button { width: 36px; height: 34px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: all 0.2s; cursor: pointer; }
.view-toggle button.active { background: var(--primary-light); color: var(--primary); }
.view-toggle button svg { width: 16px; height: 16px; }

/* Grid */
.stories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; margin-bottom: 32px; }
@media (min-width: 768px) { .stories-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .stories-grid { grid-template-columns: repeat(5, 1fr); } }

/* Story card for placeholder */
.story-card { display: flex; flex-direction: column; gap: 8px; text-decoration: none; transition: transform 0.2s; }
.story-card:hover { transform: translateY(-4px); }
.story-card-thumb { position: relative; aspect-ratio: 3 / 4; border-radius: var(--radius-md); overflow: hidden; }
.story-card-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.story-card:hover .story-card-thumb img { transform: scale(1.05); }
.story-card-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.8)); }
.story-card-status { position: absolute; top: 8px; left: 8px; }
.story-card-title { font-size: 14px; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.story-card:hover .story-card-title { color: var(--primary); }
.story-card-author { font-size: 12px; color: var(--text-muted); }
.story-card-meta { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); }
.story-card-meta svg { width: 14px; height: 14px; }

/* List view */
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }
.story-list-item { display: flex; gap: 14px; padding: 14px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; }
.story-list-item:last-child { border-bottom: none; }
.story-list-item:hover { background: var(--bg-muted); }
.story-list-thumb { width: 72px; height: 108px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; }
.story-list-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.story-list-item:hover .story-list-thumb img { transform: scale(1.05); }
.story-list-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.story-list-title { font-size: 15px; font-weight: 600; line-height: 1.4; margin-bottom: 6px; }
.story-list-item:hover .story-list-title { color: var(--primary); }
.story-list-author { font-size: 12px; color: var(--text-muted); margin-bottom: 8px; }
.story-list-tags { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; }
.story-list-stat { display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-muted); }
.story-list-stat span { display: flex; align-items: center; gap: 4px; }
.story-list-stat svg { width: 12px; height: 12px; }

/* Badges */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border); }
.badge-success { background: rgba(34, 197, 94, 0.1); color: var(--success); border-color: var(--success-border); }

/* No results */
.no-results { text-align: center; padding: 40px 20px; color: var(--text-muted); }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 28px 0 8px; }
.page-btn { min-width: 36px; height: 36px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: pointer; padding: 0 4px; }
.page-btn:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
.page-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-btn svg { width: 15px; height: 15px; }
</style>

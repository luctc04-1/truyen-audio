<template>
  <div class="library-page">
    <div class="container">

      <!-- Page header -->
      <div class="page-header">
        <h1 class="page-title">Danh sách truyện</h1>
        <p class="page-subtitle">Hàng trăm tập truyện audio chất lượng cao, cập nhật liên tục</p>
      </div>

      <!-- API error -->
      <div v-if="libError && !libLoading" class="api-error">
        <p>{{ libError }}</p>
        <button type="button" class="btn btn-sm btn-outline" @click="fetchLibrary(currentPage, { force: true })">Thử lại</button>
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
        <div
          v-for="genre in storyStore.allGenres"
          :key="genre.id"
          :class="['genre-pill', { active: storyStore.selectedGenre === genre.id }]"
          @click="selectGenre(genre.id)"
        >
          {{ genre.label }}
        </div>
      </div>

      <div class="kho-layout">
        <!-- MAIN CONTENT -->
        <div class="kho-main">

          <!-- Filter row -->
          <div class="filter-row">
            <div class="result-count"><strong>{{ libPagination.total }}</strong> truyện</div>
            <div style="flex:1;"></div>
            <div class="sort-dropdown" ref="sortDropdownRef">
              <button
                type="button"
                class="sort-dropdown-trigger"
                :class="{ open: sortDropdownOpen }"
                @click="sortDropdownOpen = !sortDropdownOpen"
              >
                <span>Sắp xếp: {{ currentFilterLabel }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <div v-if="sortDropdownOpen" class="sort-dropdown-menu">
                <button
                  v-for="opt in FILTER_OPTIONS"
                  :key="opt.id"
                  type="button"
                  :class="['sort-dropdown-item', { active: libraryFilter === opt.id }]"
                  @click="selectFilter(opt.id)"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>
            <div class="view-toggle">
              <button :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Dạng lưới">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              </button>
              <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Dạng danh sách">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
              </button>
            </div>
          </div>

          <!-- LOADING SKELETON -->
          <div v-if="libLoading" class="stories-grid">
            <StoryCardSkeleton v-for="n in 12" :key="'sk-lib-' + n" />
          </div>

          <!-- GRID VIEW -->
          <div v-else-if="viewMode === 'grid'" class="stories-grid">
            <StoryCard v-for="story in libStories" :key="story.id" :story="story" />
          </div>

          <!-- LIST VIEW -->
          <div v-else class="card" style="overflow:hidden;">
            <div v-for="story in libStories" :key="story.id" class="story-list-item" @click="$router.push(`/story/${story.id}`)">
              <div class="story-list-thumb">
                <img :src="story.image" :alt="story.title">
                <div class="story-list-overlay"></div>
              </div>
              <div class="story-list-info">
                <div>
                  <div class="story-list-title">{{ story.title }}</div>
                  <div v-if="storyCategoryTags(story).length" class="story-list-tags">
                    <span
                      v-for="label in storyCategoryTags(story)"
                      :key="label"
                      class="list-tag list-tag-category"
                    >{{ label }}</span>
                  </div>
                  <div class="story-list-status">
                    <VipBadge v-if="story.is_premium" />
                    <span v-else-if="story.status === 'completed'" class="list-tag list-tag-success">Hoàn thành</span>
                    <span v-else class="list-tag">Đang cập nhật</span>
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
                </div>
              </div>
            </div>
          </div>

          <!-- No results -->
          <div v-if="!libLoading && !libError && libStories.length === 0" class="no-results">
            <p>Không tìm thấy truyện phù hợp</p>
          </div>

          <!-- Pagination -->
          <div v-if="!libLoading && totalPages > 1" class="pagination">
            <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <template v-for="(p, i) in visiblePages" :key="p">
              <span v-if="i > 0 && visiblePages[i - 1] !== p - 1" style="color:var(--text-muted);padding:0 4px;">…</span>
              <button :class="['page-btn', { active: p === currentPage }]" @click="currentPage = p">{{ p }}</button>
            </template>
            <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
          </div>

        </div><!-- /kho-main -->
      </div><!-- /kho-layout -->
    </div><!-- /container -->
  </div>
</template>

<script>
export default { name: 'LibraryPage' }
</script>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStoryStore } from '@/stores/storyStore'
import StoryService from '@/services/StoryService'
import StoryCard from '@/components/StoryCard.vue'
import StoryCardSkeleton from '@/components/StoryCardSkeleton.vue'
import VipBadge from '@/components/VipBadge.vue'
import { getFollowedIds } from '@/utils/follows'

const FILTER_OPTIONS = [
  { id: 'all', label: 'Tất cả' },
  { id: 'newest', label: 'Mới nhất' },
  { id: 'popular', label: 'Phổ biến nhất' },
  { id: 'updating', label: 'Đang cập nhật' },
  { id: 'following', label: 'Đang theo dõi' },
]

const storyStore = useStoryStore()
const route = useRoute()
const router = useRouter()

if (typeof route.query.category === 'string' && route.query.category && route.query.category !== 'all') {
  storyStore.setGenre(route.query.category)
}
const searchQuery = ref('')
const libraryFilter = ref('all')
const sortDropdownOpen = ref(false)
const sortDropdownRef = ref(null)
const currentPage = ref(1)
const viewMode = ref('grid')
const statusFilter = ref('')
const PER_PAGE = 24

const currentFilterLabel = computed(
  () => FILTER_OPTIONS.find((o) => o.id === libraryFilter.value)?.label ?? 'Tất cả'
)

const selectFilter = (id) => {
  libraryFilter.value = id
  sortDropdownOpen.value = false
}

const handleClickOutside = (e) => {
  if (sortDropdownRef.value && !sortDropdownRef.value.contains(e.target)) {
    sortDropdownOpen.value = false
  }
}

// Server-side state — độc lập với storyStore để không ảnh hưởng trang chủ
const libStories = ref([])
const libPagination = ref({ current_page: 1, last_page: 1, per_page: PER_PAGE, total: 0 })
const libLoading = ref(false)
const libError = ref(null)
const libraryCache = new Map()
let searchTimer = null

const buildLibraryCacheKey = (page) =>
  [
    storyStore.selectedGenre,
    searchQuery.value.trim().toLowerCase(),
    libraryFilter.value,
    statusFilter.value,
    page,
  ].join('|')

const fetchLibrary = async (page = 1, { force = false } = {}) => {
  const cacheKey = buildLibraryCacheKey(page)

  if (!force && libraryCache.has(cacheKey)) {
    const cached = libraryCache.get(cacheKey)
    libStories.value = cached.items
    libPagination.value = cached.pagination
    libLoading.value = false
    libError.value = null
    return
  }

  libLoading.value = true
  libError.value = null
  libStories.value = []
  try {
    const params = { page, per_page: PER_PAGE }
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    if (storyStore.selectedGenre !== 'all') {
      const categoryName = storyStore.getGenreFilterName(storyStore.selectedGenre)
      if (categoryName) params.category = categoryName
    }

    if (libraryFilter.value === 'newest') {
      params.sort = 'newest'
    } else if (libraryFilter.value === 'popular') {
      params.sort = 'popular'
    } else if (libraryFilter.value === 'updating') {
      params.status = 'updating'
      params.sort = 'newest'
    } else if (libraryFilter.value === 'following') {
      params.per_page = 200
      params.sort = 'newest'
    } else {
      params.sort = 'trending'
    }

    if (statusFilter.value && libraryFilter.value !== 'updating') {
      params.status = statusFilter.value
    }

    const response = await StoryService.getStories(params)
    const payload = response?.data ?? response
    let items = payload?.items ?? []
    let pagination = payload?.pagination ?? libPagination.value

    if (libraryFilter.value === 'following') {
      const followed = new Set(getFollowedIds())
      items = items.filter((s) => followed.has(String(s.id)))
      pagination = {
        current_page: 1,
        last_page: 1,
        per_page: PER_PAGE,
        total: items.length,
      }
    }

    libStories.value = items
    libPagination.value = pagination
    libraryCache.set(cacheKey, { items, pagination })
  } catch (err) {
    libError.value = err?.message ?? 'Không tải được dữ liệu'
    libStories.value = []
  } finally {
    libLoading.value = false
  }
}

const totalPages = computed(() => libPagination.value.last_page || 1)
const visiblePages = computed(() => {
  const total = totalPages.value
  const cur = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur - 1, cur, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const getGenreCount = (genreId) => {
  if (genreId === 'all') return libPagination.value.total
  return null
}

const storyCategoryTags = (story) => {
  if (Array.isArray(story?.category_labels) && story.category_labels.length) {
    return storyStore.mapCategoryLabels(story.category_labels)
  }
  if (story?.genre_label) return [storyStore.getCategoryLabel(story.genre_label)]
  return []
}

const selectGenre = (genreId) => {
  storyStore.setGenre(genreId)
  const query = { ...route.query }
  if (genreId === 'all') {
    delete query.category
  } else {
    query.category = genreId
  }
  router.replace({ query })
}

const resetAndFetch = () => {
  currentPage.value = 1
  fetchLibrary(1)
}

onMounted(() => {
  fetchLibrary(1)
  document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
  clearTimeout(searchTimer)
  document.removeEventListener('click', handleClickOutside)
})

watch(libraryFilter, resetAndFetch)
watch(statusFilter, resetAndFetch)
watch(() => storyStore.selectedGenre, resetAndFetch)
watch(() => route.query.category, (category) => {
  if (typeof category === 'string' && category && category !== 'all') {
    if (storyStore.selectedGenre !== category) storyStore.setGenre(category)
    return
  }
  if (!category && storyStore.selectedGenre !== 'all') storyStore.setGenre('all')
})

watch(searchQuery, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(resetAndFetch, 400)
})

watch(currentPage, (page) => {
  fetchLibrary(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
})
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

.api-error {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px 16px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(239, 68, 68, 0.35);
  background: rgba(239, 68, 68, 0.08);
  color: #fca5a5;
  font-size: 14px;
}

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

.sort-dropdown {
  position: relative;
  flex-shrink: 0;
}

.sort-dropdown-trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 168px;
  height: 36px;
  padding: 0 12px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
}

.sort-dropdown-trigger:hover,
.sort-dropdown-trigger.open {
  border-color: var(--border-strong);
  background: var(--bg-muted);
}

.sort-dropdown-trigger svg {
  width: 14px;
  height: 14px;
  color: var(--text-muted);
  margin-left: auto;
  flex-shrink: 0;
  transition: transform 0.2s;
}

.sort-dropdown-trigger.open svg {
  transform: rotate(180deg);
}

.sort-dropdown-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  z-index: 50;
  min-width: 100%;
  padding: 6px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}

.sort-dropdown-item {
  display: block;
  width: 100%;
  padding: 10px 12px;
  border: none;
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}

.sort-dropdown-item:hover {
  background: var(--bg-muted);
}

.sort-dropdown-item.active {
  background: var(--primary-light);
  color: var(--primary);
  font-weight: 600;
}


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
.story-card-meta { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); }
.story-card-meta svg { width: 14px; height: 14px; }

/* List view */
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }
.story-list-item { display: flex; gap: 14px; padding: 14px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; }
.story-list-item:last-child { border-bottom: none; }
.story-list-item:hover { background: var(--bg-muted); }
.story-list-thumb { width: 72px; height: 108px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; position: relative; }
.story-list-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.story-list-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 55%, rgba(0, 0, 0, 0.45)); pointer-events: none; }
.story-list-item:hover .story-list-thumb img { transform: scale(1.05); }
.story-list-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.story-list-title { font-size: 15px; font-weight: 600; line-height: 1.4; margin-bottom: 6px; }
.story-list-item:hover .story-list-title { color: var(--primary); }
.story-list-tags { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 6px; align-items: center; }
.story-list-status { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; align-items: center; }
.list-tag {
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  font-size: 10px;
  font-weight: 600;
  line-height: 1.3;
  background: var(--bg-muted);
  color: var(--text-muted);
  border: 1px solid var(--border);
  white-space: nowrap;
}
.list-tag-success {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  border-color: var(--success-border);
}
.list-tag-category {
  font-weight: 500;
  background: rgba(255, 255, 255, 0.04);
}
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

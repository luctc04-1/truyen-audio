import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import StoryService from '@/services/StoryService'

export const useStoryStore = defineStore('story', () => {
  const stories = ref([])
  const selectedGenre = ref('all')

  // Loading tách biệt để điều hướng giữa các trang không bị giật.
  const listLoading = ref(false)
  const homeLoading = ref(false)
  const detailLoading = ref(false)
  const error = ref(null)
  const loaded = ref(false)
  const homeHotStories = ref([])
  const recentEpisodes = ref([])
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 50, total: 0 })

  /** Danh mục cố định — `name` khớp giá trị trong cột `category` (Supabase). */
  const GENRES = [
    { id: 'ngon-tinh', label: '💕 Ngôn Tình', name: 'Ngôn Tình' },
    { id: 'audio-dai', label: '🎬 Audio Dài', name: 'Audio Dài' },
    { id: 'trinh-tham', label: '🕵️ Trinh Thám', name: 'Trinh Thám' },
    { id: 'giang-ho', label: '🗡️ Giang Hồ', name: 'Giang Hồ' },
    { id: 'hoc-duong', label: '💼 Học Đường', name: 'Học Đường' },
    { id: 'xuyen-khong', label: '✈️ Xuyên Không', name: 'Xuyên Không' },
    { id: 'roi-nuoc-mac', label: '🎭 Rơi Nước Mắt', name: 'Rơi Nước Mắt' },
    { id: 'hai-huoc', label: '😂 Hài Hước', name: 'Hài Hước' },
    { id: 'khoa-hoc', label: '🔬 Khoa Học', name: 'Khoa Học' },
    { id: 'kinh-di', label: '👻 Kinh Dị', name: 'Kinh Dị' },
  ]

  const allGenres = computed(() => [
    { id: 'all', label: '💕 Tất cả', name: null },
    ...GENRES,
  ])

  const homeGenres = computed(() => GENRES)

  const getGenreById = (genreId) =>
    allGenres.value.find((g) => g.id === genreId) ?? null

  const getGenreFilterName = (genreId) => getGenreById(genreId)?.name ?? null

  /** Tên danh mục DB → nhãn hiển thị kèm emoji (vd. "Audio Dài" → "🎬 Audio Dài"). */
  const getCategoryLabel = (name) => {
    if (!name) return ''
    const genre = GENRES.find(
      (g) => g.name.toLowerCase() === String(name).trim().toLowerCase()
    )
    return genre?.label ?? String(name).trim()
  }

  const mapCategoryLabels = (labels) =>
    (Array.isArray(labels) ? labels : []).map((name) => getCategoryLabel(name))

  const filteredStories = computed(() => {
    if (selectedGenre.value === 'all') return stories.value
    const name = getGenreFilterName(selectedGenre.value)
    if (!name) return stories.value
    return stories.value.filter((s) =>
      (s.category_labels ?? []).some((label) => label.toLowerCase() === name.toLowerCase())
      || (s.genre_label && s.genre_label.toLowerCase().includes(name.toLowerCase()))
    )
  })

  const trendingStories = computed(() => homeHotStories.value)

  const popularStories = computed(() =>
    [...stories.value].sort((a, b) => (b.plays_raw ?? 0) - (a.plays_raw ?? 0)).slice(0, 12)
  )

  const latestStories = computed(() =>
    [...stories.value]
      .sort((a, b) => new Date(b.created_at ?? 0) - new Date(a.created_at ?? 0))
      .slice(0, 12)
  )

  const hotStories = computed(() => popularStories.value)

  const upsertStory = (story) => {
    if (!story?.id) return
    const index = stories.value.findIndex((s) => String(s.id) === String(story.id))
    if (index >= 0) {
      stories.value[index] = { ...stories.value[index], ...story }
    } else {
      stories.value.push(story)
    }
  }

  const patchStoryRating = (id, average) => {
    const story = getStoryById(id)
    if (!story) return
    upsertStory({ ...story, rating: Number(average).toFixed(1) })
  }

  const getStoryById = (id) => {
    if (!id) return null
    return stories.value.find((s) => String(s.id) === String(id)) ?? null
  }

  const loadHome = async ({ force = false } = {}) => {
    homeLoading.value = true
    error.value = null

    try {
      await Promise.all([
        loadStories({ per_page: 36 }, { force }),
        loadHotStories(),
        loadRecentEpisodes(),
      ])
    } catch (err) {
      console.error('[storyStore] loadHome failed:', err)
      error.value = err?.message ?? 'Không tải được dữ liệu trang chủ'
    } finally {
      homeLoading.value = false
    }
  }

  const loadHotStories = async () => {
    try {
      const response = await StoryService.getHotStories(10)
      const payload = response?.data ?? response
      homeHotStories.value = payload?.items ?? []
    } catch (err) {
      console.error('[storyStore] loadHotStories failed:', err)
      homeHotStories.value = []
    }
  }

  const loadRecentEpisodes = async () => {
    try {
      const response = await StoryService.getRecentEpisodes(5)
      const payload = response?.data ?? response
      recentEpisodes.value = Array.isArray(payload) ? payload : []
    } catch (err) {
      console.error('[storyStore] loadRecentEpisodes failed:', err)
      recentEpisodes.value = []
    }
  }

  const loadStories = async (params = {}, { force = false } = {}) => {
    if (loaded.value && !force) return
    if (listLoading.value) return

    listLoading.value = true
    error.value = null
    stories.value = []

    try {
      const response = await StoryService.getStories({ per_page: 50, ...params })
      const payload = response?.data ?? response

      stories.value = payload?.items ?? []
      pagination.value = payload?.pagination ?? pagination.value
      loaded.value = true
    } catch (err) {
      console.error('[storyStore] loadStories failed:', err)
      error.value = err?.message ?? 'Không tải được dữ liệu'
      if (!stories.value.length) stories.value = []
    } finally {
      listLoading.value = false
    }
  }

  const loadStoryDetail = async (id, { force = false } = {}) => {
    if (!id) return null

    const cached = getStoryById(id)
    if (!force && cached?.episodes?.length) return cached

    detailLoading.value = true
    error.value = null

    try {
      const response = await StoryService.getStory(id)
      const story = response?.data ?? response
      upsertStory(story)
      return story
    } catch (err) {
      console.error('[storyStore] loadStoryDetail failed:', err)
      error.value = err?.message ?? 'Không tải được chi tiết truyện'
      return null
    } finally {
      detailLoading.value = false
    }
  }

  const reloadStoryDetail = async (id) => loadStoryDetail(id, { force: true })

  const loadEpisode = async (episodeId) => {
    if (!episodeId) return null

    try {
      const response = await StoryService.getEpisode(episodeId)
      const payload = response?.data ?? response

      if (payload?.series) {
        upsertStory({ ...payload.series, episodes: payload.series.episodes ?? [] })
      }

      return payload
    } catch (err) {
      console.error('[storyStore] loadEpisode failed:', err)
      error.value = err?.message ?? 'Không tải được tập truyện'
      return null
    }
  }

  const setGenre = (genreId) => {
    selectedGenre.value = genreId
  }

  const genreCount = (genreId) => {
    if (genreId === 'all') return stories.value.length
    const name = getGenreFilterName(genreId)
    if (!name) return 0
    return stories.value.filter((s) =>
      (s.category_labels ?? []).some((label) => label.toLowerCase() === name.toLowerCase())
      || (s.genre_label && s.genre_label.toLowerCase().includes(name.toLowerCase()))
    ).length
  }

  return {
    stories,
    selectedGenre,
    listLoading,
    homeLoading,
    detailLoading,
    error,
    loaded,
    homeHotStories,
    recentEpisodes,
    pagination,
    allGenres,
    homeGenres,
    getGenreById,
    getGenreFilterName,
    getCategoryLabel,
    mapCategoryLabels,
    filteredStories,
    trendingStories,
    popularStories,
    latestStories,
    hotStories,
    upsertStory,
    patchStoryRating,
    getStoryById,
    loadHome,
    loadHotStories,
    loadRecentEpisodes,
    loadStories,
    loadStoryDetail,
    reloadStoryDetail,
    loadEpisode,
    setGenre,
    genreCount,
  }
})

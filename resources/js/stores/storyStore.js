import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useStoryStore = defineStore('story', () => {
  // State
  const currentStory = ref(null)
  const currentEpisode = ref(null)
  const isPlaying = ref(false)
  const currentTime = ref(0)
  const duration = ref(0)
  const stories = ref([])
  const filteredStories = ref([])
  const selectedGenre = ref('all')

  // Mock data
  const mockStories = [
    {
      id: 1,
      title: 'Nuôi Vợ Hào Môn Mà Không Hay Biết',
      author: 'Sói Review 2510',
      image: 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80',
      genre: 'ngon-tinh',
      status: 'completed',
      plays: '52.1K',
      episodeCount: 43,
      rating: 5.0,
      description: 'Một câu chuyện tình yêu đầy cảm động...',
      episodes: [
        { id: 1, title: 'Tập 1: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:06:01' },
        { id: 2, title: 'Tập 2: Nuôi Vợ Hào Môn Mà Không Hay Biết', duration: '1:04:43' },
      ]
    },
    {
      id: 2,
      title: 'Vợ Tôi Giữ Gìn Cho Tình Đầu Sắp Trở Về',
      author: 'Sói Review 2510',
      image: 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=400&q=80',
      genre: 'ngon-tinh',
      status: 'updating',
      plays: '51.4K',
      episodeCount: 32,
      rating: 4.8,
      description: 'Một tình yêu đầy ắc úc và tân tưởng...',
      episodes: []
    }
  ]

  // Computed
  const allGenres = computed(() => [
    { id: 'all', label: '💕 Tất cả' },
    { id: 'ngon-tinh', label: '💕 Ngôn Tình' },
    { id: 'audio-dai', label: '🎬 Audio Dài' },
    { id: 'trinh-tham', label: '🕵️ Trinh Thám' },
    { id: 'giang-ho', label: '🗡️ Giang Hồ' },
    { id: 'hoc-duong', label: '💼 Học Đường' },
    { id: 'xuyen-khong', label: '✈️ Xuyên Không' },
    { id: 'roi-nuoc-mac', label: '🎭 Rơi Nước Mắt' },
    { id: 'hai-huoc', label: '😂 Hài Hước' },
    { id: 'khoa-hoc', label: '🔬 Khoa Học' },
    { id: 'kinh-di', label: '👻 Kinh Dị' }
  ])

  // Methods
  const loadStories = () => {
    stories.value = mockStories
    filterStories()
  }

  const filterStories = () => {
    if (selectedGenre.value === 'all') {
      filteredStories.value = stories.value
    } else {
      filteredStories.value = stories.value.filter(s => s.genre === selectedGenre.value)
    }
  }

  const setGenre = (genreId) => {
    selectedGenre.value = genreId
    filterStories()
  }

  const getStoryById = (id) => {
    return stories.value.find(s => s.id === parseInt(id))
  }

  const playStory = (story) => {
    currentStory.value = story
    isPlaying.value = true
  }

  const pause = () => {
    isPlaying.value = false
  }

  const resume = () => {
    isPlaying.value = true
  }

  const setCurrentTime = (time) => {
    currentTime.value = time
  }

  const setDuration = (dur) => {
    duration.value = dur
  }

  // Initialize
  loadStories()

  return {
    currentStory,
    currentEpisode,
    isPlaying,
    currentTime,
    duration,
    stories,
    filteredStories,
    selectedGenre,
    allGenres,
    loadStories,
    filterStories,
    setGenre,
    getStoryById,
    playStory,
    pause,
    resume,
    setCurrentTime,
    setDuration
  }
})

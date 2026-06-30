import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { canPlayEpisode } from '@/utils/access'

// Một instance Audio duy nhất cho toàn app (không đưa vào reactive state).
let audio = null

const SPEEDS = [1, 1.3, 1.5, 2.0, 3.0]

export const useAudioStore = defineStore('audio', () => {
  const currentStory = ref(null)
  const currentEpisode = ref(null)
  const queue = ref([])
  const isPlaying = ref(false)
  const isBuffering = ref(false)
  const currentTime = ref(0)
  const duration = ref(0)
  const speed = ref(1)
  const volume = ref(1)
  const muted = ref(false)
  const expanded = ref(false)
  const sleepTimer = ref(0)
  const stopAfterEpisode = ref(false)

  let sleepHandle = null
  const SLEEP_OPTIONS = [0, 5, 10, 15, 30, 45, 60]

  const ensureAudio = () => {
    if (audio) return audio

    audio = new Audio()
    audio.preload = 'metadata'

    audio.addEventListener('timeupdate', () => {
      currentTime.value = audio.currentTime
    })
    audio.addEventListener('loadedmetadata', () => {
      duration.value = audio.duration || currentEpisode.value?.duration_seconds || 0
    })
    audio.addEventListener('durationchange', () => {
      if (Number.isFinite(audio.duration) && audio.duration > 0) {
        duration.value = audio.duration
      }
    })
    audio.addEventListener('play', () => { isPlaying.value = true })
    audio.addEventListener('pause', () => { isPlaying.value = false })
    audio.addEventListener('waiting', () => { isBuffering.value = true })
    audio.addEventListener('playing', () => { isBuffering.value = false })
    audio.addEventListener('canplay', () => { isBuffering.value = false })
    audio.addEventListener('ended', () => {
      if (stopAfterEpisode.value) {
        stopAfterEpisode.value = false
        isPlaying.value = false
        return
      }
      next()
    })

    return audio
  }

  const currentIndex = computed(() => {
    if (!currentEpisode.value) return -1
    return queue.value.findIndex((e) => String(e.id) === String(currentEpisode.value.id))
  })

  const hasNext = computed(() => {
    const i = currentIndex.value
    const auth = useAuthStore()
    return i >= 0 && queue.value.slice(i + 1).some((e) => canPlayEpisode(e, auth))
  })

  const hasPrev = computed(() => currentIndex.value > 0)

  const progress = computed(() => {
    if (!duration.value) return 0
    return Math.min(100, (currentTime.value / duration.value) * 100)
  })

  const speedLabel = computed(() => `${speed.value}x`)

  const isCurrent = (episodeId) =>
    currentEpisode.value && String(currentEpisode.value.id) === String(episodeId)

  const _loadSource = (episode, autoplay = true) => {
    const a = ensureAudio()
    if (!episode?.audio_url) {
      currentEpisode.value = episode
      return
    }

    if (a.src !== episode.audio_url) {
      a.src = episode.audio_url
      a.load()
    }
    a.playbackRate = speed.value
    a.volume = volume.value
    a.muted = muted.value

    currentEpisode.value = episode
    currentTime.value = 0
    duration.value = episode.duration_seconds || 0

    if (autoplay) play()
  }

  /**
   * Phát một tập. Nếu trùng tập đang phát thì toggle play/pause.
   */
  const playEpisode = (story, episode, episodes = null) => {
    if (!episode) return
    if (story) currentStory.value = story
    if (Array.isArray(episodes)) queue.value = episodes

    if (isCurrent(episode.id)) {
      togglePlay()
      return
    }

    _loadSource(episode, true)
  }

  /**
   * Đảm bảo context phát đúng tập (dùng khi mở trang /episode trực tiếp).
   * Không tự phát lại nếu đã đúng tập.
   */
  const ensureEpisode = (story, episode, episodes = null, autoplay = false) => {
    if (!episode) return
    if (story) currentStory.value = story
    if (Array.isArray(episodes)) queue.value = episodes

    if (isCurrent(episode.id)) return
    _loadSource(episode, autoplay)
  }

  const play = async () => {
    const a = ensureAudio()
    try {
      await a.play()
    } catch {
      // Trình duyệt có thể chặn autoplay khi chưa có thao tác người dùng.
    }
  }

  const pause = () => {
    audio?.pause()
  }

  const togglePlay = () => {
    if (!currentEpisode.value?.audio_url) return
    isPlaying.value ? pause() : play()
  }

  const seek = (seconds) => {
    const a = ensureAudio()
    const max = duration.value || 0
    a.currentTime = Math.max(0, Math.min(max, seconds))
    currentTime.value = a.currentTime
  }

  const seekToPercent = (percent) => seek(((percent / 100) * (duration.value || 0)))

  const skip = (delta) => seek((audio?.currentTime || 0) + delta)

  const setSpeed = (value) => {
    speed.value = value
    if (audio) audio.playbackRate = value
  }

  const cycleSpeed = () => {
    const idx = SPEEDS.indexOf(speed.value)
    setSpeed(SPEEDS[(idx + 1) % SPEEDS.length])
  }

  const setVolume = (value) => {
    volume.value = value
    if (audio) audio.volume = value
    if (value > 0 && muted.value) toggleMute(false)
  }

  const toggleMute = (force) => {
    muted.value = typeof force === 'boolean' ? force : !muted.value
    if (audio) audio.muted = muted.value
  }

  const next = () => {
    const i = currentIndex.value
    if (i < 0) return
    const auth = useAuthStore()
    const upcoming = queue.value.slice(i + 1).find((e) => canPlayEpisode(e, auth))
    if (upcoming) _loadSource(upcoming, true)
    else isPlaying.value = false
  }

  const prev = () => {
    const i = currentIndex.value
    if (i > 0) {
      const auth = useAuthStore()
      const previous = queue.value.slice(0, i).reverse().find((e) => canPlayEpisode(e, auth))
      if (previous) _loadSource(previous, true)
    }
  }

  const stop = () => {
    pause()
    if (audio) {
      audio.removeAttribute('src')
      audio.load()
    }
    currentEpisode.value = null
    currentStory.value = null
    queue.value = []
    currentTime.value = 0
    duration.value = 0
    isPlaying.value = false
    expanded.value = false
    setSleepTimer(0)
    stopAfterEpisode.value = false
  }

  const expand = () => { expanded.value = true }
  const collapse = () => { expanded.value = false }
  const toggleExpand = () => { expanded.value = !expanded.value }

  const setSleepTimer = (minutes) => {
    sleepTimer.value = minutes
    stopAfterEpisode.value = false
    if (sleepHandle) {
      clearTimeout(sleepHandle)
      sleepHandle = null
    }
    if (minutes > 0) {
      sleepHandle = setTimeout(() => {
        pause()
        sleepTimer.value = 0
        sleepHandle = null
      }, minutes * 60000)
    }
  }

  const setStopAfterEpisode = (val) => {
    stopAfterEpisode.value = val
    if (val) {
      sleepTimer.value = 0
      if (sleepHandle) {
        clearTimeout(sleepHandle)
        sleepHandle = null
      }
    }
  }

  const cycleSleepTimer = () => {
    const idx = SLEEP_OPTIONS.indexOf(sleepTimer.value)
    setSleepTimer(SLEEP_OPTIONS[(idx + 1) % SLEEP_OPTIONS.length])
  }

  return {
    currentStory,
    currentEpisode,
    queue,
    isPlaying,
    isBuffering,
    currentTime,
    duration,
    speed,
    volume,
    muted,
    expanded,
    sleepTimer,
    currentIndex,
    hasNext,
    hasPrev,
    progress,
    speedLabel,
    isCurrent,
    playEpisode,
    ensureEpisode,
    play,
    pause,
    togglePlay,
    seek,
    seekToPercent,
    skip,
    setSpeed,
    cycleSpeed,
    setVolume,
    toggleMute,
    next,
    prev,
    stop,
    expand,
    collapse,
    toggleExpand,
    setSleepTimer,
    setStopAfterEpisode,
    stopAfterEpisode,
    cycleSleepTimer,
  }
})

<template>
  <div class="episode-manager">
    <!-- Toolbar -->
    <div class="manager-toolbar">
      <div class="series-selector-box">
        <label>Chọn bộ truyện:</label>
        <select v-model="selectedSeriesId" @change="onSeriesChange">
          <option value="">-- Tất cả bộ truyện --</option>
          <option v-for="s in seriesOptions" :key="s.id" :value="s.id">
            {{ s.title }} ({{ formatSeriesOptionCount(s) }})
          </option>
        </select>
      </div>

      <div class="search-box">
        <i class="ri-search-line"></i>
        <input
          v-model="search"
          type="search"
          placeholder="Tìm tiêu đề tập truyện..."
          @input="debounceFetch"
        />
        <button v-if="search" class="btn-clear-search" type="button" @click="search = ''; fetchEpisodes(1)">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <button class="btn btn-primary btn-glow" type="button" @click="openCreateModal">
        <i class="ri-upload-cloud-2-line"></i>
        <span>Thêm tập audio mới</span>
      </button>
    </div>

    <!-- 🌟 STORY SUMMARY DETAIL CARD (When a specific series is selected) -->
    <transition name="fade-slide">
      <div v-if="currentSeries" class="series-detail-banner card-panel">
        <div class="banner-inner">
          <div class="banner-cover-wrap">
            <img
              :src="currentSeries.cover_url || '/android-chrome-512x512.png'"
              :alt="currentSeries.title"
              @error="handleCoverError"
            />
          </div>

          <div class="banner-content">
            <div class="banner-header-row">
              <div>
                <div class="banner-tags">
                  <span class="badge badge-neutral">{{ currentSeries.category || 'Chưa phân loại' }}</span>
                  <span v-if="currentSeries.is_premium" class="badge badge-vip">VIP</span>
                  <span v-else class="badge badge-free">Free</span>
                  <span v-if="currentSeries.is_hot" class="badge badge-hot">Hot 🔥</span>
                  <span class="badge" :class="currentSeries.status === 'completed' ? 'badge-success' : 'badge-ongoing'">
                    {{ currentSeries.status === 'completed' ? 'Hoàn thành' : 'Đang phát' }}
                  </span>
                </div>
                <h3 class="banner-title">{{ currentSeries.title }}</h3>
              </div>

              <div class="banner-actions">
                <a :href="`/story/${currentSeries.id}`" target="_blank" class="btn btn-ghost btn-sm">
                  <i class="ri-external-link-line"></i> Xem web
                </a>
              </div>
            </div>

            <div class="banner-meta-row">
              <span class="banner-meta-item">
                <i class="ri-user-voice-line"></i> Tác giả: <strong>{{ currentSeries.author || 'Chưa cập nhật' }}</strong>
              </span>
              <span v-if="currentSeries.narrator" class="banner-meta-item">
                <i class="ri-mic-line"></i> MC: <strong>{{ currentSeries.narrator }}</strong>
              </span>
              <span class="banner-meta-item">
                <i class="ri-star-fill text-amber"></i> Đánh giá: <strong>{{ Number(currentSeries.average_rating || 5).toFixed(1) }}</strong>
              </span>
              <span class="banner-meta-item">
                <i class="ri-headphone-line"></i> Lượt nghe: <strong>{{ formatNumber(currentSeries.total_listens || currentSeries.listen_count || 0) }}</strong>
              </span>
            </div>

            <!-- Episode Count Stats Bar -->
            <div class="banner-episodes-bar">
              <div class="ep-progress-info">
                <span class="ep-stat-highlight">
                  <i class="ri-play-list-line"></i>
                  <template v-if="isSingleOrZeroSeries(currentSeries)">
                    <strong>Full trọn bộ</strong> ({{ pagination.total || 1 }} tập)
                  </template>
                  <template v-else>
                    <strong>{{ pagination.total || 0 }}</strong> tập đã đăng
                    <span v-if="currentSeries.total_episodes"> / <strong>{{ currentSeries.total_episodes }}</strong> tập dự kiến</span>
                  </template>
                </span>
                <span v-if="currentSeries.total_episodes && !isSingleOrZeroSeries(currentSeries)" class="ep-percentage">
                  {{ Math.min(Math.round(((pagination.total || 0) / currentSeries.total_episodes) * 100), 100) }}% tiến độ
                </span>
              </div>
              <div v-if="currentSeries.total_episodes && !isSingleOrZeroSeries(currentSeries)" class="ep-track">
                <div
                  class="ep-fill"
                  :style="{ width: `${Math.min(((pagination.total || 0) / currentSeries.total_episodes) * 100, 100)}%` }"
                ></div>
              </div>
            </div>

            <p v-if="currentSeries.description" class="banner-description">
              {{ currentSeries.description }}
            </p>
          </div>
        </div>
      </div>
    </transition>

    <!-- Main Episode Panel -->
    <div class="card-panel table-panel">
      <!-- Loading Progress Indicator Strip -->
      <div v-if="loading && episodes.length" class="table-loading-strip"></div>

      <div class="panel-head">
        <div>
          <h3>Danh sách tập truyện audio</h3>
          <span class="panel-subtitle">
            <span v-if="currentSeriesTitle">Đang xem bộ: <strong class="text-highlight">{{ currentSeriesTitle }}</strong> • </span>
            Tổng cộng {{ pagination.total || 0 }} tập
          </span>
        </div>

        <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchEpisodes(pagination.current_page)">
          <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
          <span>Làm mới</span>
        </button>
      </div>

      <!-- Skeleton Loading State -->
      <AdminTableSkeleton v-if="loading && !episodes.length" :columns="8" :rows="8" />

      <!-- Empty State -->
      <div v-else-if="!episodes.length" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-music-2-line"></i>
        </div>
        <h4>Chưa có tập audio nào</h4>
        <p>Chọn một bộ truyện hoặc bấm nút "Thêm tập audio mới" để bắt đầu</p>
      </div>

      <!-- Episode Table -->
      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th width="80">Số tập</th>
              <th>Tiêu đề tập</th>
              <th>Bộ truyện</th>
              <th>Thời lượng</th>
              <th>Lượt nghe</th>
              <th>Gói</th>
              <th>Nghe thử</th>
              <th class="text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ep in episodes" :key="ep.id" :class="{ 'row-playing': isPlaying(ep) }">
              <td>
                <span class="ep-badge" :class="{ 'badge-full': isEpisodeFull(ep) }">
                  {{ formatEpisodeBadge(ep) }}
                </span>
              </td>
              <td>
                <div class="ep-title-cell">
                  <strong class="ep-title">{{ ep.title }}</strong>
                  <div v-if="isPlaying(ep)" class="sound-wave-bars">
                    <span></span><span></span><span></span><span></span>
                  </div>
                </div>
              </td>
              <td>
                <span class="series-tag">{{ ep.series?.title || 'N/A' }}</span>
              </td>
              <td>
                <span class="text-muted"><i class="ri-time-line"></i> {{ formatDuration(ep.duration_seconds) }}</span>
              </td>
              <td>
                <span class="text-muted">{{ formatNumber(ep.play_count || ep.listen_count || 0) }}</span>
              </td>
              <td>
                <span class="badge" :class="ep.is_premium ? 'badge-vip' : 'badge-free'">
                  {{ ep.is_premium ? 'VIP' : 'Free' }}
                </span>
              </td>
              <td>
                <button
                  class="btn-play-preview"
                  :class="{ active: isPlaying(ep) }"
                  type="button"
                  :title="isPlaying(ep) ? 'Tạm dừng' : 'Nghe thử audio này'"
                  @click="togglePlayPreview(ep)"
                >
                  <i :class="isPlaying(ep) ? 'ri-pause-fill' : 'ri-play-fill'"></i>
                  <span>{{ isPlaying(ep) ? 'Đang phát' : 'Nghe thử' }}</span>
                </button>
              </td>
              <td class="text-right">
                <div class="row-actions">
                  <button class="btn-icon" type="button" title="Sửa thông tin tập" @click="openEditModal(ep)">
                    <i class="ri-edit-line"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="pagination-footer">
        <span class="page-info">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} tập)
        </span>
        <div class="pagination-controls">
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page <= 1 || loading"
            @click="fetchEpisodes(pagination.current_page - 1)"
          >
            <i class="ri-arrow-left-s-line"></i> Trước
          </button>
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="fetchEpisodes(pagination.current_page + 1)"
          >
            Sau <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Audio Player Preview Bar (Bottom Fixed Floating Glass Studio) -->
    <transition name="fade-slide">
      <div v-if="previewingAudio" class="audio-preview-bar">
        <div class="preview-info">
          <div class="preview-icon">
            <i class="ri-disc-line ri-spin"></i>
          </div>
          <div>
            <strong>{{ previewingAudio.title }}</strong>
            <small>{{ formatPreviewSub(previewingAudio) }}</small>
          </div>
        </div>

        <audio
          ref="audioPlayerRef"
          :src="previewingAudio.storage_audio_url || previewingAudio.audio_path"
          controls
          autoplay
          class="native-audio"
          @ended="previewingAudio = null"
        ></audio>

        <button class="btn-close-audio" type="button" title="Đóng nghe thử" @click="closeAudioPreview">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </transition>

    <!-- Modal Form: Thêm / Sửa Tập -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="modal.show" class="admin-modal-overlay" @click.self="modal.show = false">
          <div class="admin-modal modal-form-dialog">
            <div class="modal-header">
              <div class="modal-title-wrap">
                <div class="modal-icon-badge" :class="modal.isEdit ? 'badge-amber' : 'badge-emerald'">
                  <i :class="modal.isEdit ? 'ri-edit-2-line' : 'ri-file-music-line'"></i>
                </div>
                <div>
                  <h3>{{ modal.isEdit ? 'Chỉnh sửa tập audio' : 'Thêm tập audio mới' }}</h3>
                  <span class="modal-subtitle">
                    {{ modal.isEdit ? 'Cập nhật link audio, thời lượng và transcript cho tập' : 'Đăng tải tập audio mới cho bộ truyện đã chọn' }}
                  </span>
                </div>
              </div>
              <button class="btn-close" type="button" title="Đóng modal" @click="modal.show = false">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <form @submit.prevent="saveEpisode">
              <div class="modal-body form-body">
                <!-- Section 1: Thuộc tính tập -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-play-list-line"></i>
                    <span>Thông tin tập truyện</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group full-width">
                      <label>Bộ truyện trực thuộc <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-book-open-line"></i>
                        <select v-model="form.series_id" required>
                          <option value="" disabled>-- Chọn bộ truyện --</option>
                          <option v-for="s in seriesOptions" :key="s.id" :value="s.id">
                            {{ s.title }}
                          </option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Số tập / Thứ tự (0: Full) <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-hashtag"></i>
                        <input v-model.number="form.episode_number" type="number" min="0" required placeholder="1, 2, 3... (0: Full)" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Thời lượng (giây)</label>
                      <div class="input-with-icon">
                        <i class="ri-time-line"></i>
                        <input v-model.number="form.duration_seconds" type="number" min="0" placeholder="Ví dụ: 1450 (24:10)" />
                      </div>
                    </div>

                    <div class="form-group full-width">
                      <label>Tiêu đề tập <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-text"></i>
                        <input v-model="form.title" type="text" required placeholder="Chương 1: Khởi hành..." />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section 2: File Audio CDN -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-disc-line"></i>
                    <span>Đường dẫn Audio & Transcript</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group full-width">
                      <label>Link file Audio (Storage URL / CDN) <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-link"></i>
                        <input
                          v-model="form.storage_audio_url"
                          type="url"
                          required
                          placeholder="https://storage.truyen-audio.me/episodes/than-mo-1.mp3"
                        />
                      </div>
                    </div>

                    <div class="form-group full-width">
                      <label>Nội dung tóm tắt tập hoặc Transcript</label>
                      <textarea
                        v-model="form.transcript"
                        rows="3"
                        placeholder="Nội dung lời dẫn, ghi chú hoặc transcript của tập này..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Section 3: Phân quyền VIP -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-shield-star-line"></i>
                    <span>Gói nghe</span>
                  </div>
                  <div class="toggle-cards-grid">
                    <label class="toggle-card" :class="{ active: form.is_premium }">
                      <input v-model="form.is_premium" type="checkbox" class="sr-only" />
                      <div class="toggle-card-icon vip"><i class="ri-vip-crown-2-line"></i></div>
                      <div class="toggle-card-body">
                        <strong>Yêu cầu tài khoản VIP</strong>
                        <small>Chỉ thành viên VIP mới nghe được tập này</small>
                      </div>
                      <div class="switch-pill"></div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-ghost" type="button" @click="modal.show = false">
                  <span>Hủy bỏ</span>
                </button>
                <button class="btn btn-primary btn-glow" type="submit" :disabled="modal.submitting">
                  <i v-if="modal.submitting" class="ri-loader-4-line ri-spin"></i>
                  <i v-else class="ri-save-line"></i>
                  <span>{{ modal.isEdit ? 'Lưu thay đổi' : 'Thêm tập audio' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import AdminService from '@/services/AdminService'
import AdminTableSkeleton from './AdminTableSkeleton.vue'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const props = defineProps({
  initialSeriesId: {
    type: String,
    default: '',
  },
})

const toast = useToastStore()

const loading = ref(false)
const episodes = ref([])
const seriesOptions = ref([])
const selectedSeriesId = ref(props.initialSeriesId || '')
const search = ref('')

const previewingAudio = ref(null)
const audioPlayerRef = ref(null)

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 20,
})

const modal = reactive({
  show: false,
  isEdit: false,
  submitting: false,
  editingId: null,
})

const form = reactive({
  series_id: '',
  episode_number: 1,
  title: '',
  storage_audio_url: '',
  duration_seconds: 0,
  transcript: '',
  is_premium: false,
})

const currentSeries = computed(() => {
  if (!selectedSeriesId.value) return null
  return seriesOptions.value.find((s) => s.id === selectedSeriesId.value) || null
})

const currentSeriesTitle = computed(() => {
  return currentSeries.value ? currentSeries.value.title : ''
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchEpisodes(1)
  }, 300)
}

function onSeriesChange() {
  fetchEpisodes(1)
}

async function fetchSeriesOptions() {
  try {
    const res = await AdminService.getSeries({ per_page: 200 })
    const payload = extractApiPayload(res)
    seriesOptions.value = payload.items || []
  } catch (err) {
    console.error('Failed to load series options', err)
  }
}

async function fetchEpisodes(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
    }
    if (selectedSeriesId.value) params.series_id = selectedSeriesId.value
    if (search.value) params.search = search.value

    const res = await AdminService.getEpisodes(params)
    const payload = extractApiPayload(res)

    episodes.value = payload.items || []
    if (payload.pagination) {
      pagination.current_page = payload.pagination.current_page
      pagination.last_page = payload.pagination.last_page
      pagination.total = payload.pagination.total
    }
  } catch (err) {
    toast.error('Không thể tải danh sách tập audio')
  } finally {
    loading.value = false
  }
}

function isPlaying(ep) {
  return previewingAudio.value && previewingAudio.value.id === ep.id
}

function togglePlayPreview(ep) {
  if (isPlaying(ep)) {
    previewingAudio.value = null
  } else {
    previewingAudio.value = ep
  }
}

function closeAudioPreview() {
  previewingAudio.value = null
}

function openCreateModal() {
  modal.isEdit = false
  modal.editingId = null
  const defaultEpNumber = episodes.value.length > 0
    ? Math.max(...episodes.value.map((e) => e.episode_number || 0)) + 1
    : 1

  Object.assign(form, {
    series_id: selectedSeriesId.value || (seriesOptions.value[0]?.id || ''),
    episode_number: defaultEpNumber,
    title: `Chương ${defaultEpNumber}`,
    storage_audio_url: '',
    duration_seconds: 0,
    transcript: '',
    is_premium: false,
  })
  modal.show = true
}

function openEditModal(ep) {
  modal.isEdit = true
  modal.editingId = ep.id
  Object.assign(form, {
    series_id: ep.series_id,
    episode_number: ep.episode_number,
    title: ep.title,
    storage_audio_url: ep.storage_audio_url || ep.audio_path || '',
    duration_seconds: ep.duration_seconds || 0,
    transcript: ep.transcript || '',
    is_premium: Boolean(ep.is_premium),
  })
  modal.show = true
}

async function saveEpisode() {
  modal.submitting = true
  try {
    if (modal.isEdit) {
      await AdminService.updateEpisode(modal.editingId, form)
      toast.success('Cập nhật tập audio thành công!')
    } else {
      await AdminService.createEpisode(form)
      toast.success('Thêm tập audio mới thành công!')
    }
    modal.show = false
    fetchEpisodes(pagination.current_page)
    fetchSeriesOptions()
  } catch (err) {
    toast.error(err.message || 'Lỗi khi lưu tập audio')
  } finally {
    modal.submitting = false
  }
}

function formatDuration(seconds) {
  if (!seconds) return '00:00'
  const m = Math.floor(seconds / 60)
  const s = Math.floor(seconds % 60)
  return `${m}:${s < 10 ? '0' : ''}${s}`
}

function formatNumber(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num
}

function handleCoverError(e) {
  e.target.src = '/android-chrome-512x512.png'
}

function isSingleOrZeroSeries(seriesObj) {
  if (!seriesObj) return false
  const total = Number(seriesObj.total_episodes)
  const count = Number(seriesObj.episodes_count)
  return total === 1 || total === 0 || count === 1 || count === 0
}

function formatSeriesOptionCount(s) {
  if (!s) return '0 tập'
  const total = Number(s.total_episodes)
  const count = Number(s.episodes_count)
  if (total === 1 || total === 0 || count === 1 || count === 0 || s.total_episodes === 0) {
    return 'Full'
  }
  return `${s.total_episodes || s.episodes_count || 0} tập`
}

function isEpisodeFull(ep) {
  if (!ep) return false
  const epNum = Number(ep.episode_number)
  if (epNum === 0 || ep.episode_number === 0 || ep.episode_number === '0') return true
  const total = Number(currentSeries.value?.total_episodes ?? ep.series?.total_episodes)
  const count = Number(currentSeries.value?.episodes_count ?? pagination.total ?? episodes.value.length)
  if (total === 1 || total === 0 || count === 1) return true
  return false
}

function formatEpisodeBadge(ep) {
  if (isEpisodeFull(ep)) {
    return 'Full'
  }
  return `#${ep.episode_number}`
}

function formatPreviewSub(ep) {
  if (!ep) return 'Audio Studio Preview'
  const prefix = isEpisodeFull(ep) ? 'Tập Full' : `Tập #${ep.episode_number}`
  return `${prefix} • ${ep.series?.title || currentSeries.value?.title || 'Audio Studio Preview'}`
}

watch(
  () => props.initialSeriesId,
  (newVal) => {
    if (newVal) {
      selectedSeriesId.value = newVal
      fetchEpisodes(1)
    }
  }
)

onMounted(async () => {
  await fetchSeriesOptions()
  fetchEpisodes(1)
})
</script>

<style>
.episode-manager {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.manager-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}

.series-selector-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.series-selector-box label {
  font-size: 13px;
  font-weight: 600;
  color: var(--admin-muted, #94a3b8);
  white-space: nowrap;
}

.series-selector-box select {
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  color: #f8fafc;
  border-radius: 12px;
  padding: 9px 14px;
  font-size: 13px;
  font-weight: 500;
  outline: none;
  max-width: 300px;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.series-selector-box select:focus {
  border-color: #a855f7;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  border-radius: 12px;
  padding: 8px 16px;
  min-width: 260px;
  flex: 1;
}

.search-box:focus-within {
  border-color: #a855f7;
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
}

.search-box i {
  color: var(--admin-muted, #94a3b8);
  font-size: 16px;
}

.search-box input {
  background: transparent;
  border: none;
  color: #f8fafc;
  font-size: 14px;
  outline: none;
  width: 100%;
}

.btn-clear-search {
  background: transparent;
  border: none;
  color: var(--admin-muted, #94a3b8);
  cursor: pointer;
  padding: 0;
  display: grid;
  place-items: center;
  font-size: 16px;
}

/* 🌟 SELECTED SERIES CARD BANNER */
.series-detail-banner {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(15, 18, 28, 0.9) 100%);
  border: 1px solid rgba(168, 85, 247, 0.25);
  border-radius: 16px;
  padding: 18px 20px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.banner-inner {
  display: flex;
  gap: 18px;
  align-items: flex-start;
}

@media (max-width: 640px) {
  .banner-inner {
    flex-direction: column;
  }
}

.banner-cover-wrap {
  width: 72px;
  height: 96px;
  border-radius: 10px;
  overflow: hidden;
  background: #090a0f;
  border: 1px solid rgba(255, 255, 255, 0.12);
  flex-shrink: 0;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
}

.banner-cover-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.banner-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.banner-header-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.banner-tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
}

.banner-title {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
  color: #f8fafc;
}

.banner-meta-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;
  font-size: 12.5px;
  color: #cbd5e1;
}

.banner-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.banner-meta-item i {
  color: #a855f7;
}

.text-amber {
  color: #fbbf24 !important;
}

.banner-episodes-bar {
  display: flex;
  flex-direction: column;
  gap: 6px;
  background: rgba(0, 0, 0, 0.25);
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.ep-progress-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
}

.ep-stat-highlight {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #e2e8f0;
}

.ep-stat-highlight strong {
  color: #c084fc;
}

.ep-percentage {
  color: #34d399;
  font-weight: 600;
}

.ep-track {
  height: 4px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 4px;
  overflow: hidden;
}

.ep-fill {
  height: 100%;
  background: linear-gradient(90deg, #a855f7, #ec4899);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.banner-description {
  margin: 0;
  font-size: 12px;
  line-height: 1.5;
  color: #94a3b8;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.badge-hot {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-ongoing {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
}

.badge-free {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.table-panel {
  position: relative;
}

.table-loading-strip {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #a855f7, #ec4899, #a855f7);
  background-size: 200% 100%;
  animation: skeletonShimmer 1.2s infinite linear;
  z-index: 10;
}

.text-highlight {
  color: #c084fc;
}

.row-playing td {
  background: rgba(168, 85, 247, 0.1) !important;
}

.ep-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.06);
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 700;
  color: #c084fc;
  font-family: var(--admin-font-mono);
}

.ep-badge.badge-full {
  background: rgba(16, 185, 129, 0.18);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.35);
  font-weight: 800;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.ep-title-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 180px;
}

.ep-title {
  color: #f8fafc;
  font-size: 13.5px;
  font-weight: 600;
  line-height: 1.45;
  word-break: normal;
}

.sound-wave-bars {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: 14px;
}

.sound-wave-bars span {
  width: 3px;
  background: #a855f7;
  border-radius: 2px;
  animation: soundBar 1s infinite ease-in-out alternate;
}

.sound-wave-bars span:nth-child(1) { height: 60%; animation-delay: 0.1s; }
.sound-wave-bars span:nth-child(2) { height: 100%; animation-delay: 0.3s; }
.sound-wave-bars span:nth-child(3) { height: 40%; animation-delay: 0.2s; }
.sound-wave-bars span:nth-child(4) { height: 80%; animation-delay: 0.4s; }

@keyframes soundBar {
  0% { height: 20%; }
  100% { height: 100%; }
}

.series-tag {
  color: var(--admin-muted, #94a3b8);
  font-size: 13px;
}

.btn-play-preview {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid rgba(168, 85, 247, 0.35);
  background: rgba(168, 85, 247, 0.12);
  color: #c084fc;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-play-preview:hover,
.btn-play-preview.active {
  background: #a855f7;
  color: #ffffff;
  border-color: #a855f7;
  box-shadow: 0 0 14px rgba(168, 85, 247, 0.5);
  transform: translateY(-1px);
}

.row-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.audio-preview-bar {
  position: sticky;
  bottom: 24px;
  background: rgba(18, 22, 34, 0.9);
  border: 1px solid rgba(168, 85, 247, 0.5);
  border-radius: 18px;
  padding: 14px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(168, 85, 247, 0.25);
  z-index: 100;
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

.preview-info {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 220px;
}

.preview-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  color: white;
  display: grid;
  place-items: center;
  font-size: 20px;
  box-shadow: 0 0 15px rgba(168, 85, 247, 0.5);
}

.preview-info strong {
  display: block;
  font-size: 14px;
  color: #f8fafc;
  font-weight: 700;
}

.preview-info small {
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
}

.native-audio {
  flex: 1;
  max-width: 650px;
  height: 38px;
  outline: none;
}

.btn-close-audio {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  border: none;
  color: #cbd5e1;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-close-audio:hover {
  background: rgba(244, 63, 94, 0.2);
  color: #fb7185;
}

.pagination-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-info {
  font-size: 13px;
  color: var(--admin-muted, #94a3b8);
}

.pagination-controls {
  display: flex;
  gap: 8px;
}

.empty-state {
  padding: 70px 20px;
  text-align: center;
  color: var(--admin-muted, #94a3b8);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: rgba(168, 85, 247, 0.12);
  color: #c084fc;
  display: grid;
  place-items: center;
  font-size: 32px;
}

.empty-state h4 { color: #f8fafc; font-size: 16px; margin: 0; }
.empty-state p { margin: 0; font-size: 13px; }

/* Modal */
.admin-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5, 8, 16, 0.78);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  padding: 24px 16px;
  overflow: hidden;
}

.admin-modal {
  background: linear-gradient(180deg, #151926 0%, #0d1017 100%);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  width: 100%;
  max-width: 640px;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.9), 0 0 35px rgba(168, 85, 247, 0.12);
  overflow: hidden;
  position: relative;
  animation: modalPopIn 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.admin-modal form {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

@keyframes modalPopIn {
  0% {
    opacity: 0;
    transform: scale(0.95) translateY(12px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .admin-modal,
.modal-fade-leave-to .admin-modal {
  transform: scale(0.95) translateY(12px);
  opacity: 0;
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(18, 22, 34, 0.7);
  backdrop-filter: blur(8px);
  flex-shrink: 0;
}

.modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: #f8fafc; }

.btn-close {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--admin-muted, #94a3b8);
  font-size: 18px;
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-close:hover {
  background: rgba(244, 63, 94, 0.15);
  border-color: rgba(244, 63, 94, 0.3);
  color: #fb7185;
  transform: rotate(90deg);
}

.modal-body {
  padding: 24px;
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
  scrollbar-width: thin;
  scrollbar-color: rgba(168, 85, 247, 0.35) transparent;
}

.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(168, 85, 247, 0.35);
  border-radius: 4px;
}

.modal-title-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon-badge {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 20px;
  flex-shrink: 0;
}

.modal-icon-badge.badge-emerald {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(6, 182, 212, 0.25) 100%);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.4);
}

.modal-icon-badge.badge-amber {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(234, 88, 12, 0.25) 100%);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.4);
}

.modal-subtitle {
  display: block;
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
  margin-top: 2px;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 24px;
}

.form-section {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #f1f5f9;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.form-section-title i {
  color: #34d399;
  font-size: 16px;
}

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.full-width { grid-column: 1 / -1; }

.form-group label {
  display: block;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
  margin-bottom: 6px;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.input-with-icon > i {
  position: absolute;
  left: 14px;
  color: #64748b;
  font-size: 16px;
  pointer-events: none;
  transition: color 0.2s ease;
}

.input-with-icon input,
.input-with-icon select {
  padding-left: 40px !important;
}

.input-with-icon:focus-within > i {
  color: #34d399;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  background: rgba(15, 18, 28, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.09);
  color: #f8fafc;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13.5px;
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  background: rgba(20, 24, 38, 0.95);
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2), 0 0 16px rgba(16, 185, 129, 0.15);
}

.toggle-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.toggle-card {
  background: rgba(18, 22, 34, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.toggle-card:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.15);
}

.toggle-card.active {
  background: rgba(245, 158, 11, 0.12);
  border-color: rgba(245, 158, 11, 0.45);
  box-shadow: 0 0 16px rgba(245, 158, 11, 0.15);
}

.toggle-card-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  font-size: 16px;
  flex-shrink: 0;
}

.toggle-card-icon.vip { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

.toggle-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.toggle-card-body strong {
  font-size: 13px;
  color: #f8fafc;
}

.toggle-card-body small {
  font-size: 11px;
  color: #94a3b8;
}

.switch-pill {
  width: 38px;
  height: 22px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  position: relative;
  transition: all 0.25s ease;
  flex-shrink: 0;
}

.switch-pill::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #ffffff;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.toggle-card.active .switch-pill {
  background: #fbbf24;
}

.toggle-card.active .switch-pill::after {
  transform: translateX(16px);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

.required { color: #fb7185; }

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: rgba(15, 18, 28, 0.75);
  backdrop-filter: blur(8px);
  flex-shrink: 0;
}
</style>

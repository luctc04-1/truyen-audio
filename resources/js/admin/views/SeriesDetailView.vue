<template>
  <div class="series-detail-view">
    <!-- Page header (ngoài series-detail-page để tránh bị clip bởi overflow-x) -->
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Chi tiết truyện</h4>
          <div class="page-title-right d-flex flex-wrap gap-2 align-items-center">
            <router-link to="/admin/series" class="btn btn-soft-secondary btn-sm">
              <i class="ri-arrow-left-line me-1"></i>Quay lại
            </router-link>
            <router-link
              v-if="series"
              :to="`/admin/episodes?series_id=${series.id}`"
              class="btn btn-soft-info btn-sm"
            >
              <i class="ri-play-list-line me-1"></i>Quản lý tập
            </router-link>
            <button
              v-if="series"
              type="button"
              class="btn btn-primary btn-sm"
              @click="goEdit"
            >
              <i class="ri-pencil-line me-1"></i>Chỉnh sửa
            </button>
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item"><router-link to="/admin/series">Truyện</router-link></li>
              <li class="breadcrumb-item active text-truncate" style="max-width: 180px;">{{ series?.title || '...' }}</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="series-detail-page">
    <!-- Loading -->
    <div v-if="loading" class="card">
      <div class="card-body text-center py-5">
        <div class="spinner-border text-primary"></div>
        <p class="text-muted mt-3 mb-0">Đang tải thông tin truyện...</p>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="loadError" class="alert alert-danger border-0 shadow-sm">
      <div class="d-flex align-items-center gap-2">
        <i class="ri-error-warning-line fs-20"></i>
        <span>{{ loadError }}</span>
        <button class="btn btn-sm btn-danger ms-auto" type="button" @click="load">Thử lại</button>
      </div>
    </div>

    <template v-else-if="series">
      <!-- Hero: cover + info trong một card -->
      <div class="card border-0 shadow-sm series-detail-hero-card">
        <div class="card-body p-3 p-lg-4">
          <div class="row g-4 align-items-start">
            <!-- Left: cover & quick info -->
            <div class="col-xl-4">
              <div class="series-detail-cover-panel sticky-side-div">
                <div class="series-detail-cover-wrap rounded overflow-hidden">
                  <img
                    :src="series.cover_url"
                    :alt="series.title"
                    class="series-detail-cover-img"
                  />
                  <div class="series-detail-cover-badges">
                    <span v-if="series.is_hot" class="badge bg-danger">Hot</span>
                    <span v-if="series.is_premium" class="badge bg-primary">VIP</span>
                    <span
                      :class="series.is_complete ? 'badge bg-success' : 'badge bg-warning text-dark'"
                    >
                      {{ series.is_complete ? 'Hoàn thành' : 'Đang cập nhật' }}
                    </span>
                  </div>
                </div>
                <div class="mt-3">
                  <div class="series-detail-block mb-3">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <span
                        v-for="(star, i) in starIcons"
                        :key="i"
                        class="fs-18"
                        :class="star"
                      ></span>
                      <span class="fw-semibold ms-1">{{ series.average_rating ?? 0 }}</span>
                      <span class="text-muted fs-13">({{ ratings.length }} đánh giá)</span>
                    </div>
                  </div>
                  <div class="series-detail-quick-list">
                    <div v-if="series.author" class="series-detail-quick-item">
                      <i class="ri-quill-pen-line text-primary"></i>
                      <div>
                        <span class="text-muted d-block fs-12">Tác giả</span>
                        <span class="fw-medium">{{ series.author }}</span>
                      </div>
                    </div>
                    <div v-if="series.narrator" class="series-detail-quick-item">
                      <i class="ri-mic-line text-info"></i>
                      <div>
                        <span class="text-muted d-block fs-12">Người đọc</span>
                        <span class="fw-medium">{{ series.narrator }}</span>
                      </div>
                    </div>
                    <div v-if="series.category" class="series-detail-quick-item">
                      <i class="ri-bookmark-line text-success"></i>
                      <div>
                        <span class="text-muted d-block fs-12">Danh mục</span>
                        <span class="fw-medium">{{ series.category }}</span>
                      </div>
                    </div>
                    <div v-if="series.published_at" class="series-detail-quick-item">
                      <i class="ri-calendar-line text-warning"></i>
                      <div>
                        <span class="text-muted d-block fs-12">Xuất bản</span>
                        <span class="fw-medium">{{ formatAdminDate(series.published_at, { dateOnly: true }) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: title + stats -->
            <div class="col-xl-8">
              <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                <div class="flex-grow-1 min-w-0">
                  <h3 class="mb-2 series-detail-title">{{ series.title }}</h3>
                  <p v-if="series.slug" class="text-muted mb-0 fs-13 text-break">
                    <i class="ri-link me-1"></i><code class="text-muted">{{ series.slug }}</code>
                  </p>
                </div>
                <span
                  class="badge fs-12"
                  :class="statusBadgeClass"
                >
                  {{ statusLabel }}
                </span>
              </div>

              <!-- Stat widgets -->
              <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                  <div class="series-detail-stat">
                    <div class="series-detail-stat-icon bg-primary-subtle text-primary">
                      <i class="ri-stack-fill"></i>
                    </div>
                    <div>
                      <p class="text-muted mb-1 fs-13">Số tập</p>
                      <h4 class="mb-0">{{ series.total_episodes?.toLocaleString('vi-VN') ?? 0 }}</h4>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="series-detail-stat">
                    <div class="series-detail-stat-icon bg-success-subtle text-success">
                      <i class="ri-headphone-line"></i>
                    </div>
                    <div>
                      <p class="text-muted mb-1 fs-13">Lượt nghe</p>
                      <h4 class="mb-0">{{ formatCompactCount(series.total_listens) }}</h4>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="series-detail-stat">
                    <div class="series-detail-stat-icon bg-warning-subtle text-warning">
                      <i class="ri-star-fill"></i>
                    </div>
                    <div>
                      <p class="text-muted mb-1 fs-13">Điểm TB</p>
                      <h4 class="mb-0">★{{ series.average_rating ?? 0 }}</h4>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="series-detail-stat">
                    <div class="series-detail-stat-icon bg-info-subtle text-info">
                      <i class="ri-vip-crown-fill"></i>
                    </div>
                    <div>
                      <p class="text-muted mb-1 fs-13">Gói</p>
                      <h4 class="mb-0 fs-16">
                        {{ series.is_premium ? 'VIP' : 'Miễn phí' }}
                        <small v-if="series.is_hot" class="text-danger fw-normal">· Hot #{{ series.hot_order || '—' }}</small>
                      </h4>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Thông số / Thông tin cơ bản -->
              <div class="series-detail-block mt-4">
                <div class="row g-3 g-lg-4">
                  <div class="col-md-6">
                    <h6 class="fs-14 mb-3 text-muted text-uppercase">
                      <i class="ri-information-line me-1"></i>Thông tin cơ bản
                    </h6>
                    <dl class="series-detail-spec-list mb-0">
                      <div class="series-detail-spec-row">
                        <dt>Tác giả</dt>
                        <dd>{{ series.author || '—' }}</dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>Người đọc</dt>
                        <dd>{{ series.narrator || '—' }}</dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>Danh mục</dt>
                        <dd>
                          <template v-if="categoryTags.length">
                            <span
                              v-for="tag in categoryTags"
                              :key="tag"
                              class="badge bg-light text-body me-1 mb-1"
                            >{{ tag }}</span>
                          </template>
                          <span v-else>—</span>
                        </dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>Trạng thái</dt>
                        <dd><span class="badge" :class="statusBadgeClass">{{ statusLabel }}</span></dd>
                      </div>
                    </dl>
                  </div>
                  <div class="col-md-6">
                    <h6 class="fs-14 mb-3 text-muted text-uppercase">
                      <i class="ri-settings-3-line me-1"></i>Hệ thống
                    </h6>
                    <dl class="series-detail-spec-list mb-0">
                      <div class="series-detail-spec-row">
                        <dt>Slug</dt>
                        <dd><code class="series-detail-code">{{ series.slug || '—' }}</code></dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>ID</dt>
                        <dd><code class="series-detail-code">{{ series.id }}</code></dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>Ngày tạo</dt>
                        <dd>{{ formatAdminDate(series.created_at) }}</dd>
                      </div>
                      <div class="series-detail-spec-row">
                        <dt>Xuất bản</dt>
                        <dd>{{ series.published_at ? formatAdminDate(series.published_at) : '—' }}</dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Description -->
              <div v-if="series.description" class="series-detail-block mt-4">
                <h6 class="text-uppercase text-muted fs-12 fw-semibold mb-2">
                  <i class="ri-file-text-line me-1"></i>Mô tả
                </h6>
                <p class="mb-0 text-muted lh-lg">{{ series.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-bottom-0 pb-0 px-3 px-md-4">
          <ul class="nav nav-tabs nav-tabs-custom nav-success series-detail-tabs flex-nowrap" role="tablist">
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ active: activeTab === 'episodes' }"
                @click="activeTab = 'episodes'"
              >
                <i class="ri-play-list-line me-1"></i>Tập truyện
                <span class="badge bg-success-subtle text-success ms-1">{{ episodes.length }}</span>
              </button>
            </li>
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ active: activeTab === 'ratings' }"
                @click="activeTab = 'ratings'"
              >
                <i class="ri-star-line me-1"></i>Đánh giá
                <span class="badge bg-warning-subtle text-warning ms-1">{{ ratings.length }}</span>
              </button>
            </li>
          </ul>
        </div>
        <div class="card-body pt-3">
          <!-- Episodes tab -->
          <div v-show="activeTab === 'episodes'">
            <div v-if="episodesLoading" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary"></div>
              <span class="text-muted ms-2">Đang tải tập...</span>
            </div>
            <div v-else-if="episodesError" class="alert alert-danger mb-0">
              Không tải được danh sách tập.
              <button class="btn btn-link btn-sm p-0 align-baseline" type="button" @click="loadEpisodes">Thử lại</button>
            </div>
            <div v-else-if="!episodes.length" class="text-center py-5 text-muted">
              <i class="ri-play-list-line display-6 d-block mb-2 opacity-50"></i>
              <p class="mb-2">Chưa có tập nào.</p>
              <router-link :to="`/admin/episodes?series_id=${series.id}`" class="btn btn-primary btn-sm">
                <i class="ri-add-line me-1"></i>Thêm tập
              </router-link>
            </div>
            <div v-else>
              <div class="table-responsive">
                <table class="table table-hover align-middle series-detail-episode-table mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 64px;">#</th>
                      <th>Tên tập</th>
                      <th>Thời lượng</th>
                      <th>Lượt nghe</th>
                      <th>VIP</th>
                      <th>Audio</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ep in episodes" :key="ep.id">
                      <td>
                        <span class="series-detail-ep-num">
                          {{ ep.episode_number === 0 ? 'Full' : ep.episode_number }}
                        </span>
                      </td>
                      <td class="fw-medium">{{ ep.title }}</td>
                      <td class="text-muted">{{ ep.duration || '—' }}</td>
                      <td>{{ ep.play_count?.toLocaleString('vi-VN') ?? 0 }}</td>
                      <td>
                        <span v-if="ep.is_premium" class="badge bg-primary-subtle text-primary">VIP</span>
                        <span v-else class="text-muted">—</span>
                      </td>
                      <td>
                        <span
                          class="badge"
                          :class="ep.has_audio ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'"
                        >
                          <i :class="ep.has_audio ? 'ri-check-line' : 'ri-close-line'" class="me-1"></i>
                          {{ ep.has_audio ? 'Có' : 'Thiếu' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted fs-13">Hiển thị {{ episodes.length }} tập</span>
                <router-link :to="`/admin/episodes?series_id=${series.id}`" class="btn btn-soft-primary btn-sm">
                  <i class="ri-external-link-line me-1"></i>Quản lý tập
                </router-link>
              </div>
            </div>
          </div>

          <!-- Ratings tab -->
          <div v-show="activeTab === 'ratings'">
            <div v-if="ratingsLoading" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary"></div>
              <span class="text-muted ms-2">Đang tải đánh giá...</span>
            </div>
            <div v-else-if="!ratings.length" class="text-center py-5 text-muted">
              <i class="ri-star-line display-6 d-block mb-2 opacity-50"></i>
              <p class="mb-0">Chưa có đánh giá nào.</p>
            </div>
            <div v-else class="row g-4">
              <!-- Rating summary -->
              <div class="col-lg-4">
                <div class="series-detail-block series-detail-rating-summary h-100">
                  <div class="text-center mb-3">
                    <div class="series-detail-rating-big">{{ series.average_rating ?? 0 }}</div>
                    <div class="d-flex justify-content-center gap-1 mb-1">
                      <span
                        v-for="(star, i) in starIcons"
                        :key="'s' + i"
                        class="fs-20"
                        :class="star"
                      ></span>
                    </div>
                    <p class="text-muted mb-0 fs-13">{{ ratings.length }} đánh giá</p>
                  </div>
                  <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="series-detail-rating-bar row g-2 align-items-center mb-2">
                    <div class="col-auto">
                      <span class="fs-13 text-muted">{{ star }} <i class="ri-star-fill text-warning fs-11"></i></span>
                    </div>
                    <div class="col">
                      <div class="progress progress-sm">
                        <div
                          class="progress-bar bg-warning"
                          role="progressbar"
                          :style="{ width: ratingPercent(star) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div class="col-auto">
                      <span class="fs-13 text-muted">{{ ratingBreakdown[star] || 0 }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Review list -->
              <div class="col-lg-8">
                <div class="series-detail-block series-detail-reviews h-100">
                  <div
                    v-for="r in ratings"
                    :key="r.id"
                    class="series-detail-review-item"
                  >
                    <div class="d-flex align-items-start gap-3">
                      <div class="series-detail-review-avatar">
                        {{ (r.user?.username || '?').charAt(0).toUpperCase() }}
                      </div>
                      <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                          <span class="fw-semibold">{{ r.user?.username || 'Ẩn danh' }}</span>
                          <span class="text-warning fs-13">
                            <i
                              v-for="n in 5"
                              :key="n"
                              :class="n <= r.rating ? 'ri-star-fill' : 'ri-star-line'"
                            ></i>
                          </span>
                          <span class="text-muted fs-12 ms-auto">{{ formatAdminDate(r.created_at) }}</span>
                        </div>
                        <p v-if="r.content" class="text-muted mb-0 fs-14">{{ r.content }}</p>
                        <p v-else class="text-muted mb-0 fs-13 fst-italic">Không có nội dung</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminService from '@/services/AdminService'
import { extractApiPayload, formatAdminDate, formatCompactCount } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const loading = ref(true)
const loadError = ref(null)
const series = ref(null)
const episodes = ref([])
const episodesLoading = ref(false)
const episodesError = ref(false)
const ratings = ref([])
const ratingsLoading = ref(false)
const activeTab = ref('episodes')

const statusLabel = computed(() => {
  if (!series.value) return '—'
  if (series.value.is_complete) return 'Đã hoàn thành'
  return series.value.status === 'draft' ? 'Bản nháp' : 'Đang cập nhật'
})

const statusBadgeClass = computed(() => {
  if (!series.value) return 'bg-secondary-subtle text-secondary'
  if (series.value.is_complete) return 'bg-success-subtle text-success'
  if (series.value.status === 'draft') return 'bg-secondary-subtle text-secondary'
  return 'bg-warning-subtle text-warning'
})

const categoryTags = computed(() => {
  const raw = series.value?.category
  if (!raw) return []
  return String(raw).split(/[,|]/).map((s) => s.trim()).filter(Boolean)
})

const ratingBreakdown = computed(() => {
  const counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 }
  for (const r of ratings.value) {
    const star = Math.min(5, Math.max(1, Number(r.rating) || 0))
    if (counts[star] !== undefined) counts[star]++
  }
  return counts
})

const starIcons = computed(() => {
  const avg = Number(series.value?.average_rating) || 0
  const icons = []
  for (let i = 1; i <= 5; i++) {
    if (avg >= i) icons.push('ri-star-fill text-warning')
    else if (avg >= i - 0.5) icons.push('ri-star-half-fill text-warning')
    else icons.push('ri-star-line text-muted opacity-50')
  }
  return icons
})

const ratingPercent = (star) => {
  const total = ratings.value.length
  if (!total) return 0
  return Math.round(((ratingBreakdown.value[star] || 0) / total) * 100)
}

const loadEpisodes = async () => {
  if (!series.value?.id) return
  episodesLoading.value = true
  episodesError.value = false
  try {
    const res = extractApiPayload(await AdminService.getEpisodes({
      series_id: series.value.id,
      per_page: 200,
    }))
    episodes.value = res?.items ?? []
  } catch {
    episodesError.value = true
    episodes.value = []
  } finally {
    episodesLoading.value = false
  }
}

const loadRatings = async () => {
  if (!series.value?.id) return
  ratingsLoading.value = true
  try {
    const res = extractApiPayload(await AdminService.getRatings({
      series_id: series.value.id,
      per_page: 50,
    }))
    ratings.value = res?.items ?? []
  } catch {
    ratings.value = []
  } finally {
    ratingsLoading.value = false
  }
}

const load = async () => {
  loading.value = true
  loadError.value = null
  try {
    series.value = extractApiPayload(await AdminService.getSeriesById(route.params.id))
    await Promise.all([loadEpisodes(), loadRatings()])
  } catch (e) {
    loadError.value = e?.message || 'Không tìm thấy truyện'
    series.value = null
    toast.error(loadError.value)
  } finally {
    loading.value = false
  }
}

const goEdit = () => {
  router.push({ name: 'AdminSeries', query: { edit: series.value.id } })
}

onMounted(load)

watch(() => route.params.id, (id) => {
  if (id) load()
})
</script>

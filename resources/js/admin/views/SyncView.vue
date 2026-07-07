<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Đồng bộ & Jobs</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Đồng bộ</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-4 col-md-6">
        <div class="card card-animate"><div class="card-body">
          <p class="text-uppercase fw-medium text-muted mb-2">Series trong DB</p>
          <h3 class="mb-0">{{ status?.series_count?.toLocaleString('vi-VN') ?? '—' }}</h3>
        </div></div>
      </div>
      <div class="col-xl-4 col-md-6">
        <div class="card card-animate"><div class="card-body">
          <p class="text-uppercase fw-medium text-muted mb-2">Episodes trong DB</p>
          <h3 class="mb-0">{{ status?.episodes_count?.toLocaleString('vi-VN') ?? '—' }}</h3>
        </div></div>
      </div>
      <div class="col-xl-4 col-md-6">
        <div class="card card-animate"><div class="card-body">
          <p class="text-uppercase fw-medium text-muted mb-2">Nguồn Supabase</p>
          <h6 class="mb-0 text-truncate">{{ status?.supabase_url || 'Chưa cấu hình' }}</h6>
        </div></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h5 class="card-title mb-0">Đồng bộ từ Supabase</h5></div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-end">
          <button class="btn btn-primary" type="button" :disabled="running" @click="runSync('all')"><i class="ri-refresh-line me-1"></i>Đồng bộ tất cả</button>
          <button class="btn btn-soft-primary" type="button" :disabled="running" @click="runSync('series')">Chỉ series</button>
          <div>
            <label class="form-label mb-1">Đồng bộ episodes theo truyện</label>
            <div class="d-flex gap-2">
              <select v-model="syncSeriesId" class="form-select form-select-sm" style="min-width: 220px">
                <option value="">Tất cả truyện</option>
                <option v-for="s in seriesOptions" :key="s.id" :value="s.id">{{ s.title }}</option>
              </select>
              <button class="btn btn-soft-primary btn-sm" type="button" :disabled="running" @click="runSync('episodes')">Đồng bộ episodes</button>
            </div>
          </div>
          <button class="btn btn-light" type="button" :disabled="running" @click="refreshAll"><i class="ri-loop-left-line me-1"></i>Làm mới</button>
        </div>
        <div v-if="running" class="mt-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span class="text-muted">Đang đồng bộ...</span></div>
        <div v-if="lastResult" class="alert alert-success mt-3 mb-0">
          <strong>{{ lastResult.message }}</strong>
          <pre v-if="lastResult.data" class="mb-0 mt-2 fs-12">{{ JSON.stringify(lastResult.data, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">Crawl jobs</h5></div>
          <div class="card-body">
            <div v-if="!jobs?.crawl_jobs?.length" class="text-muted text-center py-3">Chưa có job crawl</div>
            <div v-for="job in jobs?.crawl_jobs" :key="job.id" class="border-bottom pb-2 mb-2">
              <div class="d-flex justify-content-between">
                <span :class="jobStatusClass(job.status)">{{ job.status }}</span>
                <small class="text-muted">{{ formatAdminDate(job.started_at) }}</small>
              </div>
              <small class="text-muted">+{{ job.inserted_series }} series, +{{ job.inserted_episodes }} episodes</small>
              <div v-if="job.error_message" class="text-danger fs-12">{{ job.error_message }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">Upload jobs</h5></div>
          <div class="card-body">
            <div v-if="!jobs?.upload_jobs?.length" class="text-muted text-center py-3">Chưa có job upload</div>
            <div v-for="job in jobs?.upload_jobs" :key="job.id" class="border-bottom pb-2 mb-2">
              <div class="d-flex justify-content-between">
                <strong class="fs-13">{{ job.episode?.title || 'Tập audio' }}</strong>
                <span :class="jobStatusClass(job.status)">{{ job.status }}</span>
              </div>
              <small class="text-muted">{{ job.episode?.series_title }}</small>
              <div v-if="job.error_message" class="text-danger fs-12">{{ job.error_message }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminService from '@/services/AdminService'
import { useAdminSeriesOptions } from '@/admin/composables/useAdminSeriesOptions'
import { extractApiPayload, formatAdminDate } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const toast = useToastStore()
const { seriesOptions, loadSeriesOptions } = useAdminSeriesOptions()
const status = ref(null)
const jobs = ref(null)
const syncSeriesId = ref('')
const running = ref(false)
const lastResult = ref(null)

const jobStatusClass = (s) => ({
  completed: 'badge bg-success-subtle text-success',
  running: 'badge bg-primary-subtle text-primary',
  failed: 'badge bg-danger-subtle text-danger',
  pending: 'badge bg-warning-subtle text-warning',
}[s] || 'badge bg-secondary-subtle text-secondary')

const loadStatus = async () => { status.value = extractApiPayload(await AdminService.getSyncStatus()) }
const loadJobs = async () => { jobs.value = extractApiPayload(await AdminService.getJobs()) }
const refreshAll = async () => { await Promise.all([loadStatus(), loadJobs()]) }

const runSync = async (type) => {
  running.value = true
  lastResult.value = null
  try {
    let res
    if (type === 'all') res = await AdminService.syncAll()
    else if (type === 'series') res = await AdminService.syncSeries()
    else res = await AdminService.syncEpisodes(syncSeriesId.value || null)
    lastResult.value = res
    toast.success(res.message || 'Đồng bộ thành công')
    await refreshAll()
  } catch (e) { toast.error(e.message || 'Đồng bộ thất bại') }
  finally { running.value = false }
}

onMounted(async () => {
  await loadSeriesOptions()
  await refreshAll()
})
</script>

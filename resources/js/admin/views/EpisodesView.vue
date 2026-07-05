<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Quản lý tập truyện</h4>
          <div class="page-title-right d-flex gap-2 align-items-center">
            <button class="btn btn-primary btn-sm" type="button" @click="openCreate">
              <i class="ri-add-line me-1"></i>Thêm tập
            </button>
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Tập truyện</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="items"
      :loading="loading"
      :error="loadError"
      :pagination="pagination"
      :per-page="perPage"
      empty-text="Không có tập nào"
      empty-icon="ri-play-circle-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
      @retry="load()"
    >
      <template #filters>
        <div class="row g-3">
          <div class="col-md-4">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm tập hoặc truyện..." @input="debouncedLoad" />
          </div>
          <div class="col-md-4">
            <select v-model="seriesId" class="form-select" @change="load(1)">
              <option value="">Tất cả truyện</option>
              <option v-for="s in seriesOptions" :key="s.id" :value="s.id">{{ s.title }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="filterMissing" class="form-select" @change="load(1)">
              <option value="">Tất cả audio</option>
              <option value="1">Thiếu file audio</option>
            </select>
          </div>
        </div>
        <div v-if="selectedIds.length" class="d-flex flex-wrap gap-2 mt-3">
          <span class="text-muted align-self-center">{{ selectedIds.length }} tập đã chọn</span>
          <button class="btn btn-sm btn-soft-warning" type="button" @click="runBulk('set_premium')">Đặt VIP</button>
          <button class="btn btn-sm btn-soft-secondary" type="button" @click="runBulk('unset_premium')">Bỏ VIP</button>
          <button class="btn btn-sm btn-soft-danger" type="button" @click="runBulk('delete')">Xóa</button>
        </div>
      </template>

      <template #cell-select="{ row }">
        <input type="checkbox" class="form-check-input" :checked="selectedIds.includes(row.id)" @change="toggleSelect(row.id)" />
      </template>

      <template #cell-title="{ row }">
        <strong>{{ row.title }}</strong>
        <div class="text-muted fs-12">#{{ row.episode_number }}</div>
      </template>

      <template #cell-has_audio="{ row }">
        <span :class="row.has_audio ? 'badge bg-success-subtle text-success' : 'badge bg-danger-subtle text-danger'">
          {{ row.has_audio ? 'Sẵn sàng' : 'Thiếu file' }}
        </span>
      </template>

      <template #cell-play_count="{ row }">
        {{ row.play_count?.toLocaleString('vi-VN') ?? '—' }}
      </template>

      <template #cell-is_premium="{ row }">
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" :checked="row.is_premium" :disabled="savingId === row.id" @change="togglePremium(row, $event.target.checked)" />
        </div>
      </template>

      <template #cell-actions="{ row }">
        <div class="btn-group btn-group-sm">
          <button class="btn btn-soft-primary" type="button" @click="openEdit(row)"><i class="ri-pencil-line"></i></button>
          <button class="btn btn-soft-danger" type="button" @click="remove(row)"><i class="ri-delete-bin-line"></i></button>
        </div>
      </template>
    </AdminDataTable>

    <div v-if="panelOpen" class="card mt-3">
      <div class="card-header"><h5 class="card-title mb-0">{{ editing ? 'Chỉnh sửa tập' : 'Thêm tập mới' }}</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Truyện *</label>
            <select v-model="form.series_id" class="form-select" :disabled="!!editing">
              <option value="">Chọn truyện</option>
              <option v-for="s in seriesOptions" :key="s.id" :value="s.id">{{ s.title }}</option>
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Số tập *</label><input v-model.number="form.episode_number" type="number" min="0" class="form-control" /></div>
          <div class="col-md-3"><label class="form-label">Thời lượng (giây)</label><input v-model.number="form.duration_seconds" type="number" min="0" class="form-control" /></div>
          <div class="col-12"><label class="form-label">Tiêu đề *</label><input v-model="form.title" class="form-control" /></div>
          <div class="col-12"><label class="form-label">Transcript</label><textarea v-model="form.transcript" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6">
            <label class="form-label">File audio (MP3)</label>
            <input type="file" accept="audio/*" class="form-control" @change="onAudioSelect" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Lên lịch đăng (tùy chọn)</label>
            <input v-model="form.publish_at" type="datetime-local" class="form-control" />
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch"><input id="ep-vip" v-model="form.is_premium" class="form-check-input" type="checkbox" /><label class="form-check-label" for="ep-vip">Tập VIP</label></div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="button" :disabled="saving || !canSave" @click="save">
            <i class="ri-save-3-line me-1"></i>{{ saving ? 'Đang lưu...' : 'Lưu' }}
          </button>
          <button class="btn btn-light" type="button" @click="closePanel">Hủy</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { useAdminSeriesOptions } from '@/admin/composables/useAdminSeriesOptions'
import { debounce, extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const columns = [
  { key: 'select', label: '', thClass: 'text-center', tdClass: 'text-center', width: '40px' },
  { key: 'title', label: 'Tập' },
  { key: 'series_title', label: 'Truyện' },
  { key: 'duration', label: 'Thời lượng' },
  { key: 'play_count', label: 'Lượt nghe' },
  { key: 'has_audio', label: 'Audio' },
  { key: 'is_premium', label: 'VIP', tdClass: 'text-center' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const route = useRoute()
const { seriesOptions, loadSeriesOptions } = useAdminSeriesOptions()
const loading = ref(true)
const loadError = ref(null)
const saving = ref(false)
const savingId = ref(null)
const items = ref([])
const search = ref('')
const seriesId = ref('')
const filterMissing = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20, from: 0, to: 0 })
const perPage = ref(20)
const panelOpen = ref(false)
const editing = ref(null)
const audioFile = ref(null)
const selectedIds = ref([])
const form = reactive({ series_id: '', title: '', episode_number: 1, duration_seconds: 0, transcript: '', is_premium: false, publish_at: '' })

const canSave = computed(() => form.title.trim() && form.series_id && form.episode_number >= 0)

const load = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const params = { page, per_page: perPage.value, search: search.value || undefined }
    if (seriesId.value) params.series_id = seriesId.value
    if (filterMissing.value === '1') params.missing_audio = 1
    const res = extractApiPayload(await AdminService.getEpisodes(params))
    items.value = res?.items ?? []
    pagination.value = res?.pagination ?? { current_page: 1, last_page: 1 }
  } catch (e) {
    loadError.value = e?.message || 'Không tải được danh sách tập'
    toast.error(loadError.value)
    items.value = []
  } finally { loading.value = false }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const togglePremium = async (item, value) => {
  savingId.value = item.id
  try {
    Object.assign(item, extractApiPayload(await AdminService.updateEpisode(item.id, { is_premium: value })))
    toast.success('Đã cập nhật')
  } catch { toast.error('Không thể cập nhật') }
  finally { savingId.value = null }
}

const resetForm = () => {
  form.series_id = seriesId.value || ''; form.title = ''; form.episode_number = 1
  form.duration_seconds = 0; form.transcript = ''; form.is_premium = false; form.publish_at = ''
  audioFile.value = null
}

const toggleSelect = (id) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id)
  } else {
    selectedIds.value = [...selectedIds.value, id]
  }
}

const runBulk = async (action) => {
  if (!selectedIds.value.length) return
  if (action === 'delete' && !confirm(`Xóa ${selectedIds.value.length} tập đã chọn?`)) return
  try {
    await AdminService.bulkEpisodes(action, selectedIds.value)
    toast.success('Đã xử lý hàng loạt')
    selectedIds.value = []
    await load(pagination.value.current_page)
  } catch {
    toast.error('Không thể thực hiện thao tác hàng loạt')
  }
}

const openCreate = () => { editing.value = null; resetForm(); panelOpen.value = true }
const openEdit = (item) => {
  editing.value = item
  form.series_id = item.series_id; form.title = item.title
  form.episode_number = item.episode_number; form.duration_seconds = item.duration_seconds || 0
  form.transcript = item.transcript || ''; form.is_premium = item.is_premium
  form.publish_at = item.publish_at ? item.publish_at.slice(0, 16) : ''
  panelOpen.value = true
}
const closePanel = () => { panelOpen.value = false; editing.value = null; resetForm() }
const onAudioSelect = (e) => { audioFile.value = e.target.files?.[0] || null }

const save = async () => {
  saving.value = true
  try {
    const payload = { ...form, publish_at: form.publish_at || undefined }
    let result
    if (editing.value) {
      result = extractApiPayload(await AdminService.updateEpisode(editing.value.id, payload))
      if (audioFile.value) result = extractApiPayload(await AdminService.uploadEpisodeAudio(editing.value.id, audioFile.value))
      const idx = items.value.findIndex((i) => i.id === editing.value.id)
      if (idx >= 0) items.value[idx] = result
      toast.success('Đã lưu tập')
    } else {
      result = extractApiPayload(await AdminService.createEpisode(payload))
      if (audioFile.value) result = extractApiPayload(await AdminService.uploadEpisodeAudio(result.id, audioFile.value))
      toast.success('Đã tạo tập')
      await load(1)
    }
    closePanel()
  } catch { toast.error('Không thể lưu tập') }
  finally { saving.value = false }
}

const remove = async (item) => {
  if (!confirm(`Xóa tập "${item.title}"?`)) return
  try {
    await AdminService.deleteEpisode(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
    toast.success('Đã xóa tập')
  } catch { toast.error('Không thể xóa') }
}

onMounted(async () => {
  if (route.query.series_id) seriesId.value = String(route.query.series_id)
  await loadSeriesOptions()
  await load()
})
</script>

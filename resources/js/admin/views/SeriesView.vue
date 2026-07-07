<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Quản lý truyện audio</h4>
          <div class="page-title-right d-flex gap-2 align-items-center">
            <button class="btn btn-primary btn-sm" type="button" @click="openCreate">
              <i class="ri-add-line me-1"></i>Thêm truyện
            </button>
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Truyện</li>
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
      :expanded-keys="expandedId ? [expandedId] : []"
      empty-text="Không có truyện nào. Hãy đồng bộ từ Supabase hoặc thêm truyện mới."
      empty-icon="ri-book-open-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
      @retry="load()"
    >
      <template #filters>
        <div class="row g-3 align-items-center">
          <div class="col-md-4">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm truyện, tác giả..." @input="debouncedLoad" />
          </div>
          <div class="col-md-3">
            <select v-model="filterPremium" class="form-select" @change="load(1)">
              <option value="">Tất cả gói</option>
              <option value="1">VIP</option>
              <option value="0">Miễn phí</option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="filterHot" class="form-select" @change="load(1)">
              <option value="">Tất cả hot</option>
              <option value="1">Hot</option>
              <option value="0">Thường</option>
            </select>
          </div>
        </div>
      </template>

      <template #cell-expand="{ row }">
        <button
          class="btn btn-sm btn-light"
          type="button"
          :title="expandedId === row.id ? 'Thu gọn' : 'Xem danh sách tập'"
          @click="toggleExpand(row)"
        >
          <i :class="expandedId === row.id ? 'ri-subtract-line' : 'ri-add-line'"></i>
        </button>
      </template>

      <template #cell-title="{ row }">
        <div class="d-flex align-items-center gap-2">
          <img :src="row.cover_url" :alt="row.title" class="table-cover" />
          <div>
            <h6 class="mb-0">
              <router-link :to="`/admin/series/${row.id}`" class="text-body">{{ row.title }}</router-link>
            </h6>
            <small class="text-muted">{{ row.author }} · {{ row.narrator }}</small>
          </div>
        </div>
      </template>

      <template #cell-total_listens="{ row }">
        {{ row.total_listens?.toLocaleString('vi-VN') ?? '—' }}
      </template>

      <template #cell-average_rating="{ row }">
        <span class="text-warning fw-medium">★{{ row.average_rating ?? 0 }}</span>
      </template>

      <template #cell-is_complete="{ row }">
        <span :class="row.is_complete ? 'badge bg-success-subtle text-success' : 'badge bg-warning-subtle text-warning'">
          {{ row.is_complete ? 'Đã hoàn thành' : 'Đang cập nhật' }}
        </span>
      </template>

      <template #cell-is_premium="{ row }">
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" :checked="row.is_premium" :disabled="savingId === row.id" @change="toggleField(row, 'is_premium', $event.target.checked)" />
        </div>
      </template>

      <template #cell-actions="{ row }">
        <div class="btn-group btn-group-sm">
          <router-link :to="`/admin/series/${row.id}`" class="btn btn-soft-info" title="Xem chi tiết">
            <i class="ri-eye-line"></i>
          </router-link>
          <button class="btn btn-soft-primary" type="button" @click="openEdit(row)"><i class="ri-pencil-line"></i></button>
<!--          <button class="btn btn-soft-danger" type="button" @click="remove(row)"><i class="ri-delete-bin-line"></i></button>-->
        </div>
      </template>

      <template #row-expand="{ row }">
        <div class="px-3 py-3">
          <div v-if="episodesState(row.id)?.loading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="text-muted ms-2">Đang tải tập...</span>
          </div>
          <div v-else-if="episodesState(row.id)?.error" class="text-danger py-2">
            Không tải được danh sách tập.
            <button class="btn btn-link btn-sm p-0 align-baseline" type="button" @click="loadEpisodes(row.id, true)">Thử lại</button>
          </div>
          <div v-else-if="!episodesState(row.id)?.items?.length" class="text-muted py-2">
            Chưa có tập nào.
            <router-link :to="`/admin/episodes?series_id=${row.id}`" class="ms-1">Thêm tập</router-link>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-sm table-bordered admin-episode-nested-table mb-0">
              <thead class="table-light">
                <tr>
                  <th>Tiêu đề tập</th>
                  <th style="width: 100px">Thời lượng</th>
                  <th style="width: 90px">Lượt nghe</th>
                  <th style="width: 60px" class="text-center">VIP</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ep in episodesState(row.id).items" :key="ep.id">
                  <td>
                      <span v-if="ep.episode_number !==0 ">Tập {{ ep.episode_number }} - {{ ep.title }}</span>
                      <span v-else>Full - {{ ep.title }}</span>
                  </td>
                  <td>{{ ep.duration || '—' }}</td>
                  <td>{{ ep.play_count?.toLocaleString('vi-VN') ?? 0 }}</td>
                  <td class="text-center">
                    <i v-if="ep.is_premium" class="ri-vip-crown-fill text-warning"></i>
                    <span v-else class="text-muted">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <small class="text-muted">{{ episodesState(row.id).items.length }} / {{ row.total_episodes }} tập</small>
              <router-link :to="`/admin/episodes?series_id=${row.id}`" class="btn btn-soft-primary btn-sm">
                Quản lý tập <i class="ri-arrow-right-line"></i>
              </router-link>
            </div>
          </div>
        </div>
      </template>
    </AdminDataTable>

    <Teleport to="body">
      <div v-if="panelOpen" class="admin-theme series-modal-root">
        <div class="modal-backdrop fade show"></div>

        <div
          id="seriesFormModal"
          class="modal fade show series-form-modal"
          tabindex="-1"
          aria-labelledby="seriesFormModalLabel"
          aria-modal="true"
          style="display: block"
          @click.self="closePanel"
        >
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
              <div class="modal-header bg-info-subtle p-3">
                <h5 id="seriesFormModalLabel" class="modal-title">
                  {{ editing ? 'Chỉnh sửa truyện' : 'Thêm truyện mới' }}
                </h5>
                <button type="button" class="btn-close" aria-label="Đóng" @click="closePanel"></button>
              </div>

              <form class="tablelist-form" autocomplete="off" @submit.prevent="save">
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="text-center">
                        <div class="series-modal-cover-wrap">
                          <label for="series-cover-input" class="series-modal-cover-box mb-0" title="Chọn ảnh bìa">
                            <img
                              v-if="coverDisplayUrl"
                              :src="coverDisplayUrl"
                              :alt="form.title || 'Ảnh bìa'"
                              class="series-modal-cover-img"
                            />
                            <span v-else class="series-modal-cover-placeholder">
                              <i class="ri-book-open-line"></i>
                            </span>
                          </label>
                          <label for="series-cover-input" class="series-modal-cover-edit" title="Đổi ảnh">
                            <i class="ri-image-fill"></i>
                          </label>
                          <input
                            ref="coverInputRef"
                            id="series-cover-input"
                            type="file"
                            accept="image/*"
                            class="d-none"
                            @change="onCoverSelect"
                          />
                        </div>
                        <p class="text-muted fs-12 mt-2 mb-0">
                          {{ coverPreviewUrl ? 'Ảnh xem trước (chưa lưu)' : 'Ảnh bìa' }}
                        </p>
                      </div>
                    </div>

                    <div class="col-lg-8">
                      <label class="form-label">Tên truyện <span class="text-danger">*</span></label>
                      <input v-model="form.title" class="form-control" placeholder="Nhập tên truyện" required />
                    </div>
                    <div class="col-lg-4">
                      <label class="form-label">Slug</label>
                      <input v-model="form.slug" class="form-control" placeholder="Tự tạo nếu để trống" />
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Tác giả</label>
                      <input v-model="form.author" class="form-control" placeholder="Tác giả" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Người đọc</label>
                      <input v-model="form.narrator" class="form-control" placeholder="Người đọc" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Danh mục</label>
                      <input v-model="form.category" class="form-control" list="series-category-list" placeholder="Chọn hoặc nhập" />
                      <datalist id="series-category-list">
                        <option v-for="cat in categoryOptions" :key="cat" :value="cat" />
                      </datalist>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Trạng thái</label>
                      <select v-model="form.status" class="form-select">
                        <option value="draft">Nháp</option>
                        <option value="published">Đã xuất bản</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Thứ tự Hot</label>
                      <input v-model.number="form.hot_order" type="number" min="0" class="form-control" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label d-block">Tuỳ chọn</label>
                      <div class="d-flex flex-wrap gap-3 pt-1">
                        <div class="form-check form-switch mb-0">
                          <input id="series-hot" v-model="form.is_hot" class="form-check-input" type="checkbox" />
                          <label class="form-check-label" for="series-hot">Hot</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                          <input id="series-vip" v-model="form.is_premium" class="form-check-input" type="checkbox" />
                          <label class="form-check-label" for="series-vip">VIP</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                          <input id="series-complete" v-model="form.is_complete" class="form-check-input" type="checkbox" />
                          <label class="form-check-label" for="series-complete">Hoàn thành</label>
                        </div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Mô tả</label>
                      <textarea
                        v-model="form.description"
                        class="form-control"
                        rows="4"
                        placeholder="Mô tả ngắn về truyện..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" @click="closePanel">Hủy</button>
                    <button type="submit" class="btn btn-success" :disabled="saving || !form.title.trim()">
                      <i class="ri-save-3-line me-1"></i>
                      {{ saving ? 'Đang lưu...' : (editing ? 'Cập nhật' : 'Thêm truyện') }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { debounce, extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const columns = [
  { key: 'expand', label: '', thClass: 'text-center', tdClass: 'text-center', width: '44px' },
  { key: 'title', label: 'Truyện' },
  { key: 'category', label: 'Danh mục' },
  { key: 'total_episodes', label: 'Tập' },
  { key: 'total_listens', label: 'Lượt nghe' },
  { key: 'average_rating', label: 'Đánh giá' },
  { key: 'is_complete', label: 'Trạng thái' },
  { key: 'actions', label: 'Actions' },
]

const toast = useToastStore()
const loading = ref(true)
const loadError = ref(null)
const saving = ref(false)
const savingId = ref(null)
const items = ref([])
const categoryOptions = ref([])
const search = ref('')
const filterPremium = ref('')
const filterHot = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20, from: 0, to: 0 })
const perPage = ref(20)
const panelOpen = ref(false)
const editing = ref(null)
const coverFile = ref(null)
const coverPreviewUrl = ref(null)
const coverInputRef = ref(null)
const form = reactive({ title: '', slug: '', author: '', narrator: '', category: '', description: '', is_hot: false, is_premium: false, is_complete: false, status: 'published', hot_order: 0 })
const route = useRoute()
const router = useRouter()
const expandedId = ref(null)
const episodesBySeries = ref({})

const episodesState = (seriesId) => episodesBySeries.value[seriesId]

const coverDisplayUrl = computed(() => coverPreviewUrl.value || editing.value?.cover_url || null)

const clearCoverPreview = () => {
  if (coverPreviewUrl.value) {
    URL.revokeObjectURL(coverPreviewUrl.value)
    coverPreviewUrl.value = null
  }
  if (coverInputRef.value) coverInputRef.value.value = ''
}

const loadEpisodes = async (seriesId, force = false) => {
  if (!force && episodesBySeries.value[seriesId]?.items) return

  episodesBySeries.value = {
    ...episodesBySeries.value,
    [seriesId]: { loading: true, items: [], error: false },
  }

  try {
    const res = extractApiPayload(await AdminService.getEpisodes({
      series_id: seriesId,
      per_page: 200,
    }))
    episodesBySeries.value = {
      ...episodesBySeries.value,
      [seriesId]: { loading: false, items: res?.items ?? [], error: false },
    }
  } catch {
    episodesBySeries.value = {
      ...episodesBySeries.value,
      [seriesId]: { loading: false, items: [], error: true },
    }
  }
}

const toggleExpand = async (row) => {
  if (expandedId.value === row.id) {
    expandedId.value = null
    return
  }
  expandedId.value = row.id
  await loadEpisodes(row.id)
}

const resetForm = () => {
  form.title = ''; form.slug = ''; form.author = ''; form.narrator = ''
  form.category = ''; form.description = ''; form.is_hot = false; form.is_premium = false
  form.is_complete = false; form.status = 'published'; form.hot_order = 0
  coverFile.value = null
  clearCoverPreview()
}

const load = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const params = { page, per_page: perPage.value, search: search.value || undefined }
    if (filterPremium.value !== '') params.is_premium = filterPremium.value
    if (filterHot.value !== '') params.is_hot = filterHot.value
    const res = extractApiPayload(await AdminService.getSeries(params))
    items.value = res?.items ?? []
    pagination.value = res?.pagination ?? { current_page: 1, last_page: 1 }
    expandedId.value = null
  } catch (e) {
    loadError.value = e?.message || 'Không tải được danh sách truyện'
    toast.error(loadError.value)
    items.value = []
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const cats = extractApiPayload(await AdminService.getCategories())
    categoryOptions.value = Array.isArray(cats) ? cats.map((c) => c.name) : []
  } catch {
    categoryOptions.value = []
  }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const toggleField = async (item, field, value) => {
  savingId.value = item.id
  try {
    const updated = extractApiPayload(await AdminService.updateSeries(item.id, { [field]: value }))
    Object.assign(item, updated)
    toast.success('Đã cập nhật')
  } catch { toast.error('Không thể cập nhật') }
  finally { savingId.value = null }
}

const openCreate = () => { editing.value = null; resetForm(); panelOpen.value = true }
const openEdit = (item) => {
  editing.value = item
  form.title = item.title; form.slug = item.slug || ''
  form.author = item.author || ''; form.narrator = item.narrator || ''
  form.category = item.category || ''; form.description = item.description || ''
  form.is_hot = item.is_hot; form.is_premium = item.is_premium
  form.is_complete = item.is_complete; form.status = item.status || 'published'; form.hot_order = item.hot_order || 0
  panelOpen.value = true
}

const openEditById = async (id) => {
  const item = items.value.find((i) => String(i.id) === String(id))
  if (item) {
    openEdit(item)
    return
  }
  try {
    const data = extractApiPayload(await AdminService.getSeriesById(id))
    openEdit(data)
  } catch {
    toast.error('Không tải được thông tin truyện')
  }
}
const closePanel = () => {
  panelOpen.value = false
  editing.value = null
  resetForm()
  if (route.query.edit) {
    const { edit: _edit, ...rest } = route.query
    router.replace({ query: rest })
  }
}

const onCoverSelect = (e) => {
  const file = e.target.files?.[0] || null
  if (coverPreviewUrl.value) {
    URL.revokeObjectURL(coverPreviewUrl.value)
    coverPreviewUrl.value = null
  }
  coverFile.value = file
  if (file) coverPreviewUrl.value = URL.createObjectURL(file)
}

const onKeydown = (e) => {
  if (e.key === 'Escape' && panelOpen.value) closePanel()
}

watch(panelOpen, (open) => {
  document.body.classList.toggle('modal-open', open)
  document.body.style.overflow = open ? 'hidden' : ''
})

const save = async () => {
  saving.value = true
  try {
    const payload = {
      title: form.title, slug: form.slug || undefined, author: form.author, narrator: form.narrator,
      category: form.category, description: form.description, is_hot: form.is_hot, is_premium: form.is_premium,
      is_complete: form.is_complete, status: form.status, hot_order: form.hot_order,
    }
    let result
    if (editing.value) {
      result = extractApiPayload(await AdminService.updateSeries(editing.value.id, payload))
      if (coverFile.value) result = extractApiPayload(await AdminService.uploadSeriesCover(editing.value.id, coverFile.value))
      const idx = items.value.findIndex((i) => i.id === editing.value.id)
      if (idx >= 0) items.value[idx] = result
      toast.success('Đã lưu truyện')
    } else {
      result = extractApiPayload(await AdminService.createSeries(payload))
      if (coverFile.value) result = extractApiPayload(await AdminService.uploadSeriesCover(result.id, coverFile.value))
      toast.success('Đã tạo truyện')
      await load(1)
    }
    closePanel()
  } catch { toast.error('Không thể lưu truyện') }
  finally { saving.value = false }
}

const remove = async (item) => {
  if (!confirm(`Xóa truyện "${item.title}" và tất cả tập?`)) return
  try {
    await AdminService.deleteSeries(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
    toast.success('Đã xóa truyện')
  } catch { toast.error('Không thể xóa') }
}

onMounted(async () => {
  window.addEventListener('keydown', onKeydown)
  if (route.query.search) search.value = String(route.query.search)
  if (route.query.is_hot !== undefined) filterHot.value = String(route.query.is_hot)
  await loadCategories()
  await load()
  if (route.query.edit) await openEditById(route.query.edit)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.classList.remove('modal-open')
  document.body.style.overflow = ''
  clearCoverPreview()
})

watch(() => route.query.search, (val) => {
  if (val !== undefined) {
    search.value = String(val)
    load(1)
  }
})

watch(() => route.query.is_hot, (val) => {
  if (val !== undefined) {
    filterHot.value = String(val)
    load(1)
  }
})

watch(() => route.query.edit, async (id) => {
  if (id && !panelOpen.value) await openEditById(id)
})
</script>

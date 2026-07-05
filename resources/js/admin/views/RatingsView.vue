<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Đánh giá truyện</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Đánh giá</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div v-if="stats" class="row mb-3">
      <div class="col-md-4">
        <div class="card"><div class="card-body text-center">
          <h3 class="text-primary mb-0">{{ stats.average }}★</h3>
          <small class="text-muted">{{ stats.total }} đánh giá</small>
        </div></div>
      </div>
      <div class="col-md-8">
        <div class="card"><div class="card-body">
          <div v-for="(count, star) in stats.by_star" :key="star" class="d-flex justify-content-between mb-1">
            <span>{{ star }} sao</span>
            <span class="badge bg-warning-subtle text-warning">{{ count }}</span>
          </div>
        </div></div>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="items"
      :loading="loading"
      :pagination="pagination"
      :per-page="perPage"
      empty-text="Không có đánh giá"
      empty-icon="ri-star-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
    >
      <template #filters>
        <div class="row g-3">
          <div class="col-md-6">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm truyện, nội dung..." @input="debouncedLoad" />
          </div>
          <div class="col-md-3">
            <select v-model="ratingFilter" class="form-select" @change="load(1)">
              <option value="">Tất cả sao</option>
              <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
            </select>
          </div>
        </div>
      </template>

      <template #cell-user="{ row }">
        <strong>{{ row.user?.username || 'Ẩn danh' }}</strong>
        <div class="text-warning">{{ '★'.repeat(row.rating) }}</div>
      </template>

      <template #cell-content="{ row }">
        {{ row.content || '—' }}
      </template>

      <template #cell-series="{ row }">
        {{ row.series?.title || '—' }}
      </template>

      <template #cell-actions="{ row }">
        <button class="btn btn-sm btn-soft-danger" type="button" @click="remove(row)">
          <i class="ri-delete-bin-line"></i>
        </button>
      </template>
    </AdminDataTable>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { debounce, extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const columns = [
  { key: 'user', label: 'User' },
  { key: 'content', label: 'Nội dung' },
  { key: 'series', label: 'Truyện' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const loading = ref(true)
const items = ref([])
const stats = ref(null)
const search = ref('')
const ratingFilter = ref('')
const perPage = ref(15)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15, from: 0, to: 0 })

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = extractApiPayload(await AdminService.getRatings({
      page, per_page: perPage.value, search: search.value || undefined, rating: ratingFilter.value || undefined,
    }))
    items.value = res.items
    pagination.value = res.pagination
    stats.value = res.stats
  } finally {
    loading.value = false
  }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const remove = async (item) => {
  if (!confirm('Xóa đánh giá này?')) return
  try {
    await AdminService.deleteRating(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
    toast.success('Đã xóa đánh giá')
    await load(pagination.value.current_page)
  } catch {
    toast.error('Không thể xóa')
  }
}

onMounted(() => load())
</script>

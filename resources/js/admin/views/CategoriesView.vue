<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Danh mục truyện</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Danh mục</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-8">
        <AdminDataTable
          title="Danh sách danh mục"
          :columns="columns"
          :rows="categories"
          :loading="loading"
          row-key="name"
          :show-length-menu="false"
          :pagination="{ current_page: 1, last_page: 1, total: categories.length, from: categories.length ? 1 : 0, to: categories.length }"
          empty-text="Chưa có danh mục"
          empty-icon="ri-price-tag-3-line"
        >
          <template #cell-name="{ row }">
            <strong>{{ row.name }}</strong>
          </template>
          <template #cell-series_count="{ row }">
            <span class="badge bg-primary-subtle text-primary">{{ row.series_count }}</span>
          </template>

          <template #cell-actions="{ row }">
            <button class="btn btn-soft-primary btn-sm" type="button" @click="startRename(row)">
              <i class="ri-pencil-line"></i> Đổi tên
            </button>
          </template>
        </AdminDataTable>
      </div>

      <div class="col-xl-4">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ renaming ? 'Đổi tên danh mục' : 'Thêm danh mục mới' }}</h5></div>
          <div class="card-body">
            <p v-if="renaming" class="text-muted">Đổi tên "<strong>{{ renaming }}</strong>" thành:</p>
            <label class="form-label">Tên danh mục</label>
            <input v-model="newName" class="form-control mb-3" placeholder="VD: Tiên hiệp" />
            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="button" :disabled="!newName.trim() || saving" @click="save">
                {{ renaming ? 'Đổi tên' : 'Thêm (gán khi tạo truyện)' }}
              </button>
              <button v-if="renaming" class="btn btn-light" type="button" @click="cancelRename">Hủy</button>
            </div>
            <small v-if="!renaming" class="text-muted d-block mt-2">Danh mục được gán khi tạo/chỉnh sửa truyện.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const columns = [
  { key: 'name', label: 'Tên danh mục' },
  { key: 'series_count', label: 'Số truyện' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const loading = ref(true)
const saving = ref(false)
const categories = ref([])
const renaming = ref(null)
const newName = ref('')

const load = async () => {
  loading.value = true
  try {
    categories.value = extractApiPayload(await AdminService.getCategories())
  } finally {
    loading.value = false
  }
}

const startRename = (cat) => {
  renaming.value = cat.name
  newName.value = cat.name
}

const cancelRename = () => {
  renaming.value = null
  newName.value = ''
}

const save = async () => {
  if (!newName.value.trim()) return
  if (!renaming.value) {
    toast.success('Hãy gán danh mục này khi tạo hoặc sửa truyện.')
    return
  }
  saving.value = true
  try {
    await AdminService.renameCategory(renaming.value, newName.value.trim())
    toast.success('Đã đổi tên danh mục')
    cancelRename()
    await load()
  } catch {
    toast.error('Không thể đổi tên')
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

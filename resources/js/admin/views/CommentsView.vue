<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Kiểm duyệt bình luận</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Bình luận</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="items"
      :loading="loading"
      :pagination="pagination"
      :per-page="perPage"
      empty-text="Không có bình luận"
      empty-icon="ri-chat-3-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
    >
      <template #filters>
        <div class="row g-3 align-items-center">
          <div class="col-md-8">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm nội dung, user..." @input="debouncedLoad" />
          </div>
          <div class="col-md-4">
            <div class="form-check">
              <input id="include-replies" v-model="includeReplies" class="form-check-input" type="checkbox" @change="load(1)" />
              <label class="form-check-label" for="include-replies">Bao gồm trả lời</label>
            </div>
          </div>
        </div>
      </template>

      <template #cell-user="{ row }">
        <strong>{{ row.user?.username || 'Ẩn danh' }}</strong>
      </template>

      <template #cell-content="{ row }">
        <p class="mb-0" style="max-width: 360px">{{ row.content }}</p>
      </template>

      <template #cell-context="{ row }">
        <small class="text-muted">
          {{ row.series?.title }}
          <span v-if="row.episode"> · Tập {{ row.episode.episode_number }}</span>
        </small>
      </template>

      <template #cell-actions="{ row }">
        <div class="d-flex gap-1 justify-content-end">
          <button
            class="btn btn-sm"
            :class="row.is_pinned ? 'btn-warning' : 'btn-soft-warning'"
            type="button"
            @click="togglePin(row)"
          >
            <i class="ri-pushpin-line"></i>
          </button>
          <button class="btn btn-sm btn-soft-danger" type="button" @click="remove(row)">
            <i class="ri-delete-bin-line"></i>
          </button>
        </div>
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
  { key: 'context', label: 'Ngữ cảnh' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const loading = ref(true)
const items = ref([])
const search = ref('')
const includeReplies = ref(false)
const perPage = ref(15)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15, from: 0, to: 0 })

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = extractApiPayload(await AdminService.getComments({
      page, per_page: perPage.value, search: search.value || undefined, include_replies: includeReplies.value ? 1 : undefined,
    }))
    items.value = res.items
    pagination.value = res.pagination
  } finally {
    loading.value = false
  }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const togglePin = async (item) => {
  try {
    const updated = extractApiPayload(await AdminService.pinComment(item.id, !item.is_pinned))
    Object.assign(item, updated)
    toast.success(item.is_pinned ? 'Đã ghim' : 'Đã bỏ ghim')
  } catch {
    toast.error('Không thể cập nhật')
  }
}

const remove = async (item) => {
  if (!confirm('Xóa bình luận này?')) return
  try {
    await AdminService.deleteComment(item.id)
    items.value = items.value.filter((i) => i.id !== item.id)
    toast.success('Đã xóa bình luận')
  } catch {
    toast.error('Không thể xóa')
  }
}

onMounted(() => load())
</script>

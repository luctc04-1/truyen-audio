<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Quản lý người dùng</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Người dùng</li>
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
      empty-text="Không có người dùng"
      empty-icon="ri-user-3-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
      @retry="load()"
    >
      <template #filters>
        <div class="row g-3">
          <div class="col-md-4">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm username, email..." @input="debouncedLoad" />
          </div>
          <div class="col-md-2">
            <select v-model="filterAdmin" class="form-select" @change="load(1)">
              <option value="">Tất cả quyền</option>
              <option value="1">Admin</option>
              <option value="0">User thường</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="filterPremium" class="form-select" @change="load(1)">
              <option value="">Tất cả gói</option>
              <option value="1">VIP</option>
              <option value="0">Free</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="filterBanned" class="form-select" @change="load(1)">
              <option value="">Tất cả trạng thái</option>
              <option value="1">Đã khóa</option>
              <option value="0">Hoạt động</option>
            </select>
          </div>
        </div>
      </template>

      <template #cell-user="{ row }">
        <strong>{{ row.username }}</strong>
        <div class="text-muted fs-12">{{ row.email }}</div>
        <span v-if="row.is_banned" class="badge bg-danger-subtle text-danger mt-1">Đã khóa</span>
      </template>

      <template #cell-is_premium="{ row }">
        <span :class="row.is_premium ? 'badge bg-warning-subtle text-warning' : 'badge bg-secondary-subtle text-secondary'">
          {{ row.is_premium ? 'VIP' : 'Free' }}
        </span>
        <div v-if="row.subscription?.end_at" class="text-muted fs-12">đến {{ formatAdminDate(row.subscription.end_at, { dateOnly: true }) }}</div>
      </template>

      <template #cell-is_admin="{ row }">
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" :checked="row.is_admin" :disabled="savingId === row.id" @change="updateField(row, 'is_admin', $event.target.checked)" />
        </div>
      </template>

      <template #cell-is_banned="{ row }">
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" :checked="row.is_banned" :disabled="savingId === row.id" @change="updateField(row, 'is_banned', $event.target.checked)" />
        </div>
      </template>

      <template #cell-actions="{ row }">
        <div class="btn-group btn-group-sm">
          <button class="btn btn-soft-warning" type="button" title="Cấp VIP" @click="openVipModal(row)"><i class="ri-vip-crown-line"></i></button>
          <button v-if="row.is_premium" class="btn btn-soft-secondary" type="button" title="Thu hồi VIP" @click="revokeVip(row)"><i class="ri-close-circle-line"></i></button>
        </div>
      </template>
    </AdminDataTable>

    <div v-if="vipModal" class="card mt-3">
      <div class="card-header"><h5 class="card-title mb-0">Cấp VIP: {{ vipModal.username }}</h5></div>
      <div class="card-body">
        <label class="form-label">Chọn gói</label>
        <select v-model="selectedPlanId" class="form-select mb-3">
          <option value="">Chọn gói VIP</option>
          <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} — {{ p.duration_days }} ngày</option>
        </select>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="button" :disabled="!selectedPlanId || vipSaving" @click="grantVip">Cấp VIP</button>
          <button class="btn btn-light" type="button" @click="vipModal = null">Hủy</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { debounce, extractApiPayload, formatAdminDate } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const columns = [
  { key: 'user', label: 'User' },
  { key: 'is_premium', label: 'Gói' },
  { key: 'devices', label: 'Thiết bị' },
  { key: 'listens', label: 'Lượt nghe' },
  { key: 'is_admin', label: 'Admin', tdClass: 'text-center' },
  { key: 'is_banned', label: 'Khóa', tdClass: 'text-center' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const loading = ref(true)
const loadError = ref(null)
const savingId = ref(null)
const vipSaving = ref(false)
const items = ref([])
const plans = ref([])
const search = ref('')
const filterAdmin = ref('')
const filterPremium = ref('')
const filterBanned = ref('')
const perPage = ref(20)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20, from: 0, to: 0 })
const vipModal = ref(null)
const selectedPlanId = ref('')

const load = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const params = { page, per_page: perPage.value, search: search.value || undefined }
    if (filterAdmin.value !== '') params.is_admin = filterAdmin.value
    if (filterPremium.value !== '') params.is_premium = filterPremium.value
    if (filterBanned.value !== '') params.is_banned = filterBanned.value
    const res = extractApiPayload(await AdminService.getUsers(params))
    items.value = res.items
    pagination.value = res.pagination
  } catch (e) {
    loadError.value = e?.message || 'Không tải được danh sách người dùng'
    items.value = []
  } finally {
    loading.value = false
  }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const updateField = async (item, field, value) => {
  savingId.value = item.id
  try {
    const updated = extractApiPayload(await AdminService.updateUser(item.id, { [field]: value }))
    Object.assign(item, updated)
    toast.success('Đã cập nhật')
  } catch {
    toast.error('Không thể cập nhật')
  } finally {
    savingId.value = null
  }
}

const openVipModal = (row) => {
  vipModal.value = row
  selectedPlanId.value = plans.value[0]?.id || ''
}

const grantVip = async () => {
  if (!vipModal.value || !selectedPlanId.value) return
  vipSaving.value = true
  try {
    const updated = extractApiPayload(await AdminService.grantUserVip(vipModal.value.id, selectedPlanId.value))
    const idx = items.value.findIndex((i) => i.id === vipModal.value.id)
    if (idx >= 0) Object.assign(items.value[idx], updated)
    toast.success('Đã cấp VIP')
    vipModal.value = null
  } catch {
    toast.error('Không thể cấp VIP')
  } finally {
    vipSaving.value = false
  }
}

const revokeVip = async (row) => {
  if (!confirm(`Thu hồi VIP của ${row.username}?`)) return
  try {
    const updated = extractApiPayload(await AdminService.revokeUserVip(row.id))
    Object.assign(row, updated)
    toast.success('Đã thu hồi VIP')
  } catch {
    toast.error('Không thể thu hồi VIP')
  }
}

onMounted(async () => {
  try {
    plans.value = extractApiPayload(await AdminService.getPlans())
  } catch { plans.value = [] }
  await load()
})
</script>

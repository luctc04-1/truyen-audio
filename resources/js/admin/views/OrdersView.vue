<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Gói VIP & đơn hàng</h4>
          <div class="page-title-right d-flex gap-2 align-items-center">
            <button class="btn btn-primary btn-sm" type="button" @click="openCreatePlan"><i class="ri-add-line me-1"></i>Thêm gói</button>
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Đơn hàng</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div v-for="plan in plans" :key="plan.id" class="col-xl-3 col-md-6">
        <div class="card" :class="{ 'border-primary': editingPlan?.id === plan.id }">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h5 class="mb-1">{{ plan.name }}</h5>
                <p class="text-muted mb-2 fs-12">{{ plan.code }}</p>
              </div>
              <span :class="plan.is_active ? 'badge bg-success-subtle text-success' : 'badge bg-secondary-subtle text-secondary'">
                {{ plan.is_active ? 'Đang bán' : 'Tắt' }}
              </span>
            </div>
            <h4 class="text-primary mb-0">{{ formatMoney(plan.price) }}</h4>
            <small class="text-muted">{{ plan.duration_days }} ngày · {{ plan.orders_count }} đơn</small>
            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-soft-primary btn-sm flex-grow-1" type="button" @click="openPlanEdit(plan)"><i class="ri-pencil-line me-1"></i>Sửa</button>
              <button class="btn btn-soft-danger btn-sm" type="button" :disabled="plan.orders_count > 0" @click="removePlan(plan)"><i class="ri-delete-bin-line"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="editingPlan || creatingPlan" class="card mb-3">
      <div class="card-header"><h5 class="card-title mb-0">{{ creatingPlan ? 'Thêm gói VIP' : `Sửa gói: ${editingPlan.name}` }}</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div v-if="creatingPlan" class="col-md-4"><label class="form-label">Mã gói *</label><input v-model="planForm.code" class="form-control" placeholder="vip_1m" /></div>
          <div class="col-md-4"><label class="form-label">Tên gói</label><input v-model="planForm.name" class="form-control" /></div>
          <div class="col-md-4"><label class="form-label">Giá (VNĐ)</label><input v-model.number="planForm.price" type="number" min="0" class="form-control" /></div>
          <div class="col-md-4"><label class="form-label">Thời hạn (ngày)</label><input v-model.number="planForm.duration_days" type="number" min="1" class="form-control" /></div>
          <div class="col-12"><label class="form-label">Mô tả</label><textarea v-model="planForm.description" class="form-control" rows="2"></textarea></div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input id="plan-active" v-model="planForm.is_active" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="plan-active">Đang bán</label>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="button" :disabled="planSaving" @click="savePlan">{{ creatingPlan ? 'Tạo gói' : 'Lưu gói' }}</button>
          <button class="btn btn-light" type="button" @click="closePlanPanel">Hủy</button>
        </div>
      </div>
    </div>

    <AdminDataTable
      title="Danh sách đơn hàng"
      :columns="orderColumns"
      :rows="items"
      :loading="loading"
      :error="loadError"
      :pagination="pagination"
      :per-page="perPage"
      empty-text="Không có đơn hàng"
      empty-icon="ri-shopping-bag-line"
      @page-change="load"
      @update:per-page="onPerPageChange"
      @retry="load()"
    >
      <template #filters>
        <div class="row g-3">
          <div class="col-md-4"><input v-model="search" type="search" class="form-control" placeholder="Mã đơn, user..." @input="debouncedLoad" /></div>
          <div class="col-md-3">
            <select v-model="status" class="form-select" @change="load(1)">
              <option value="">Tất cả trạng thái</option>
              <option value="paid">Đã thanh toán</option>
              <option value="pending">Chờ xử lý</option>
              <option value="cancelled">Đã hủy</option>
              <option value="failed">Thất bại</option>
            </select>
          </div>
        </div>
      </template>

      <template #cell-order_code="{ row }"><strong>{{ row.order_code }}</strong></template>
      <template #cell-user="{ row }">{{ row.user?.username || row.user?.email || '—' }}</template>
      <template #cell-plan="{ row }">{{ row.plan?.name || '—' }}</template>
      <template #cell-amount="{ row }">{{ formatMoney(row.amount) }}</template>
      <template #cell-created_at="{ row }">{{ formatAdminDate(row.created_at) }}</template>
      <template #cell-paid_at="{ row }">{{ formatAdminDate(row.paid_at) }}</template>

      <template #cell-status="{ row }">
        <select
          class="form-select form-select-sm"
          :value="row.status"
          :disabled="savingOrderId === row.id"
          @change="updateOrderStatus(row, $event.target.value)"
        >
          <option value="pending">Chờ xử lý</option>
          <option value="paid">Đã thanh toán</option>
          <option value="cancelled">Đã hủy</option>
          <option value="failed">Thất bại</option>
        </select>
      </template>
    </AdminDataTable>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { debounce, extractApiPayload, formatAdminDate, formatMoney } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const orderColumns = [
  { key: 'order_code', label: 'Mã đơn' },
  { key: 'user', label: 'User' },
  { key: 'plan', label: 'Gói' },
  { key: 'amount', label: 'Số tiền' },
  { key: 'payment_method', label: 'Phương thức' },
  { key: 'status', label: 'Trạng thái' },
  { key: 'created_at', label: 'Tạo lúc' },
  { key: 'paid_at', label: 'Thanh toán' },
]

const toast = useToastStore()
const loading = ref(true)
const loadError = ref(null)
const planSaving = ref(false)
const savingOrderId = ref(null)
const items = ref([])
const plans = ref([])
const search = ref('')
const status = ref('')
const perPage = ref(20)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20, from: 0, to: 0 })
const editingPlan = ref(null)
const creatingPlan = ref(false)
const planForm = reactive({ code: '', name: '', price: 0, duration_days: 30, description: '', is_active: true })

const load = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const res = extractApiPayload(await AdminService.getOrders({ page, per_page: perPage.value, search: search.value || undefined, status: status.value || undefined }))
    items.value = res.items
    pagination.value = res.pagination
  } catch (e) {
    loadError.value = e?.message || 'Không tải được đơn hàng'
    items.value = []
  } finally { loading.value = false }
}

const debouncedLoad = debounce(() => load(1), 300)
const onPerPageChange = (value) => { perPage.value = value; load(1) }

const openCreatePlan = () => {
  creatingPlan.value = true
  editingPlan.value = null
  planForm.code = ''; planForm.name = ''; planForm.price = 0
  planForm.duration_days = 30; planForm.description = ''; planForm.is_active = true
}

const openPlanEdit = (plan) => {
  creatingPlan.value = false
  editingPlan.value = plan
  planForm.code = plan.code; planForm.name = plan.name; planForm.price = plan.price
  planForm.duration_days = plan.duration_days; planForm.description = plan.description || ''; planForm.is_active = plan.is_active
}

const closePlanPanel = () => { editingPlan.value = null; creatingPlan.value = false }

const savePlan = async () => {
  planSaving.value = true
  try {
    if (creatingPlan.value) {
      const created = extractApiPayload(await AdminService.createPlan({ ...planForm }))
      plans.value.push(created)
      toast.success('Đã tạo gói VIP')
    } else if (editingPlan.value) {
      const updated = extractApiPayload(await AdminService.updatePlan(editingPlan.value.id, { ...planForm }))
      const idx = plans.value.findIndex((p) => p.id === editingPlan.value.id)
      if (idx >= 0) plans.value[idx] = updated
      toast.success('Đã cập nhật gói VIP')
    }
    closePlanPanel()
  } catch { toast.error('Không thể lưu gói') }
  finally { planSaving.value = false }
}

const removePlan = async (plan) => {
  if (!confirm(`Xóa gói "${plan.name}"?`)) return
  try {
    await AdminService.deletePlan(plan.id)
    plans.value = plans.value.filter((p) => p.id !== plan.id)
    toast.success('Đã xóa gói')
  } catch { toast.error('Không thể xóa gói (có thể đã có đơn)') }
}

const updateOrderStatus = async (row, newStatus) => {
  if (row.status === newStatus) return
  savingOrderId.value = row.id
  try {
    const updated = extractApiPayload(await AdminService.updateOrder(row.id, { status: newStatus }))
    Object.assign(row, updated)
    toast.success('Đã cập nhật đơn hàng')
  } catch {
    toast.error('Không thể cập nhật đơn')
  } finally {
    savingOrderId.value = null
  }
}

onMounted(async () => {
  try {
    plans.value = extractApiPayload(await AdminService.getPlans())
  } catch { plans.value = [] }
  await load()
})
</script>

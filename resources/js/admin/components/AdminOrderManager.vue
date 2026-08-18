<template>
  <div class="order-manager">
    <!-- VIP Plans Management Header Card -->
    <div class="card-panel plans-panel">
      <div class="panel-head">
        <div>
          <h3>Gói cước VIP đang bán</h3>
          <span class="panel-subtitle">Quản lý giá tiền và thời hạn của các gói VIP</span>
        </div>
        <button class="btn btn-primary btn-sm btn-glow" type="button" @click="openCreatePlanModal">
          <i class="ri-add-line"></i>
          <span>Thêm gói mới</span>
        </button>
      </div>

      <div class="plans-grid">
        <div v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ 'plan-inactive': !plan.is_active }">
          <div class="plan-top-row">
            <div class="plan-crown">
              <i class="ri-vip-crown-fill"></i>
            </div>
            <div class="plan-badge">
              <span v-if="plan.is_active" class="badge badge-active">Đang bán</span>
              <span v-else class="badge badge-inactive">Tạm ngưng</span>
            </div>
          </div>

          <h4 class="plan-name">{{ plan.name }}</h4>
          <div class="plan-price">{{ formatCurrency(plan.price) }}</div>
          <div class="plan-duration">
            <i class="ri-time-line"></i> {{ plan.duration_days }} ngày sử dụng
          </div>
          <p class="plan-desc">{{ plan.description || 'Gói nghe không giới hạn chất lượng cao' }}</p>
          <div class="plan-actions">
            <button class="btn btn-ghost btn-sm" type="button" @click="openEditPlanModal(plan)">
              <i class="ri-edit-line"></i> Sửa giá & thời hạn
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Management Section -->
    <div class="orders-section">
      <div class="manager-toolbar">
        <div class="search-box">
          <i class="ri-search-line"></i>
          <input
            v-model="filters.search"
            type="search"
            placeholder="Tìm mã đơn hàng, email khách hàng..."
            @input="debounceFetch"
          />
          <button v-if="filters.search" class="btn-clear-search" type="button" @click="filters.search = ''; fetchOrders(1)">
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="filter-group">
          <select v-model="filters.status" @change="fetchOrders(1)">
            <option value="all">Tất cả trạng thái</option>
            <option value="paid">Đã thanh toán (Paid)</option>
            <option value="pending">Chờ thanh toán (Pending)</option>
            <option value="cancelled">Đã hủy (Cancelled)</option>
          </select>
        </div>
      </div>

      <div class="card-panel table-panel">
        <!-- Loading Progress Indicator Strip -->
        <div v-if="loading && orders.length" class="table-loading-strip"></div>

        <div class="panel-head">
          <div>
            <h3>Lịch sử đơn hàng VIP</h3>
            <span class="panel-subtitle">Tổng cộng {{ pagination.total || 0 }} đơn hàng</span>
          </div>
          <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchOrders(pagination.current_page)">
            <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
            <span>Làm mới</span>
          </button>
        </div>

        <!-- Skeleton Loading State -->
        <AdminTableSkeleton v-if="loading && !orders.length" :columns="8" :rows="6" />

        <!-- Orders Table -->
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Mã đơn hàng</th>
                <th>Khách hàng</th>
                <th>Gói VIP</th>
                <th>Số tiền</th>
                <th>Phương thức</th>
                <th>Trạng thái</th>
                <th>Thời gian</th>
                <th class="text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td>
                  <strong class="order-code">{{ order.order_code }}</strong>
                </td>
                <td>
                  <div class="customer-info">
                    <strong>{{ order.user?.username || 'Khách vãng lai' }}</strong>
                    <small>{{ order.user?.email || 'N/A' }}</small>
                  </div>
                </td>
                <td>
                  <span class="badge badge-vip">{{ order.plan?.name || 'Gói VIP' }}</span>
                </td>
                <td>
                  <strong class="text-amount">{{ formatCurrency(order.amount) }}</strong>
                </td>
                <td>
                  <span class="badge badge-method">{{ order.payment_method || 'PayOS' }}</span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(order.status)">
                    {{ getStatusText(order.status) }}
                  </span>
                </td>
                <td>
                  <span class="text-muted">{{ formatDate(order.created_at) }}</span>
                </td>
                <td class="text-right">
                  <div class="row-actions">
                    <button
                      v-if="order.status !== 'paid'"
                      class="btn btn-ghost btn-sm"
                      type="button"
                      title="Duyệt đã nhận tiền"
                      @click="updateStatus(order, 'paid')"
                    >
                      <i class="ri-check-line text-success"></i> Duyệt
                    </button>
                    <button
                      v-if="order.status !== 'cancelled'"
                      class="btn-icon btn-icon-danger"
                      type="button"
                      title="Hủy đơn"
                      @click="updateStatus(order, 'cancelled')"
                    >
                      <i class="ri-close-line"></i>
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
            Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} đơn)
          </span>
          <div class="pagination-controls">
            <button
              class="btn btn-ghost btn-sm"
              type="button"
              :disabled="pagination.current_page <= 1 || loading"
              @click="fetchOrders(pagination.current_page - 1)"
            >
              <i class="ri-arrow-left-s-line"></i> Trước
            </button>
            <button
              class="btn btn-ghost btn-sm"
              type="button"
              :disabled="pagination.current_page >= pagination.last_page || loading"
              @click="fetchOrders(pagination.current_page + 1)"
            >
              Sau <i class="ri-arrow-right-s-line"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form: Tạo / Sửa Gói VIP -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="planModal.show" class="admin-modal-overlay" @click.self="planModal.show = false">
          <div class="admin-modal modal-form-dialog modal-sm">
            <div class="modal-header">
              <div class="modal-title-wrap">
                <div class="modal-icon-badge badge-purple">
                  <i :class="planModal.isEdit ? 'ri-edit-2-line' : 'ri-vip-crown-2-line'"></i>
                </div>
                <div>
                  <h3>{{ planModal.isEdit ? 'Chỉnh sửa gói VIP' : 'Tạo gói VIP mới' }}</h3>
                  <span class="modal-subtitle">Thiết lập giá cước, thời hạn và kích hoạt mở bán</span>
                </div>
              </div>
              <button class="btn-close" type="button" title="Đóng modal" @click="planModal.show = false">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <form @submit.prevent="savePlan">
              <div class="modal-body form-body">
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-information-line"></i>
                    <span>Thông tin gói cước</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group">
                      <label>Mã gói (Code) <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-code-line"></i>
                        <input v-model="planForm.code" type="text" required placeholder="VIP_MONTH..." />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Tên gói hiển thị <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-price-tag-3-line"></i>
                        <input v-model="planForm.name" type="text" required placeholder="VIP 1 Tháng..." />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-money-dollar-circle-line"></i>
                    <span>Giá & Thời hạn</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group">
                      <label>Giá tiền (VNĐ) <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-money-dollar-box-line"></i>
                        <input v-model.number="planForm.price" type="number" min="0" required placeholder="99000" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Thời hạn (ngày) <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-calendar-event-line"></i>
                        <input v-model.number="planForm.duration_days" type="number" min="1" required placeholder="30" />
                      </div>
                    </div>

                    <div class="form-group full-width">
                      <label>Mô tả quyền lợi</label>
                      <textarea
                        v-model="planForm.description"
                        rows="2"
                        placeholder="Tiết kiệm 20%, nghe trọn bộ không giới hạn..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="form-section">
                  <div class="toggle-cards-grid">
                    <label class="toggle-card" :class="{ active: planForm.is_active }">
                      <input v-model="planForm.is_active" type="checkbox" class="sr-only" />
                      <div class="toggle-card-icon active-icon"><i class="ri-checkbox-circle-line"></i></div>
                      <div class="toggle-card-body">
                        <strong>Kích hoạt mở bán</strong>
                        <small>Hiển thị gói này cho thành viên mua trên web</small>
                      </div>
                      <div class="switch-pill"></div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-ghost" type="button" @click="planModal.show = false">
                  <span>Hủy bỏ</span>
                </button>
                <button class="btn btn-primary btn-glow" type="submit" :disabled="planModal.submitting">
                  <i v-if="planModal.submitting" class="ri-loader-4-line ri-spin"></i>
                  <i v-else class="ri-save-line"></i>
                  <span>{{ planModal.isEdit ? 'Lưu thay đổi' : 'Tạo gói VIP' }}</span>
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
import { ref, reactive, onMounted } from 'vue'
import AdminService from '@/services/AdminService'
import AdminTableSkeleton from './AdminTableSkeleton.vue'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const toast = useToastStore()

const loading = ref(false)
const orders = ref([])
const plans = ref([])

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
})

const filters = reactive({
  search: '',
  status: 'all',
})

const planModal = reactive({
  show: false,
  isEdit: false,
  editingId: null,
  submitting: false,
})

const planForm = reactive({
  code: '',
  name: '',
  price: 99000,
  duration_days: 30,
  description: '',
  is_active: true,
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchOrders(1)
  }, 300)
}

async function fetchPlans() {
  try {
    const res = await AdminService.getPlans()
    plans.value = extractApiPayload(res) || []
  } catch (err) {
    console.error('Failed to load plans', err)
  }
}

async function fetchOrders(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
    }
    if (filters.search) params.search = filters.search
    if (filters.status !== 'all') params.status = filters.status

    const res = await AdminService.getOrders(params)
    const payload = extractApiPayload(res)

    orders.value = payload.items || []
    if (payload.pagination) {
      pagination.current_page = payload.pagination.current_page
      pagination.last_page = payload.pagination.last_page
      pagination.total = payload.pagination.total
    }
  } catch (err) {
    toast.error('Không thể tải danh sách đơn hàng')
  } finally {
    loading.value = false
  }
}

async function updateStatus(order, newStatus) {
  try {
    await AdminService.updateOrderStatus(order.id, newStatus)
    order.status = newStatus
    toast.success(`Đã cập nhật trạng thái đơn #${order.order_code}`)
  } catch (err) {
    toast.error('Lỗi khi cập nhật trạng thái đơn')
  }
}

function openCreatePlanModal() {
  planModal.isEdit = false
  planModal.editingId = null
  Object.assign(planForm, {
    code: '',
    name: '',
    price: 99000,
    duration_days: 30,
    description: '',
    is_active: true,
  })
  planModal.show = true
}

function openEditPlanModal(plan) {
  planModal.isEdit = true
  planModal.editingId = plan.id
  Object.assign(planForm, {
    code: plan.code,
    name: plan.name,
    price: Number(plan.price),
    duration_days: plan.duration_days,
    description: plan.description || '',
    is_active: Boolean(plan.is_active),
  })
  planModal.show = true
}

async function savePlan() {
  planModal.submitting = true
  try {
    if (planModal.isEdit) {
      await AdminService.updatePlan(planModal.editingId, planForm)
      toast.success('Cập nhật gói VIP thành công!')
    } else {
      await AdminService.createPlan(planForm)
      toast.success('Thêm gói VIP thành công!')
    }
    planModal.show = false
    fetchPlans()
  } catch (err) {
    toast.error(err.message || 'Lỗi khi lưu gói VIP')
  } finally {
    planModal.submitting = false
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'paid':
    case 'completed':
      return 'badge-paid'
    case 'pending':
      return 'badge-pending'
    case 'cancelled':
      return 'badge-cancelled'
    default:
      return 'badge-neutral'
  }
}

function getStatusText(status) {
  switch (status) {
    case 'paid':
    case 'completed':
      return 'Đã thanh toán'
    case 'pending':
      return 'Chờ xử lý'
    case 'cancelled':
      return 'Đã hủy'
    default:
      return status
  }
}

function formatCurrency(amount) {
  return Number(amount || 0).toLocaleString('vi-VN') + 'đ'
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchPlans()
  fetchOrders(1)
})
</script>

<style>
.order-manager {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 18px;
  padding: 24px;
}

.plan-card {
  background: rgba(24, 27, 38, 0.7);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  position: relative;
  transition: all 0.25s var(--ease-spring);
}

.plan-card:hover {
  transform: translateY(-3px);
  border-color: rgba(168, 85, 247, 0.4);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(168, 85, 247, 0.15);
}

.plan-inactive {
  opacity: 0.6;
}

.plan-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.plan-crown {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(251, 191, 36, 0.15);
  color: #fbbf24;
  display: grid;
  place-items: center;
  font-size: 18px;
}

.badge-active {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.badge-inactive {
  background: rgba(148, 163, 184, 0.12);
  color: var(--admin-muted, #94a3b8);
}

.plan-name {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #f8fafc;
}

.plan-price {
  font-size: 24px;
  font-weight: 800;
  color: #fbbf24;
  letter-spacing: -0.02em;
}

.plan-duration {
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.plan-desc {
  margin: 0;
  font-size: 13px;
  color: var(--admin-faint, #64748b);
  flex: 1;
  line-height: 1.4;
}

.plan-actions {
  margin-top: 8px;
}

.manager-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
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
  min-width: 320px;
  flex: 1;
  max-width: 460px;
}

.search-box:focus-within {
  border-color: #a855f7;
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
}

.search-box i { color: var(--admin-muted, #94a3b8); }
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

.filter-group select {
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  color: #f8fafc;
  border-radius: 12px;
  padding: 9px 14px;
  font-size: 13px;
  font-weight: 500;
  outline: none;
  cursor: pointer;
}

.filter-group select:focus {
  border-color: #a855f7;
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

.customer-info { display: flex; flex-direction: column; }
.customer-info strong { color: #f8fafc; font-size: 13px; font-weight: 600; }
.customer-info small { color: var(--admin-muted, #94a3b8); font-size: 12px; }

.badge-cancelled {
  background: rgba(244, 63, 94, 0.15);
  color: #fb7185;
  border: 1px solid rgba(244, 63, 94, 0.25);
}

.badge-method {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
}

.row-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.text-success { color: #34d399; }

.pagination-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-info { font-size: 13px; color: var(--admin-muted, #94a3b8); }
.pagination-controls { display: flex; gap: 8px; }

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
  max-width: 500px;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.9), 0 0 35px rgba(168, 85, 247, 0.12);
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

.modal-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #f8fafc; }

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

.modal-icon-badge.badge-purple {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.25) 0%, rgba(236, 72, 153, 0.25) 100%);
  color: #c084fc;
  border: 1px solid rgba(168, 85, 247, 0.4);
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
  gap: 18px;
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
  font-size: 12.5px;
  font-weight: 700;
  color: #f1f5f9;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.form-section-title i {
  color: #c084fc;
  font-size: 16px;
}

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
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
}

.input-with-icon input {
  padding-left: 40px !important;
}

.input-with-icon:focus-within > i {
  color: #c084fc;
}

.form-group input,
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
.form-group textarea:focus {
  background: rgba(20, 24, 38, 0.95);
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.2), 0 0 16px rgba(168, 85, 247, 0.15);
}

.toggle-cards-grid {
  display: grid;
  grid-template-columns: 1fr;
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
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.45);
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.15);
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

.toggle-card-icon.active-icon { background: rgba(16, 185, 129, 0.15); color: #34d399; }

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
  background: #10b981;
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

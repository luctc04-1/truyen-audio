<template>
  <div class="user-manager">
    <!-- Toolbar -->
    <div class="manager-toolbar">
      <div class="search-box">
        <i class="ri-search-line"></i>
        <input
          v-model="filters.search"
          type="search"
          placeholder="Tìm tên người dùng, email..."
          @input="debounceFetch"
        />
        <button v-if="filters.search" class="btn-clear-search" type="button" @click="filters.search = ''; fetchUsers(1)">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="filter-group">
        <select v-model="filters.is_admin" @change="fetchUsers(1)">
          <option value="">Tất cả vai trò</option>
          <option value="true">Chỉ Quản trị viên (Admin)</option>
          <option value="false">Thành viên (Member)</option>
        </select>
      </div>
    </div>

    <!-- User Table Card -->
    <div class="card-panel table-panel">
      <!-- Loading Progress Indicator Strip -->
      <div v-if="loading && users.length" class="table-loading-strip"></div>

      <div class="panel-head">
        <div>
          <h3>Danh sách tài khoản người dùng</h3>
          <span class="panel-subtitle">Tổng cộng {{ pagination.total || 0 }} tài khoản</span>
        </div>

        <div class="panel-head-actions" style="display: flex; align-items: center; gap: 10px;">
          <!-- Segmented View Switcher -->
          <div class="view-switcher-group">
            <button
              class="view-switch-btn"
              :class="{ active: viewMode === 'table' }"
              type="button"
              title="Xem dạng Bảng"
              @click="viewMode = 'table'"
            >
              <i class="ri-table-line"></i>
              <span>Bảng</span>
            </button>
            <button
              class="view-switch-btn"
              :class="{ active: viewMode === 'grid' }"
              type="button"
              title="Xem dạng Thẻ thành viên"
              @click="viewMode = 'grid'"
            >
              <i class="ri-grid-fill"></i>
              <span>Thẻ</span>
            </button>
          </div>

          <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchUsers(pagination.current_page)">
            <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
            <span>Làm mới</span>
          </button>
        </div>
      </div>

      <!-- Skeleton Loading State -->
      <AdminTableSkeleton v-if="loading && !users.length" :columns="8" :rows="6" />

      <!-- Empty State -->
      <div v-else-if="!users.length" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-user-unfollow-line"></i>
        </div>
        <h4>Không tìm thấy người dùng nào</h4>
        <p>Thử đổi từ khóa tìm kiếm hoặc lọc vai trò khác</p>
      </div>

      <!-- 1. Table Mode -->
      <div v-else-if="viewMode === 'table'" class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Người dùng</th>
              <th>Vai trò</th>
              <th>Gói VIP</th>
              <th>Thiết bị</th>
              <th>Lượt nghe</th>
              <th>Đơn hàng</th>
              <th>Ngày tham gia</th>
              <th class="text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>
                <div class="user-profile">
                  <div class="user-avatar">
                    <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username" />
                    <span v-else>{{ (user.username || user.email || 'U').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="user-details">
                    <strong class="user-name">{{ user.username || 'Chưa đặt tên' }}</strong>
                    <small class="user-email">{{ user.email }}</small>
                  </div>
                </div>
              </td>
              <td>
                <button
                  class="badge-role"
                  :class="user.is_admin ? 'role-admin' : 'role-member'"
                  type="button"
                  title="Bấm để đổi vai trò Admin / Member"
                  @click="toggleAdmin(user)"
                >
                  <i :class="user.is_admin ? 'ri-shield-user-fill' : 'ri-user-3-line'"></i>
                  <span>{{ user.is_admin ? 'Admin' : 'Member' }}</span>
                </button>
              </td>
              <td>
                <div v-if="user.is_vip" class="vip-info">
                  <span class="badge badge-vip">{{ user.vip_plan || 'VIP' }}</span>
                  <small class="vip-expiry">Hết hạn: {{ user.vip_end_at }}</small>
                </div>
                <span v-else class="text-muted">Miễn phí</span>
              </td>
              <td>
                <span class="text-muted"><i class="ri-smartphone-line"></i> {{ user.devices_count || 0 }}</span>
              </td>
              <td>
                <span class="text-muted">{{ user.listening_histories_count || 0 }} tập</span>
              </td>
              <td>
                <span class="text-muted">{{ user.orders_count || 0 }}</span>
              </td>
              <td>
                <span class="text-muted">{{ formatDate(user.created_at) }}</span>
              </td>
              <td class="text-right">
                <button class="btn btn-ghost btn-sm" type="button" @click="openVipModal(user)">
                  <i class="ri-vip-crown-fill" style="color: #fbbf24;"></i>
                  <span>Cấp VIP</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 2. User Cards Studio Mode -->
      <div v-else-if="viewMode === 'grid'" class="user-cards-grid">
        <div v-for="user in users" :key="user.id" class="user-card">
          <div class="user-card-header">
            <div class="user-card-avatar">
              <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username" />
              <span v-else>{{ (user.username || user.email || 'U').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="user-card-info">
              <strong>{{ user.username || 'Chưa đặt tên' }}</strong>
              <small>{{ user.email }}</small>
            </div>
          </div>

          <div class="user-card-stats">
            <div class="user-stat-box">
              <span>Vai trò</span>
              <strong>
                <span class="badge" :class="user.is_admin ? 'badge-vip' : 'badge-neutral'">
                  {{ user.is_admin ? 'Admin' : 'Member' }}
                </span>
              </strong>
            </div>
            <div class="user-stat-box">
              <span>Trạng thái VIP</span>
              <strong>
                <span v-if="user.is_vip" class="badge badge-paid">VIP: {{ user.vip_plan || 'Active' }}</span>
                <span v-else class="text-muted" style="font-size: 11px;">Miễn phí</span>
              </strong>
            </div>
            <div class="user-stat-box">
              <span>Lượt nghe</span>
              <strong>{{ user.listening_histories_count || 0 }} tập</strong>
            </div>
            <div class="user-stat-box">
              <span>Thiết bị</span>
              <strong>{{ user.devices_count || 0 }} thiết bị</strong>
            </div>
          </div>

          <div class="user-card-footer">
            <button
              class="btn btn-ghost btn-sm"
              type="button"
              title="Đổi vai trò"
              @click="toggleAdmin(user)"
            >
              <i :class="user.is_admin ? 'ri-shield-user-line' : 'ri-user-line'"></i>
              <span>{{ user.is_admin ? 'Hạ Member' : 'Nâng Admin' }}</span>
            </button>

            <button class="btn btn-primary btn-sm btn-glow" type="button" @click="openVipModal(user)">
              <i class="ri-vip-crown-fill"></i>
              <span>Cấp VIP</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="pagination-footer">
        <span class="page-info">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} tài khoản)
        </span>
        <div class="pagination-controls">
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page <= 1 || loading"
            @click="fetchUsers(pagination.current_page - 1)"
          >
            <i class="ri-arrow-left-s-line"></i> Trước
          </button>
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="fetchUsers(pagination.current_page + 1)"
          >
            Sau <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Cấp VIP -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="vipModal.show" class="admin-modal-overlay" @click.self="vipModal.show = false">
          <div class="admin-modal modal-sm">
            <div class="modal-header">
              <div class="modal-title-wrap">
                <div class="modal-icon-badge badge-gold">
                  <i class="ri-vip-crown-fill"></i>
                </div>
                <div>
                  <h3>Cấp VIP thành viên</h3>
                  <span class="modal-subtitle">Gia hạn hoặc kích hoạt quyền VIP nghe toàn bộ</span>
                </div>
              </div>
              <button class="btn-close" type="button" title="Đóng modal" @click="vipModal.show = false">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <form @submit.prevent="submitGrantVip">
              <div class="modal-body form-body">
                <div class="user-target-card">
                  <div class="avatar-mini-lg">
                    <img v-if="vipModal.user?.avatar_url" :src="vipModal.user.avatar_url" alt="" />
                    <span v-else>{{ (vipModal.user?.username || vipModal.user?.email || 'U').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div>
                    <strong>{{ vipModal.user?.username || vipModal.user?.name || 'Người dùng' }}</strong>
                    <small>{{ vipModal.user?.email }}</small>
                  </div>
                </div>

                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-calendar-check-line"></i>
                    <span>Chọn gói VIP hệ thống</span>
                  </div>
                  <div class="quick-days-selector">
                    <button
                      v-for="p in plans"
                      :key="p.id"
                      type="button"
                      class="btn-day"
                      :class="{ active: vipModal.plan_id === p.id || (!vipModal.plan_id && vipModal.days === p.duration_days) }"
                      @click="selectPlan(p)"
                    >
                      <i class="ri-vip-crown-line"></i>
                      <div class="plan-btn-text">
                        <strong>{{ p.name }}</strong>
                        <small>{{ p.duration_days }} ngày ({{ formatPrice(p.price) }})</small>
                      </div>
                    </button>
                  </div>

                  <div class="form-group">
                    <label>Hoặc nhập số ngày VIP tùy chỉnh:</label>
                    <div class="input-with-icon">
                      <i class="ri-calendar-event-line"></i>
                      <input v-model.number="vipModal.days" type="number" min="1" required placeholder="Nhập số ngày..." @input="vipModal.plan_id = null" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-ghost" type="button" @click="vipModal.show = false">
                  <span>Hủy bỏ</span>
                </button>
                <button class="btn btn-primary btn-glow" type="submit" :disabled="vipModal.submitting">
                  <i v-if="vipModal.submitting" class="ri-loader-4-line ri-spin"></i>
                  <i v-else class="ri-vip-crown-fill"></i>
                  <span>Xác nhận cấp VIP</span>
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
const viewMode = ref('table')
const users = ref([])

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
})

const filters = reactive({
  search: '',
  is_admin: '',
})

const plans = ref([])

const vipModal = reactive({
  show: false,
  user: null,
  plan_id: null,
  days: 30,
  submitting: false,
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchUsers(1)
  }, 300)
}

async function fetchPlans() {
  try {
    const res = await AdminService.getPlans()
    const payload = extractApiPayload(res)
    plans.value = Array.isArray(payload) ? payload : (payload.items || [])
  } catch (err) {
    console.error('Error fetching plans for VIP modal:', err)
  }
}

function selectPlan(plan) {
  vipModal.plan_id = plan.id
  vipModal.days = plan.duration_days
}

function formatPrice(val) {
  if (!val) return '0đ'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

async function fetchUsers(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
    }
    if (filters.search) params.search = filters.search
    if (filters.is_admin !== '') params.is_admin = filters.is_admin

    const res = await AdminService.getUsers(params)
    const payload = extractApiPayload(res)

    users.value = payload.items || []
    if (payload.pagination) {
      pagination.current_page = payload.pagination.current_page
      pagination.last_page = payload.pagination.last_page
      pagination.total = payload.pagination.total
    }
  } catch (err) {
    toast.error('Không thể tải danh sách người dùng')
  } finally {
    loading.value = false
  }
}

async function toggleAdmin(user) {
  const actionText = user.is_admin ? 'HỦY QUYỀN Admin' : 'CẤP QUYỀN Admin'
  if (!confirm(`Bạn có chắc chắn muốn ${actionText} cho tài khoản ${user.email}?`)) {
    return
  }

  try {
    const res = await AdminService.toggleUserAdmin(user.id)
    const updated = extractApiPayload(res)
    user.is_admin = updated.is_admin
    toast.success(`Đã cập nhật quyền cho ${user.email}`)
  } catch (err) {
    toast.error('Lỗi khi đổi vai trò Admin')
  }
}

function openVipModal(user) {
  vipModal.user = user
  if (plans.value && plans.value.length > 0) {
    const defaultPlan = plans.value[0]
    vipModal.plan_id = defaultPlan.id
    vipModal.days = defaultPlan.duration_days || 30
  } else {
    vipModal.plan_id = null
    vipModal.days = 30
  }
  vipModal.show = true
}

async function submitGrantVip() {
  vipModal.submitting = true
  try {
    const payload = { days: vipModal.days }
    if (vipModal.plan_id) {
      payload.plan_id = vipModal.plan_id
    }
    await AdminService.grantUserVip(vipModal.user.id, payload)
    toast.success(`Đã cấp ${vipModal.days} ngày VIP thành công!`)
    vipModal.show = false
    fetchUsers(pagination.current_page)
  } catch (err) {
    toast.error('Lỗi khi cấp VIP')
  } finally {
    vipModal.submitting = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN')
}

onMounted(() => {
  fetchUsers(1)
  fetchPlans()
})
</script>

<style>
.user-manager {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.manager-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
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

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  color: white;
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 14px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  color: #f8fafc;
  font-size: 14px;
  font-weight: 600;
}

.user-email {
  color: var(--admin-muted, #94a3b8);
  font-size: 12px;
}

.badge-role {
  border: none;
  cursor: pointer;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: transform 0.15s ease;
}

.badge-role:hover { transform: scale(1.06); }

.role-admin {
  background: rgba(244, 63, 94, 0.16);
  color: #fb7185;
  border: 1px solid rgba(244, 63, 94, 0.3);
}

.role-member {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
}

.vip-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.vip-expiry {
  font-size: 11px;
  color: #fbbf24;
  font-weight: 500;
}

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
  max-width: 480px;
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

.modal-body {
  padding: 24px;
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
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

.modal-icon-badge.badge-gold {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(234, 88, 12, 0.25) 100%);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.4);
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

.user-target-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 14px;
}

.avatar-mini-lg {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  display: grid;
  place-items: center;
  font-size: 16px;
  font-weight: 700;
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-mini-lg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-target-card strong {
  display: block;
  font-size: 14px;
  color: #f8fafc;
}

.user-target-card small {
  font-size: 12px;
  color: #94a3b8;
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
  color: #fbbf24;
  font-size: 16px;
}

.quick-days-selector {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.btn-day {
  background: rgba(15, 18, 28, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.09);
  color: #cbd5e1;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-day i {
  font-size: 15px;
  color: #fbbf24;
}

.btn-day:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.2);
}

.btn-day.active {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.5);
  box-shadow: 0 0 16px rgba(245, 158, 11, 0.2);
}

.plan-btn-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
  line-height: 1.3;
}

.plan-btn-text strong {
  font-size: 12.5px;
  color: #f8fafc;
}

.plan-btn-text small {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 400;
}

.btn-day.active .plan-btn-text strong {
  color: #fbbf24;
}

.btn-day.active .plan-btn-text small {
  color: #fde68a;
}

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
  color: #fbbf24;
}

.input-with-icon input {
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

.input-with-icon input:focus {
  background: rgba(20, 24, 38, 0.95);
  border-color: #fbbf24;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2), 0 0 16px rgba(245, 158, 11, 0.15);
}

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

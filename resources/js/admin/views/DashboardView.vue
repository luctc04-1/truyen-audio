<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Tổng quan</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Đang tải...</span>
      </div>
    </div>

    <div v-else-if="error" class="admin-error card">
      <i class="ri-error-warning-line fs-1 text-danger"></i>
      <p class="mb-0">{{ error }}</p>
      <button class="btn btn-primary btn-sm" type="button" @click="load">Thử lại</button>
    </div>

    <template v-else-if="data">
      <div class="row">
        <div v-for="card in metricCards" :key="card.label" class="col-xl-3 col-md-6">
          <div class="card card-animate">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1 overflow-hidden">
                  <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ card.label }}</p>
                </div>
                <div class="flex-shrink-0">
                  <span :class="['avatar-title rounded fs-3', card.bg]">
                    <i :class="[card.icon, card.color]"></i>
                  </span>
                </div>
              </div>
              <div class="mt-4">
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ card.value }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xl-8">
          <div class="card">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">Truyện mới nhất</h4>
              <router-link to="/admin/series" class="btn btn-soft-primary btn-sm">Xem tất cả</router-link>
            </div>
            <div class="card-body">
              <div v-if="!data.recent_series?.length" class="text-muted text-center py-4">Chưa có truyện</div>
              <div v-else class="table-responsive table-card">
                <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Truyện</th>
                      <th>Danh mục</th>
                      <th>Tập</th>
                      <th>Lượt nghe</th>
                      <th>Trạng thái</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="story in data.recent_series" :key="story.id">
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <img :src="story.cover_url" :alt="story.title" class="table-cover" />
                          <div>
                            <h6 class="mb-0">{{ story.title }}</h6>
                            <small class="text-muted">{{ story.author }}</small>
                          </div>
                        </div>
                      </td>
                      <td>{{ story.category || '—' }}</td>
                      <td>{{ story.total_episodes || story.episodes_count || 0 }}</td>
                      <td>{{ formatCompactCount(story.total_listens) }}</td>
                      <td>
                        <span v-if="story.is_premium" class="badge bg-warning-subtle text-warning">VIP</span>
                        <span v-else-if="story.is_hot" class="badge bg-danger-subtle text-danger">Hot</span>
                        <span v-else class="badge bg-success-subtle text-success">Đang phát</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">Đơn hàng gần đây</h4>
              <router-link to="/admin/orders" class="btn btn-soft-primary btn-sm">Xem tất cả</router-link>
            </div>
            <div class="card-body">
              <div v-if="!data.recent_orders?.length" class="text-muted text-center py-4">Chưa có đơn hàng</div>
              <div v-else class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                  <thead class="table-light">
                    <tr><th>Mã</th><th>User</th><th>Số tiền</th><th>Trạng thái</th></tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in data.recent_orders" :key="order.id">
                      <td><strong>{{ order.order_code }}</strong></td>
                      <td>{{ order.user?.username || '—' }}</td>
                      <td>{{ formatMoney(order.amount) }}</td>
                      <td><span class="badge bg-light text-dark">{{ orderStatusLabel(order.status) }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Đơn theo trạng thái</h4></div>
            <div class="card-body">
              <div v-if="!Object.keys(data.orders_by_status || {}).length" class="text-muted text-center py-3">Chưa có đơn</div>
              <div v-for="(count, st) in data.orders_by_status" :key="st" class="d-flex justify-content-between mb-2">
                <span>{{ orderStatusLabel(st) }}</span>
                <span class="badge bg-primary-subtle text-primary">{{ count }}</span>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header align-items-center d-flex">
              <h4 class="card-title mb-0 flex-grow-1">Bình luận mới</h4>
              <router-link to="/admin/comments" class="btn btn-soft-primary btn-sm">Xem tất cả</router-link>
            </div>
            <div class="card-body">
              <div v-if="!data.recent_comments?.length" class="text-muted text-center py-4">Chưa có bình luận</div>
              <div v-for="comment in data.recent_comments" :key="comment.id" class="d-flex mb-3 pb-3 border-bottom">
                <div class="flex-grow-1">
                  <h6 class="mb-1">{{ comment.user?.username || 'Ẩn danh' }}</h6>
                  <p class="text-muted mb-1 fs-13">{{ comment.content }}</p>
                  <small class="text-muted">{{ comment.series?.title }}</small>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h4 class="card-title mb-0">Danh mục phổ biến</h4>
            </div>
            <div class="card-body">
              <div v-if="!data.category_stats?.length" class="text-muted text-center py-3">Chưa có danh mục</div>
              <div v-for="cat in data.category_stats" :key="cat.category" class="d-flex justify-content-between mb-2">
                <span>{{ cat.category || 'Khác' }}</span>
                <span class="badge bg-primary-subtle text-primary">{{ cat.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import AdminService from '@/services/AdminService'
import { useAdminPage } from '@/admin/composables/useAdminPage'
import { formatCompactCount, formatMoney, orderStatusLabel } from '@/utils/helpers'

const { loading, error, run } = useAdminPage()
const data = ref(null)

const metricCards = computed(() => {
  if (!data.value?.metrics) return []
  const m = data.value.metrics
  return [
    { label: 'Tổng truyện', value: formatCompactCount(m.series_count), icon: 'ri-book-2-line', bg: 'bg-primary-subtle', color: 'text-primary' },
    { label: 'Tập audio', value: formatCompactCount(m.episodes_count), icon: 'ri-headphone-line', bg: 'bg-success-subtle', color: 'text-success' },
    { label: 'Người dùng', value: formatCompactCount(m.users_count), icon: 'ri-group-line', bg: 'bg-warning-subtle', color: 'text-warning' },
    { label: 'Đơn hàng', value: formatCompactCount(m.orders_count), icon: 'ri-shopping-bag-line', bg: 'bg-secondary-subtle', color: 'text-secondary' },
    { label: 'Bình luận', value: formatCompactCount(m.comments_count), icon: 'ri-chat-3-line', bg: 'bg-info-subtle', color: 'text-info' },
    { label: 'Doanh thu VIP', value: formatMoney(m.paid_revenue), icon: 'ri-bank-card-line', bg: 'bg-danger-subtle', color: 'text-danger' },
  ]
})

const load = async () => {
  const result = await run(() => AdminService.getDashboard(), {
    errorMessage: 'Không tải được dashboard. Kiểm tra đăng nhập admin và kết nối API.',
  })
  if (result) data.value = result
}

onMounted(load)
</script>

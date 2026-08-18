<template>
  <div class="admin-shell">
    <!-- Fixed Sidebar -->
    <AdminSidebar
      :active-section="activeSection"
      :menu-groups="menuGroups"
      :open="sidebarOpen"
      @change-section="onSectionChange"
      @close-sidebar="sidebarOpen = false"
    />

    <!-- Main Admin Body (Scrollable with margin-left) -->
    <main class="admin-main">
      <!-- Topbar -->
      <AdminTopbar
        :current-title="currentTitle"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
        @quick-add-series="openAddSeries"
      />

      <!-- Content Area with Smooth Tab Transitions -->
      <div class="admin-content">
        <transition name="fade-slide" mode="out-in">
          <!-- 1. DASHBOARD OVERVIEW -->
          <div v-if="activeSection === 'dashboard'" key="dashboard" class="dashboard-view">
            <!-- Welcome Hero Banner -->
            <div class="welcome-hero">
              <div>
                <div class="welcome-badge">
                  <span class="live-pulse"></span>
                  <span>Hệ thống: Trực tuyến & Sẵn sàng</span>
                </div>
                <h2 class="welcome-title">Trung Tâm Điều Hành Truyện Audio 🎧</h2>
                <p class="welcome-sub">
                  Tổng quan thời gian thực về kho truyện, doanh số gói VIP và tương tác người dùng trên toàn hệ thống.
                </p>
              </div>

              <div class="welcome-actions">
                <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'series'">
                  <i class="ri-book-open-line"></i>
                  <span>Kho truyện</span>
                </button>
                <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'sync'">
                  <i class="ri-cloud-line"></i>
                  <span>Đồng bộ dữ liệu</span>
                </button>
              </div>
            </div>

            <!-- KPI Stats Cards -->
            <div class="stats-grid">
              <AdminStatsCard
                label="Tổng số truyện"
                :value="dashboardData.metrics?.total_series || 0"
                icon="ri-book-3-fill"
                tone="purple"
                :trend="12"
                subtext="Đang lưu trong kho truyện"
              />
              <AdminStatsCard
                label="Tập audio phát hành"
                :value="dashboardData.metrics?.total_episodes || 0"
                icon="ri-headphone-fill"
                tone="emerald"
                :trend="8"
                subtext="Sẵn sàng phát trực tuyến"
              />
              <AdminStatsCard
                label="Thành viên đăng ký"
                :value="dashboardData.metrics?.total_users || 0"
                icon="ri-group-fill"
                tone="blue"
                :trend="15"
                subtext="Tài khoản người dùng"
              />
              <AdminStatsCard
                label="Doanh thu VIP"
                :value="formatCurrency(dashboardData.metrics?.total_revenue || 0)"
                icon="ri-vip-crown-fill"
                tone="amber"
                :trend="24"
                subtext="Tổng nạp các gói VIP"
              />
            </div>

            <!-- Visual Analytics Grid: Plan Revenue & Category Distribution -->
            <div class="analytics-grid">
              <!-- VIP Plans Revenue Breakdown -->
              <div class="card-panel">
                <div class="panel-head">
                  <div>
                    <h3>Doanh thu theo Gói VIP</h3>
                    <span class="panel-subtitle">Tỉ trọng bán hàng các gói cước</span>
                  </div>
                  <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'orders'">
                    Quản lý gói <i class="ri-arrow-right-s-line"></i>
                  </button>
                </div>

                <div class="metric-bar-list">
                  <div v-for="plan in dashboardData.plan_stats" :key="plan.id" class="metric-bar-item">
                    <div class="metric-bar-header">
                      <strong>{{ plan.name }} ({{ plan.orders_count }} đơn)</strong>
                      <span>{{ formatCurrency(plan.revenue) }} ({{ plan.percentage || 0 }}%)</span>
                    </div>
                    <div class="progress-track">
                      <div
                        class="progress-fill progress-purple"
                        :style="{ width: `${Math.max(plan.percentage || 0, 5)}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Categories Distribution -->
              <div class="card-panel">
                <div class="panel-head">
                  <div>
                    <h3>Phân bố Thể loại Truyện</h3>
                    <span class="panel-subtitle">Top các danh mục nhiều truyện nhất</span>
                  </div>
                  <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'series'">
                    Xem kho truyện <i class="ri-arrow-right-s-line"></i>
                  </button>
                </div>

                <div class="metric-bar-list">
                  <div v-for="cat in dashboardData.category_stats" :key="cat.category" class="metric-bar-item">
                    <div class="metric-bar-header">
                      <strong>{{ cat.category }}</strong>
                      <span>{{ cat.count }} bộ ({{ cat.percentage }}%)</span>
                    </div>
                    <div class="progress-track">
                      <div
                        class="progress-fill progress-emerald"
                        :style="{ width: `${Math.max(cat.percentage, 8)}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Series Rankings & Recent Orders Grid -->
            <div class="dashboard-grid">
              <!-- Top 5 Most Listened Series Table -->
              <div class="card-panel">
                <div class="panel-head">
                  <div>
                    <h3>Bảng Xếp Hạng Truyện Nghe Nhiều Nhất</h3>
                    <span class="panel-subtitle">Top 5 truyện audio được quan tâm nhất</span>
                  </div>
                  <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'series'">
                    Xem tất cả <i class="ri-arrow-right-s-line"></i>
                  </button>
                </div>

                <div class="table-responsive">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th width="50">Hạng</th>
                        <th>Bìa & Tên truyện</th>
                        <th>Thể loại</th>
                        <th>Tập</th>
                        <th>Lượt nghe</th>
                        <th>Đánh giá</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(story, idx) in dashboardData.top_series" :key="story.id">
                        <td>
                          <div class="rank-badge" :class="idx < 3 ? `rank-${idx + 1}` : 'rank-other'">
                            {{ idx + 1 }}
                          </div>
                        </td>
                        <td>
                          <div class="story-cell">
                            <img :src="story.cover_url || '/android-chrome-512x512.png'" :alt="story.title" />
                            <div>
                              <strong>{{ story.title }}</strong>
                              <small>{{ story.author || 'Đang cập nhật' }}</small>
                            </div>
                          </div>
                        </td>
                        <td><span class="badge badge-neutral">{{ story.category }}</span></td>
                        <td><strong>{{ story.total_episodes || 0 }}</strong> tập</td>
                        <td><span class="text-muted">{{ formatNumber(story.total_listens || story.listen_count || 0) }}</span></td>
                        <td>
                          <span class="rating-text"><i class="ri-star-fill"></i> {{ Number(story.average_rating || 5).toFixed(1) }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Recent 5 Orders Table -->
              <div class="card-panel">
                <div class="panel-head">
                  <div>
                    <h3>Đơn Hàng VIP Gần Đây</h3>
                    <span class="panel-subtitle">5 giao dịch thanh toán mới nhất</span>
                  </div>
                  <button class="btn btn-ghost btn-sm" type="button" @click="activeSection = 'orders'">
                    Xem đơn <i class="ri-arrow-right-s-line"></i>
                  </button>
                </div>

                <div class="table-responsive">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>Mã đơn</th>
                        <th>Khách</th>
                        <th>Gói</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="order in dashboardData.recent_orders" :key="order.id">
                        <td><strong class="order-code">{{ order.order_code }}</strong></td>
                        <td>
                          <div class="customer-info">
                            <strong>{{ order.user?.username || 'Khách' }}</strong>
                            <small>{{ order.user?.email }}</small>
                          </div>
                        </td>
                        <td><span class="badge badge-vip">{{ order.plan?.name || 'VIP' }}</span></td>
                        <td><strong class="text-amount">{{ formatCurrency(order.amount) }}</strong></td>
                        <td>
                          <span class="badge" :class="order.status === 'paid' ? 'badge-paid' : 'badge-pending'">
                            {{ order.status === 'paid' ? 'Đã thu' : 'Chờ' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Quick Shortcuts Bar -->
            <div class="quick-actions-bar">
              <div class="quick-action-card" @click="activeSection = 'series'">
                <div class="qa-icon purple"><i class="ri-book-2-line"></i></div>
                <div>
                  <strong>Quản lý kho truyện</strong>
                  <p>Thêm mới, sửa ảnh bìa, danh mục và tác giả</p>
                </div>
              </div>

              <div class="quick-action-card" @click="activeSection = 'episodes'">
                <div class="qa-icon emerald"><i class="ri-play-circle-line"></i></div>
                <div>
                  <strong>Upload & Audio Studio</strong>
                  <p>Thêm tập, phát nghe thử file audio trực tuyến</p>
                </div>
              </div>

              <div class="quick-action-card" @click="activeSection = 'hot-order'">
                <div class="qa-icon amber"><i class="ri-fire-fill"></i></div>
                <div>
                  <strong>Sắp xếp Truyện Hot</strong>
                  <p>Kéo thả & ưu tiên hiển thị truyện hot trang chủ</p>
                </div>
              </div>

              <div class="quick-action-card" @click="activeSection = 'sync'">
                <div class="qa-icon blue"><i class="ri-refresh-line"></i></div>
                <div>
                  <strong>Đồng bộ Supabase & Crawler</strong>
                  <p>Cập nhật truyện tự động từ nguồn bên ngoài</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. SERIES MANAGEMENT -->
          <div v-else-if="activeSection === 'series'" key="series">
            <AdminSeriesManager ref="seriesManagerRef" @manage-episodes="handleManageEpisodes" />
          </div>

          <!-- 2.1 HOT ORDER MANAGEMENT -->
          <div v-else-if="activeSection === 'hot-order'" key="hot-order">
            <AdminHotOrderManager />
          </div>

          <!-- 3. EPISODES MANAGEMENT -->
          <div v-else-if="activeSection === 'episodes'" key="episodes">
            <AdminEpisodeManager :initial-series-id="jumpSeriesId" />
          </div>

          <!-- 4. USERS MANAGEMENT -->
          <div v-else-if="activeSection === 'users'" key="users">
            <AdminUserManager />
          </div>

          <!-- 5. ORDERS & VIP PLANS -->
          <div v-else-if="activeSection === 'orders'" key="orders">
            <AdminOrderManager />
          </div>

          <!-- 6. SYNC & CRAWLER -->
          <div v-else-if="activeSection === 'sync'" key="sync">
            <AdminSyncManager />
          </div>

          <!-- 7. COMMUNITY & COMMENTS MODERATION -->
          <div v-else-if="activeSection === 'comments' || activeSection === 'community'" key="community">
            <AdminCommunityManager />
          </div>

          <!-- 8. SYSTEM SETTINGS -->
          <div v-else-if="activeSection === 'settings'" key="settings">
            <AdminSettingManager />
          </div>
        </transition>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminTopbar from '@/admin/components/AdminTopbar.vue'
import AdminStatsCard from '@/admin/components/AdminStatsCard.vue'
import AdminSeriesManager from '@/admin/components/AdminSeriesManager.vue'
import AdminHotOrderManager from '@/admin/components/AdminHotOrderManager.vue'
import AdminEpisodeManager from '@/admin/components/AdminEpisodeManager.vue'
import AdminUserManager from '@/admin/components/AdminUserManager.vue'
import AdminOrderManager from '@/admin/components/AdminOrderManager.vue'
import AdminSyncManager from '@/admin/components/AdminSyncManager.vue'
import AdminCommunityManager from '@/admin/components/AdminCommunityManager.vue'
import AdminSettingManager from '@/admin/components/AdminSettingManager.vue'
import AdminService from '@/services/AdminService'
import { extractApiPayload } from '@/utils/helpers'

const sidebarOpen = ref(false)
const activeSection = ref('dashboard')
const jumpSeriesId = ref('')
const seriesManagerRef = ref(null)

const menuGroups = [
  {
    title: 'Tổng quan',
    items: [
      { id: 'dashboard', label: 'Bảng điều khiển', icon: 'ri-dashboard-2-line' },
    ],
  },
  {
    title: 'Nội Dung & Studio',
    items: [
      { id: 'series', label: 'Kho truyện audio', icon: 'ri-book-open-line' },
      { id: 'hot-order', label: 'Sắp xếp Truyện Hot', icon: 'ri-fire-fill', isHot: true },
      { id: 'episodes', label: 'Tập truyện & Audio', icon: 'ri-play-circle-line' },
    ],
  },
  {
    title: 'Kinh Doanh & Thành Viên',
    items: [
      { id: 'orders', label: 'Đơn hàng & Gói cước', icon: 'ri-vip-crown-line' },
      { id: 'users', label: 'Người dùng & VIP', icon: 'ri-user-3-line' },
      { id: 'comments', label: 'Kiểm duyệt & Thảo luận', icon: 'ri-discuss-line' },
    ],
  },
  {
    title: 'Hệ Thống & Vận Hành',
    items: [
      { id: 'sync', label: 'Đồng bộ Supabase & Crawl', icon: 'ri-cloud-line' },
      { id: 'settings', label: 'Cài đặt hệ thống', icon: 'ri-settings-3-line' },
    ],
  },
]

const currentTitle = computed(() => {
  for (const group of menuGroups) {
    const item = group.items.find((m) => m.id === activeSection.value)
    if (item) return item.label
  }
  return 'Quản trị'
})

const dashboardData = reactive({
  metrics: {
    total_series: 0,
    total_episodes: 0,
    total_users: 0,
    total_comments: 0,
    total_revenue: 0,
  },
  top_series: [],
  recent_orders: [],
  recent_comments: [],
  plan_stats: [],
  category_stats: [],
})

function onSectionChange(sectionId) {
  activeSection.value = sectionId
  sidebarOpen.value = false
  if (sectionId === 'dashboard') {
    fetchDashboardData()
  }
}

function handleManageEpisodes(seriesId) {
  jumpSeriesId.value = seriesId
  activeSection.value = 'episodes'
}

function openAddSeries() {
  activeSection.value = 'series'
  setTimeout(() => {
    if (seriesManagerRef.value?.openCreateModal) {
      seriesManagerRef.value.openCreateModal()
    }
  }, 100)
}

async function fetchDashboardData() {
  try {
    const res = await AdminService.getDashboardStats()
    const payload = extractApiPayload(res)
    if (payload) {
      Object.assign(dashboardData, payload)
    }
  } catch (err) {
    console.error('Failed to load dashboard data', err)
  }
}

function formatCurrency(amount) {
  return Number(amount || 0).toLocaleString('vi-VN') + 'đ'
}

function formatNumber(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style>
@import '../../css/admin/admin.css';
</style>

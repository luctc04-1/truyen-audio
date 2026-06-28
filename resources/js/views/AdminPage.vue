<template>
  <div class="admin-shell">
    <AdminSidebar
      :active-section="activeSection"
      :menu-items="menuItems"
      :open="sidebarOpen"
      @change-section="activeSection = $event"
    />

    <section class="admin-main">
      <AdminTopbar v-model:search="search" @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <div class="admin-content">
        <div class="page-heading">
          <div>
            <p>Trang quản trị</p>
            <h1>{{ currentTitle }}</h1>
          </div>
          <div class="heading-actions">
            <button class="ghost-btn" type="button">
              <i class="ri-upload-cloud-2-line"></i>
              Import audio
            </button>
            <button class="ghost-btn" type="button">
              <i class="ri-refresh-line"></i>
              Đồng bộ crawl
            </button>
          </div>
        </div>

        <section v-if="activeSection === 'dashboard'" class="metrics-grid">
          <article v-for="metric in metrics" :key="metric.label" class="metric-card">
            <div class="metric-icon" :class="metric.tone">
              <i :class="metric.icon"></i>
            </div>
            <div>
              <p>{{ metric.label }}</p>
              <h2>{{ metric.value }}</h2>
              <span :class="metric.trend > 0 ? 'up' : 'down'">
                <i :class="metric.trend > 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line'"></i>
                {{ Math.abs(metric.trend) }}% tuần này
              </span>
            </div>
          </article>
        </section>

        <section v-if="activeSection === 'dashboard'" class="workbench">
          <article class="panel panel-wide">
            <div class="panel-header">
              <div>
                <p>Nội dung</p>
                <h2>Danh sách truyện nổi bật</h2>
              </div>
              <div class="segmented">
                <button
                  v-for="filter in statusFilters"
                  :key="filter"
                  :class="{ active: activeFilter === filter }"
                  type="button"
                  @click="activeFilter = filter"
                >
                  {{ filter }}
                </button>
              </div>
            </div>

            <div class="table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Truyện</th>
                    <th>Danh mục</th>
                    <th>Tập</th>
                    <th>Nghe</th>
                    <th>Trạng thái</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="story in filteredStories" :key="story.title">
                    <td>
                      <div class="story-cell">
                        <img :src="story.cover" :alt="story.title" />
                        <div>
                          <strong>{{ story.title }}</strong>
                          <small>{{ story.author }} - {{ story.narrator }}</small>
                        </div>
                      </div>
                    </td>
                    <td>{{ story.category }}</td>
                    <td>{{ story.episodes }}</td>
                    <td>{{ story.listens }}</td>
                    <td>
                      <span class="status-pill" :class="story.statusClass">{{ story.status }}</span>
                    </td>
                    <td>
                      <button class="icon-btn table-action" type="button" title="Chỉnh sửa">
                        <i class="ri-pencil-line"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <article class="panel">
            <div class="panel-header compact">
              <div>
                <p>Kiểm duyệt</p>
                <h2>Bình luận mới</h2>
              </div>
              <span class="soft-count">8 chờ duyệt</span>
            </div>

            <div class="comment-list">
              <div v-for="comment in comments" :key="comment.user" class="comment-item">
                <div>
                  <strong>{{ comment.user }}</strong>
                  <p>{{ comment.text }}</p>
                  <small>{{ comment.story }}</small>
                </div>
                <div class="comment-actions">
                  <button class="accept" type="button" title="Duyệt">
                    <i class="ri-check-line"></i>
                  </button>
                  <button class="reject" type="button" title="Ẩn">
                    <i class="ri-close-line"></i>
                  </button>
                </div>
              </div>
            </div>
          </article>
        </section>

        <section v-if="activeSection === 'dashboard'" class="bottom-grid">
          <article class="panel">
            <div class="panel-header compact">
              <div>
                <p>Audio</p>
                <h2>Upload jobs</h2>
              </div>
            </div>
            <div class="job-list">
              <div v-for="job in uploadJobs" :key="job.name" class="job-item">
                <div class="job-top">
                  <strong>{{ job.name }}</strong>
                  <span>{{ job.progress }}%</span>
                </div>
                <div class="progress-line">
                  <span :style="{ width: `${job.progress}%` }"></span>
                </div>
                <small>{{ job.status }}</small>
              </div>
            </div>
          </article>

          <article class="panel">
            <div class="panel-header compact">
              <div>
                <p>Doanh thu</p>
                <h2>Gói VIP & đơn hàng</h2>
              </div>
            </div>
            <div class="plan-list">
              <div v-for="plan in plans" :key="plan.name" class="plan-row">
                <span>{{ plan.name }}</span>
                <strong>{{ plan.revenue }}</strong>
                <small>{{ plan.orders }} đơn</small>
              </div>
            </div>
          </article>

          <article class="panel quick-form">
            <div class="panel-header compact">
              <div>
                <p>Thao tác nhanh</p>
                <h2>Tạo tập audio</h2>
              </div>
            </div>
            <label>
              Truyện
              <select>
                <option>Thần Mộ</option>
                <option>Đấu Phá Thương Khung</option>
                <option>Ma Thổi Đèn</option>
              </select>
            </label>
            <label>
              Tên tập
              <input type="text" value="Chương 128: Khởi hành" />
            </label>
            <label class="toggle-row">
              <span>Yêu cầu VIP</span>
              <input type="checkbox" checked />
            </label>
            <button class="primary-btn full" type="button">
              <i class="ri-save-3-line"></i>
              Lưu nháp
            </button>
          </article>
        </section>

        <section v-if="activeSection === 'series'" class="module-grid">
          <article class="panel panel-wide">
            <div class="panel-header">
              <div>
                <p>Kho truyện</p>
                <h2>Quản lý truyện audio</h2>
              </div>
              <button class="primary-btn" type="button"><i class="ri-add-line"></i>Thêm truyện</button>
            </div>
            <div class="table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Truyện</th>
                    <th>Nguồn</th>
                    <th>Premium</th>
                    <th>Hot</th>
                    <th>SEO</th>
                    <th>Publish</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="story in stories" :key="story.title">
                    <td>
                      <div class="story-cell">
                        <img :src="story.cover" :alt="story.title" />
                        <div>
                          <strong>{{ story.title }}</strong>
                          <small>{{ story.author }} - {{ story.episodes }} tập</small>
                        </div>
                      </div>
                    </td>
                    <td>{{ story.source }}</td>
                    <td><span class="status-pill" :class="story.statusClass">{{ story.status }}</span></td>
                    <td><input type="checkbox" :checked="story.hot" /></td>
                    <td>{{ story.seo }}</td>
                    <td>{{ story.published }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <article class="panel editor-panel">
            <div class="panel-header compact">
              <div>
                <p>Form mẫu</p>
                <h2>Thông tin truyện</h2>
              </div>
            </div>
            <label>Tên truyện<input type="text" value="Thần Mộ" /></label>
            <label>Slug<input type="text" value="than-mo" /></label>
            <label>Danh mục<select><option>Tiên hiệp</option><option>Trinh thám</option></select></label>
            <label>Tác giả<input type="text" value="Thần Đông" /></label>
            <label>Người đọc<input type="text" value="MC Huyền Vũ" /></label>
            <label>Mô tả<textarea rows="4">Mô tả ngắn hiển thị ở trang chi tiết truyện.</textarea></label>
            <button class="primary-btn full" type="button"><i class="ri-save-3-line"></i>Lưu truyện</button>
          </article>
        </section>

        <section v-if="activeSection === 'episodes'" class="module-grid">
          <article class="panel panel-wide">
            <div class="panel-header">
              <div>
                <p>Audio</p>
                <h2>Danh sách tập truyện</h2>
              </div>
              <button class="primary-btn" type="button"><i class="ri-upload-cloud-line"></i>Upload tập</button>
            </div>
            <div class="table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Tập</th>
                    <th>Truyện</th>
                    <th>Thời lượng</th>
                    <th>Nghe</th>
                    <th>Audio</th>
                    <th>Lịch đăng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="episode in episodes" :key="episode.title">
                    <td><strong>{{ episode.title }}</strong><small class="block-muted">#{{ episode.number }}</small></td>
                    <td>{{ episode.series }}</td>
                    <td>{{ episode.duration }}</td>
                    <td>{{ episode.plays }}</td>
                    <td><span class="status-pill" :class="episode.audioClass">{{ episode.audio }}</span></td>
                    <td>{{ episode.publishAt }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <article class="panel editor-panel">
            <div class="panel-header compact">
              <div>
                <p>Biên tập</p>
                <h2>Tạo tập audio</h2>
              </div>
            </div>
            <label>Truyện<select><option>Thần Mộ</option><option>Ma Thổi Đèn</option></select></label>
            <label>Số tập<input type="number" value="682" /></label>
            <label>Tiêu đề<input type="text" value="Chương 682: Cửa trời mở" /></label>
            <label>File audio<div class="dropzone"><i class="ri-file-music-line"></i><span>Kéo file MP3 vào đây</span></div></label>
            <label>Transcript<textarea rows="5">Nội dung transcript hoặc ghi chú tập...</textarea></label>
            <label class="toggle-row"><span>Tập VIP</span><input type="checkbox" /></label>
            <button class="primary-btn full" type="button"><i class="ri-save-3-line"></i>Lưu tập</button>
          </article>
        </section>

        <section v-if="activeSection === 'categories'" class="bottom-grid">
          <article class="panel">
            <div class="panel-header compact"><div><p>Phân loại</p><h2>Danh mục</h2></div></div>
            <div class="simple-list">
              <div v-for="category in categories" :key="category.name">
                <span>{{ category.name }}</span>
                <small>{{ category.count }} truyện</small>
                <button class="icon-btn table-action" type="button"><i class="ri-pencil-line"></i></button>
              </div>
            </div>
          </article>
          <article class="panel">
            <div class="panel-header compact"><div><p>Gắn nhãn</p><h2>Tags phổ biến</h2></div></div>
            <div class="tag-cloud">
              <span v-for="tag in tags" :key="tag">#{{ tag }}</span>
            </div>
          </article>
          <article class="panel editor-panel">
            <div class="panel-header compact"><div><p>Form mẫu</p><h2>Thêm danh mục/tag</h2></div></div>
            <label>Tên<input type="text" value="Kiếm hiệp" /></label>
            <label>Slug<input type="text" value="kiem-hiep" /></label>
            <label>Loại<select><option>Danh mục</option><option>Tag</option></select></label>
            <button class="primary-btn full" type="button"><i class="ri-add-line"></i>Thêm mới</button>
          </article>
        </section>

        <section v-if="activeSection === 'users'" class="module-grid">
          <article class="panel panel-wide">
            <div class="panel-header"><div><p>Tài khoản</p><h2>Người dùng</h2></div></div>
            <div class="table-wrap">
              <table class="admin-table">
                <thead><tr><th>User</th><th>Gói</th><th>Thiết bị</th><th>Lượt nghe</th><th>Đăng nhập cuối</th><th>Quyền</th></tr></thead>
                <tbody>
                  <tr v-for="user in users" :key="user.email">
                    <td><strong>{{ user.name }}</strong><small class="block-muted">{{ user.email }}</small></td>
                    <td>{{ user.plan }}</td>
                    <td>{{ user.devices }}</td>
                    <td>{{ user.listens }}</td>
                    <td>{{ user.lastLogin }}</td>
                    <td><span class="status-pill" :class="user.admin ? 'premium' : 'draft'">{{ user.admin ? 'Admin' : 'Member' }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
          <article class="panel">
            <div class="panel-header compact"><div><p>Hồ sơ</p><h2>Chi tiết user</h2></div></div>
            <div class="profile-card">
              <img src="/theme/admin/assets/images/users/avatar-2.jpg" alt="User" />
              <strong>Minh Anh</strong>
              <small>VIP tháng còn 18 ngày</small>
            </div>
            <div class="stat-list">
              <div><span>Yêu thích</span><strong>42 truyện</strong></div>
              <div><span>Playlist</span><strong>6 danh sách</strong></div>
              <div><span>Bình luận</span><strong>128</strong></div>
            </div>
          </article>
        </section>

        <section v-if="activeSection === 'orders'" class="module-grid">
          <article class="panel panel-wide">
            <div class="panel-header"><div><p>Thanh toán</p><h2>Đơn hàng VIP</h2></div></div>
            <div class="table-wrap">
              <table class="admin-table">
                <thead><tr><th>Mã đơn</th><th>User</th><th>Gói</th><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th></tr></thead>
                <tbody>
                  <tr v-for="order in orders" :key="order.code">
                    <td><strong>{{ order.code }}</strong></td>
                    <td>{{ order.user }}</td>
                    <td>{{ order.plan }}</td>
                    <td>{{ order.amount }}</td>
                    <td>{{ order.method }}</td>
                    <td><span class="status-pill" :class="order.statusClass">{{ order.status }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
          <article class="panel editor-panel">
            <div class="panel-header compact"><div><p>Gói cước</p><h2>Plan VIP</h2></div></div>
            <label>Mã gói<input type="text" value="VIP_MONTH" /></label>
            <label>Tên gói<input type="text" value="VIP tháng" /></label>
            <label>Giá<input type="text" value="99000" /></label>
            <label>Thời hạn ngày<input type="number" value="30" /></label>
            <label class="toggle-row"><span>Đang bán</span><input type="checkbox" checked /></label>
            <button class="primary-btn full" type="button"><i class="ri-save-3-line"></i>Lưu gói</button>
          </article>
        </section>

        <section v-if="activeSection === 'jobs'" class="bottom-grid">
          <article class="panel panel-wide">
            <div class="panel-header"><div><p>Crawler</p><h2>Jobs lấy truyện</h2></div><button class="primary-btn" type="button"><i class="ri-play-line"></i>Chạy crawl</button></div>
            <div class="timeline-list">
              <div v-for="job in crawlJobs" :key="job.source">
                <span :class="['timeline-dot', job.statusClass]"></span>
                <div><strong>{{ job.source }}</strong><p>{{ job.detail }}</p><small>{{ job.time }}</small></div>
              </div>
            </div>
          </article>
          <article class="panel panel-wide">
            <div class="panel-header compact"><div><p>Audio</p><h2>Upload jobs</h2></div></div>
            <div class="job-list">
              <div v-for="job in uploadJobs" :key="job.name" class="job-item">
                <div class="job-top"><strong>{{ job.name }}</strong><span>{{ job.progress }}%</span></div>
                <div class="progress-line"><span :style="{ width: `${job.progress}%` }"></span></div>
                <small>{{ job.status }}</small>
              </div>
            </div>
          </article>
        </section>

        <section v-if="activeSection === 'comments'" class="module-grid">
          <article class="panel panel-wide">
            <div class="panel-header"><div><p>Cộng đồng</p><h2>Bình luận & đánh giá</h2></div></div>
            <div class="comment-list large">
              <div v-for="comment in comments" :key="comment.user" class="comment-item">
                <div><strong>{{ comment.user }}</strong><p>{{ comment.text }}</p><small>{{ comment.story }}</small></div>
                <div class="comment-actions"><button class="accept" type="button"><i class="ri-check-line"></i></button><button class="reject" type="button"><i class="ri-delete-bin-line"></i></button></div>
              </div>
            </div>
          </article>
          <article class="panel">
            <div class="panel-header compact"><div><p>Rating</p><h2>Tổng quan đánh giá</h2></div></div>
            <div class="rating-box"><strong>4.7</strong><span>★★★★★</span><small>12,840 lượt đánh giá</small></div>
            <div class="stat-list">
              <div><span>5 sao</span><strong>72%</strong></div>
              <div><span>4 sao</span><strong>18%</strong></div>
              <div><span>Cần xem lại</span><strong>46</strong></div>
            </div>
          </article>
        </section>

        <section v-if="activeSection === 'settings'" class="module-grid">
          <article class="panel editor-panel">
            <div class="panel-header compact"><div><p>Hệ thống</p><h2>Cài đặt website</h2></div></div>
            <label>Tên website<input type="text" value="Truyen Audio" /></label>
            <label>Domain chính<input type="text" value="https://truyenaudio.local" /></label>
            <label>Storage audio<select><option>Local storage</option><option>S3 compatible</option></select></label>
            <label>Số tập free trước VIP<input type="number" value="10" /></label>
            <label class="toggle-row"><span>Bật đăng ký mới</span><input type="checkbox" checked /></label>
            <button class="primary-btn full" type="button"><i class="ri-save-3-line"></i>Lưu cài đặt</button>
          </article>
          <article class="panel editor-panel">
            <div class="panel-header compact"><div><p>SEO</p><h2>Meta mặc định</h2></div></div>
            <label>Meta title<input type="text" value="Truyện Audio - Nghe truyện Việt Nam" /></label>
            <label>Meta description<textarea rows="4">Kho truyện audio chọn lọc, nghe mọi lúc mọi nơi.</textarea></label>
            <label>OG image<input type="text" value="/images/og-default.jpg" /></label>
            <label>Robots<select><option>index,follow</option><option>noindex,nofollow</option></select></label>
          </article>
        </section>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useAdminDashboard } from '@/admin/useAdminDashboard'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminTopbar from '@/admin/components/AdminTopbar.vue'

const {
  sidebarOpen,
  search,
  activeSection,
  activeFilter,
  menuItems,
  metrics,
  statusFilters,
  stories,
  episodes,
  categories,
  tags,
  users,
  orders,
  crawlJobs,
  comments,
  uploadJobs,
  plans,
  currentTitle,
  filteredStories
} = useAdminDashboard()
</script>

<style>
@import '../../css/admin/admin.css';
</style>


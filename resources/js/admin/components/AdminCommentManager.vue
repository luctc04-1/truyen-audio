<template>
  <div class="comment-manager">
    <!-- Toolbar -->
    <div class="manager-toolbar">
      <div class="type-switch">
        <button
          class="btn-tab"
          :class="{ active: activeType === 'series' }"
          type="button"
          @click="changeType('series')"
        >
          <i class="ri-book-3-line"></i>
          <span>Bình luận truyện</span>
        </button>
        <button
          class="btn-tab"
          :class="{ active: activeType === 'community' }"
          type="button"
          @click="changeType('community')"
        >
          <i class="ri-discuss-line"></i>
          <span>Bình luận cộng đồng</span>
        </button>
      </div>

      <div class="search-box">
        <i class="ri-search-line"></i>
        <input
          v-model="search"
          type="search"
          placeholder="Tìm nội dung bình luận..."
          @input="debounceFetch"
        />
        <button v-if="search" class="btn-clear-search" type="button" @click="search = ''; fetchComments(1)">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card-panel table-panel">
      <!-- Loading Progress Indicator Strip -->
      <div v-if="loading && comments.length" class="table-loading-strip"></div>

      <div class="panel-head">
        <div>
          <h3>Kiểm duyệt bình luận</h3>
          <span class="panel-subtitle">Tổng cộng {{ pagination.total || 0 }} bình luận</span>
        </div>
        <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchComments(pagination.current_page)">
          <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
          <span>Làm mới</span>
        </button>
      </div>

      <!-- Skeleton Loading State -->
      <AdminTableSkeleton v-if="loading && !comments.length" :columns="5" :rows="6" />

      <!-- Empty State -->
      <div v-else-if="!comments.length" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-chat-smile-3-line"></i>
        </div>
        <h4>Không có bình luận nào</h4>
        <p>Hệ thống không phát hiện bình luận vi phạm</p>
      </div>

      <!-- Table -->
      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Người gửi</th>
              <th>{{ activeType === 'series' ? 'Truyện' : 'Bài viết' }}</th>
              <th>Nội dung bình luận</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in comments" :key="c.id">
              <td>
                <div class="user-cell">
                  <div class="avatar-mini">
                    <img v-if="c.user?.avatar_url" :src="c.user.avatar_url" alt="" />
                    <span v-else>{{ (c.user?.username || c.user?.email || 'U').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div>
                    <strong>{{ c.user?.username || 'Người dùng' }}</strong>
                    <small>{{ c.user?.email }}</small>
                  </div>
                </div>
              </td>
              <td>
                <span class="target-title">
                  {{ activeType === 'series' ? (c.series?.title || 'Truyện') : (c.post?.title || 'Bài viết') }}
                </span>
              </td>
              <td>
                <p class="comment-content">{{ c.content || c.text || 'N/A' }}</p>
              </td>
              <td>
                <span class="text-muted">{{ formatDate(c.created_at) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="pagination-footer">
        <span class="page-info">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} bình luận)
        </span>
        <div class="pagination-controls">
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page <= 1 || loading"
            @click="fetchComments(pagination.current_page - 1)"
          >
            <i class="ri-arrow-left-s-line"></i> Trước
          </button>
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="fetchComments(pagination.current_page + 1)"
          >
            Sau <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
    </div>
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
const comments = ref([])
const activeType = ref('series')
const search = ref('')

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 20,
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchComments(1)
  }, 300)
}

function changeType(type) {
  activeType.value = type
  fetchComments(1)
}

async function fetchComments(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
      type: activeType.value,
    }
    if (search.value) params.search = search.value

    const res = await AdminService.getComments(params)
    const payload = extractApiPayload(res)

    comments.value = payload.items || []
    if (payload.pagination) {
      pagination.current_page = payload.pagination.current_page
      pagination.last_page = payload.pagination.last_page
      pagination.total = payload.pagination.total
    }
  } catch (err) {
    toast.error('Không thể tải bình luận')
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchComments(1)
})
</script>

<style>
.comment-manager {
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

.type-switch {
  display: flex;
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  border-radius: 12px;
  padding: 4px;
  gap: 4px;
}

.btn-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  border-radius: 9px;
  background: transparent;
  border: none;
  color: var(--admin-muted, #94a3b8);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-tab:hover { color: #f8fafc; }
.btn-tab.active {
  background: #a855f7;
  color: #ffffff;
  box-shadow: 0 2px 10px rgba(168, 85, 247, 0.4);
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
  min-width: 280px;
  flex: 1;
  max-width: 420px;
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

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-mini {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  color: white;
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 13px;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-mini img { width: 100%; height: 100%; object-fit: cover; }
.user-cell strong { display: block; color: #f8fafc; font-size: 13px; font-weight: 600; }
.user-cell small { color: var(--admin-muted, #94a3b8); font-size: 11px; }

.target-title {
  color: #c084fc;
  font-weight: 600;
  max-width: 180px;
  display: inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.comment-content {
  margin: 0;
  color: #e2e8f0;
  font-size: 13px;
  max-width: 420px;
  line-height: 1.4;
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

.empty-state {
  padding: 70px 20px;
  text-align: center;
  color: var(--admin-muted, #94a3b8);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: rgba(168, 85, 247, 0.12);
  color: #c084fc;
  display: grid;
  place-items: center;
  font-size: 32px;
}

.empty-state h4 { color: #f8fafc; font-size: 16px; margin: 0; }
.empty-state p { margin: 0; font-size: 13px; }
</style>

<template>
  <div class="hot-order-manager">
    <!-- Top Summary Banner -->
    <div class="hot-header-card">
      <div class="hot-header-info">
        <div class="badge-fire-glow">
          <i class="ri-fire-fill"></i>
          <span>Quản lý Vị trí Truyện Hot</span>
        </div>
        <h2 class="hot-title">Sắp Xếp Thứ Tự Truyện Hot 🔥</h2>
        <p class="hot-desc">
          Kéo thả thẻ truyện hoặc dùng nút điều hướng để thiết lập thứ tự hiển thị ưu tiên của truyện Hot trên toàn bộ hệ thống.
        </p>
      </div>

      <div class="hot-stats-pills">
        <div class="stat-pill">
          <span class="pill-label">Tổng truyện Hot</span>
          <strong class="pill-val purple">{{ hotItems.length }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Tổng lượt nghe</span>
          <strong class="pill-val emerald">{{ formatNumber(totalListens) }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Đánh giá TB</span>
          <strong class="pill-val amber">⭐ {{ averageScore.toFixed(1) }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Đã ghim vị trí</span>
          <strong class="pill-val cyan">📌 {{ pinnedIds.size }}</strong>
        </div>
      </div>
    </div>

    <!-- Main Controls Toolbar -->
    <div class="manager-toolbar-card">
      <div class="toolbar-left">
        <!-- Quick Search in Hot List -->
        <div class="search-box hot-search">
          <i class="ri-search-line"></i>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Lọc tên truyện, tác giả..."
          />
          <button v-if="searchQuery" class="btn-clear-search" type="button" @click="searchQuery = ''">
            <i class="ri-close-line"></i>
          </button>
        </div>

        <!-- Category Dropdown Filter -->
        <select v-if="availableCategories.length > 1" v-model="selectedCategory" class="filter-select">
          <option value="all">Tất cả thể loại ({{ hotItems.length }})</option>
          <option v-for="cat in availableCategories" :key="cat.name" :value="cat.name">
            {{ cat.name }} ({{ cat.count }})
          </option>
        </select>

        <!-- Smart Auto-Sort Presets Dropdown -->
        <div class="preset-dropdown">
          <button class="btn btn-ghost btn-sm" type="button" @click="presetMenuOpen = !presetMenuOpen">
            <i class="ri-magic-line"></i>
            <span>Sắp xếp tự động</span>
            <i class="ri-arrow-down-s-line"></i>
          </button>
          <div v-if="presetMenuOpen" class="preset-menu" @click="presetMenuOpen = false">
            <button type="button" @click="applyPreset('listens')">
              <i class="ri-headphone-line text-emerald"></i>
              <div>
                <strong>Lượt nghe nhiều nhất</strong>
                <small>Giữ nguyên vị trí truyện đã ghim 📌</small>
              </div>
            </button>
            <button type="button" @click="applyPreset('rating')">
              <i class="ri-star-line text-amber"></i>
              <div>
                <strong>Đánh giá cao nhất</strong>
                <small>Xếp theo số sao trung bình</small>
              </div>
            </button>
            <button type="button" @click="applyPreset('latest')">
              <i class="ri-time-line text-purple"></i>
              <div>
                <strong>Mới cập nhật nhất</strong>
                <small>Xếp theo thời gian update</small>
              </div>
            </button>
          </div>
        </div>

        <!-- Add Story to Hot Button -->
        <button class="btn btn-ghost btn-sm btn-add-hot" type="button" @click="openAddModal">
          <i class="ri-add-circle-line"></i>
          <span>Thêm truyện vào Hot</span>
        </button>

        <!-- Auto-save Toggle Button -->
        <button
          class="btn btn-ghost btn-sm btn-autosave"
          :class="{ 'autosave-active': autoSaveEnabled }"
          type="button"
          :title="autoSaveEnabled ? 'Đang bật tự động lưu khi kéo thả' : 'Bấm để bật tự động lưu khi kéo thả'"
          @click="toggleAutoSave"
        >
          <i class="ri-flashlight-fill text-amber"></i>
          <span>Tự động lưu: {{ autoSaveEnabled ? 'BẬT' : 'TẮT' }}</span>
        </button>

        <!-- Live Preview Toggle Modal Button -->
        <button
          class="btn btn-ghost btn-sm"
          type="button"
          @click="previewModalOpen = true"
        >
          <i class="ri-eye-line"></i>
          <span>Xem trước Web</span>
        </button>
      </div>

      <div class="toolbar-right">
        <!-- Undo / Redo Actions -->
        <div class="undo-redo-group">
          <button
            class="btn-icon-small"
            type="button"
            title="Hoàn tác thao tác trước (Ctrl+Z)"
            :disabled="!canUndo"
            @click="undo"
          >
            <i class="ri-arrow-go-back-line"></i>
          </button>
          <button
            class="btn-icon-small"
            type="button"
            title="Làm lại thao tác vừa hoàn tác (Ctrl+Y)"
            :disabled="!canRedo"
            @click="redo"
          >
            <i class="ri-arrow-go-forward-line"></i>
          </button>
        </div>

        <!-- Live Auto-Save / Save Status Badge -->
        <div class="save-status-badge" :class="saveStatusClass">
          <span class="status-dot"></span>
          <span>{{ saveStatusText }}</span>
        </div>

        <button
          v-if="hasChanges && !autoSaveEnabled"
          class="btn btn-ghost btn-sm"
          type="button"
          :disabled="saving"
          @click="resetChanges"
        >
          <i class="ri-restart-line"></i>
          <span>Khôi phục</span>
        </button>

        <button
          v-if="!autoSaveEnabled || hasChanges"
          class="btn btn-primary btn-sm btn-glow"
          type="button"
          :disabled="saving || !hasChanges"
          @click="saveOrder()"
        >
          <i class="ri-save-3-line" :class="{ 'ri-spin': saving }"></i>
          <span>{{ saving ? 'Đang lưu...' : 'Lưu thứ tự' }}</span>
          <span class="hotkey-tip">Ctrl+S</span>
        </button>
      </div>
    </div>

    <!-- Elevated Floating Batch Action Toolbar (When items selected) -->
    <transition name="fade-slide">
      <div v-if="selectedIds.size > 0" class="batch-action-bar">
        <div class="batch-info">
          <i class="ri-checkbox-circle-fill text-purple"></i>
          <strong>Đã chọn {{ selectedIds.size }} bộ truyện</strong>
        </div>
        <div class="batch-actions">
          <button class="btn btn-ghost btn-xs" type="button" @click="batchMoveToTop">
            <i class="ri-skip-up-line"></i>
            <span>Đưa lên Đầu</span>
          </button>
          <button class="btn btn-ghost btn-xs" type="button" @click="batchMoveToBottom">
            <i class="ri-skip-down-line"></i>
            <span>Đưa xuống Cuối</span>
          </button>
          <button class="btn btn-ghost btn-xs btn-batch-danger" type="button" @click="batchRemoveFromHot">
            <i class="ri-fire-fill"></i>
            <span>Bỏ Hot đã chọn</span>
          </button>
          <button class="btn btn-ghost btn-xs" type="button" @click="selectedIds.clear()">
            <i class="ri-close-line"></i>
            <span>Bỏ chọn</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- Main List Panel Card -->
    <div class="card-panel hot-list-panel">
      <div v-if="loading" class="table-loading-strip"></div>

      <!-- Bulk Select All Header Bar -->
      <div v-if="hotItems.length" class="list-sub-header">
        <label class="select-all-label">
          <input
            type="checkbox"
            :checked="isAllSelected"
            :indeterminate="isIndeterminate"
            @change="toggleSelectAll"
          />
          <span>Chọn tất cả ({{ filteredItems.length }} truyện)</span>
        </label>

        <span class="drag-hint">
          <i class="ri-draggable"></i> Nắm vào icon tay cầm để kéo thả đổi vị trí
        </span>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !hotItems.length" class="empty-state">
        <div class="empty-icon-wrap fire-empty">
          <i class="ri-fire-line"></i>
        </div>
        <h4>Chưa có truyện nào trong danh sách Hot</h4>
        <p>Thêm truyện vào danh sách Hot để sắp xếp và hiển thị nổi bật ở trang chủ</p>
        <button class="btn btn-primary btn-sm" type="button" @click="openAddModal">
          <i class="ri-add-line"></i>
          <span>Chọn truyện thêm vào Hot ngay</span>
        </button>
      </div>

      <!-- Transition Group with Smooth FLIP Animations -->
      <transition-group
        name="reorder-flip"
        tag="div"
        class="reorder-container"
      >
        <div
          v-for="item in filteredItems"
          :key="item.id"
          class="hot-item-card"
          :class="{
            'is-dragging': draggedId === item.id,
            'is-drop-target': dropTargetId === item.id,
            'is-selected': selectedIds.has(item.id),
            'is-pinned': pinnedIds.has(item.id),
            'top-1': rankMap.get(item.id) === 1,
            'top-2': rankMap.get(item.id) === 2,
            'top-3': rankMap.get(item.id) === 3,
          }"
          draggable="true"
          @dragstart="onDragStart($event, item.id)"
          @dragover.prevent="onDragOver($event, item.id)"
          @dragleave="onDragLeave($event, item.id)"
          @drop="onDrop($event, item.id)"
          @dragend="onDragEnd"
        >
          <!-- Left Elements: Selection, Handle, Rank, Delta, Cover -->
          <div class="card-left-group">
            <label class="item-checkbox-wrap" @click.stop>
              <input
                type="checkbox"
                :checked="selectedIds.has(item.id)"
                @change="toggleSelectItem(item.id)"
              />
            </label>

            <div class="drag-handle" title="Nắm và kéo thả để di chuyển vị trí">
              <i class="ri-draggable"></i>
            </div>

            <div class="rank-indicator" :class="`rank-pos-${rankMap.get(item.id)}`">
              <span v-if="rankMap.get(item.id) === 1" class="rank-crown">👑</span>
              <span class="rank-number">#{{ rankMap.get(item.id) }}</span>
            </div>

            <div
              class="rank-delta-badge"
              :class="getRankDeltaClass(item.id)"
              :title="getRankDeltaTooltip(item.id)"
            >
              <span>{{ getRankDeltaText(item.id) }}</span>
            </div>

            <div class="story-thumb-wrap">
              <img
                :src="item.cover_url || '/android-chrome-512x512.png'"
                :alt="item.title"
                class="story-thumb"
                loading="lazy"
                @error="handleImgError"
              />
              <span v-if="item.is_premium" class="vip-mini-tag">VIP</span>

              <button
                class="btn-audio-preview"
                :class="{ 'is-playing': playingStoryId === item.id }"
                type="button"
                :title="playingStoryId === item.id ? 'Dừng phát' : 'Nghe thử trích đoạn'"
                @click.stop="toggleAudioPreview(item)"
              >
                <i :class="playingStoryId === item.id ? 'ri-pause-fill' : 'ri-play-fill'"></i>
              </button>
            </div>
          </div>

          <!-- Center Details: Title, Meta, Tags -->
          <div class="card-center-group">
            <div class="story-title-row">
              <strong class="story-title" :title="item.title">{{ item.title }}</strong>
              <span class="category-pill">{{ item.category || 'Tổng hợp' }}</span>
              <span v-if="pinnedIds.has(item.id)" class="pin-active-tag">
                <i class="ri-pushpin-fill"></i> Đã ghim
              </span>
            </div>
            <div class="story-meta-row">
              <span v-if="item.author" class="meta-item"><i class="ri-user-line"></i> {{ item.author }}</span>
              <span class="meta-item"><i class="ri-headphone-line"></i> {{ formatNumber(item.total_listens || item.listen_count || 0) }}</span>
              <span class="meta-item"><i class="ri-play-list-2-line"></i> {{ item.total_episodes || 0 }} tập</span>
              <span class="meta-item rating"><i class="ri-star-fill text-amber"></i> {{ Number(item.average_rating || 5).toFixed(1) }}</span>
            </div>
          </div>

          <!-- Right Elements: Quick Rank, Pin, Move Actions -->
          <div class="card-right-group">
            <div class="quick-rank-jump">
              <label class="rank-input-label">Hạng:</label>
              <input
                type="number"
                min="1"
                :max="hotItems.length"
                :value="rankMap.get(item.id)"
                class="rank-num-input"
                title="Nhập số thứ tự và nhấn Enter để nhảy vị trí"
                @change="onDirectRankChange(item.id, $event.target.value)"
              />
            </div>

            <button
              class="btn-icon btn-pin"
              :class="{ active: pinnedIds.has(item.id) }"
              type="button"
              :title="pinnedIds.has(item.id) ? 'Bỏ ghim vị trí' : 'Ghim cố định vị trí khi sắp xếp tự động'"
              @click="togglePin(item.id)"
            >
              <i :class="pinnedIds.has(item.id) ? 'ri-pushpin-fill' : 'ri-pushpin-line'"></i>
            </button>

            <div class="reorder-actions">
              <button
                class="btn-icon"
                type="button"
                title="Đưa lên Top 1"
                :disabled="rankMap.get(item.id) === 1"
                @click="moveToTop(item.id)"
              >
                <i class="ri-skip-up-line"></i>
              </button>
              <button
                class="btn-icon"
                type="button"
                title="Lên 1 bậc"
                :disabled="rankMap.get(item.id) === 1"
                @click="moveUp(item.id)"
              >
                <i class="ri-arrow-up-s-line"></i>
              </button>
              <button
                class="btn-icon"
                type="button"
                title="Xuống 1 bậc"
                :disabled="rankMap.get(item.id) === hotItems.length"
                @click="moveDown(item.id)"
              >
                <i class="ri-arrow-down-s-line"></i>
              </button>
              <button
                class="btn-icon"
                type="button"
                title="Đưa xuống cuối cùng"
                :disabled="rankMap.get(item.id) === hotItems.length"
                @click="moveToBottom(item.id)"
              >
                <i class="ri-skip-down-line"></i>
              </button>
              <button
                class="btn-icon btn-remove"
                type="button"
                title="Bỏ khỏi danh sách Hot"
                @click="removeFromHot(item)"
              >
                <i class="ri-fire-fill text-danger"></i>
              </button>
            </div>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Hidden Audio Element for Sneak Peek -->
    <audio ref="audioElementRef" @ended="onAudioEnded"></audio>

    <!-- MODAL: LIVE CLIENT PREVIEW -->
    <div v-if="previewModalOpen" class="admin-modal-overlay" @click.self="previewModalOpen = false">
      <div class="admin-modal-box preview-modal-box">
        <div class="modal-head">
          <div class="preview-head-left">
            <div class="live-dot-pulse"></div>
            <div>
              <h3>Xem Trước Hiển Thị Trang Chủ</h3>
              <p>Mô phỏng vị trí hiển thị các truyện Hot theo thứ tự thực tế</p>
            </div>
          </div>

          <div class="preview-head-right">
            <div class="device-switch">
              <button
                class="device-btn"
                :class="{ active: previewDevice === 'desktop' }"
                type="button"
                @click="previewDevice = 'desktop'"
              >
                <i class="ri-computer-line"></i> Máy tính
              </button>
              <button
                class="device-btn"
                :class="{ active: previewDevice === 'mobile' }"
                type="button"
                @click="previewDevice = 'mobile'"
              >
                <i class="ri-smartphone-line"></i> Điện thoại
              </button>
            </div>
            <button class="btn-close-modal" type="button" @click="previewModalOpen = false">
              <i class="ri-close-line"></i>
            </button>
          </div>
        </div>

        <div class="modal-body preview-modal-body">
          <div class="preview-canvas" :class="`device-${previewDevice}`">
            <div class="mock-client-page">
              <div class="mock-nav">
                <div class="mock-logo"><i class="ri-headphone-fill"></i> Truyện Audio Hay</div>
                <div class="mock-search-bar"><i class="ri-search-line"></i> Tìm truyện, tác giả...</div>
              </div>

              <div class="mock-section-title">
                <div class="mock-badge"><i class="ri-fire-fill text-amber"></i> Truyện Hot Đang Nghe Nhiều</div>
                <small class="mock-sub">Top truyện nổi bật cập nhật theo thứ tự trong Admin</small>
              </div>

              <div class="mock-story-cards-grid" :class="`grid-${previewDevice}`">
                <div
                  v-for="(item, idx) in hotItems"
                  :key="item.id"
                  class="mock-story-card"
                >
                  <div class="mock-cover-wrap">
                    <img
                      :src="item.cover_url || '/android-chrome-512x512.png'"
                      :alt="item.title"
                      @error="handleImgError"
                    />
                    <div class="mock-rank-pill" :class="`rank-${idx + 1}`">
                      #{{ idx + 1 }}
                    </div>
                    <span v-if="item.is_premium" class="mock-vip-badge">VIP</span>
                  </div>
                  <div class="mock-card-body">
                    <strong class="mock-card-title">{{ item.title }}</strong>
                    <div class="mock-card-author">{{ item.author || 'Đang cập nhật' }}</div>
                    <div class="mock-card-footer">
                      <span class="mock-views"><i class="ri-headphone-line"></i> {{ formatNumber(item.total_listens || item.listen_count || 0) }}</span>
                      <span class="mock-rating"><i class="ri-star-fill text-amber"></i> {{ Number(item.average_rating || 5).toFixed(1) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-foot">
          <span class="text-muted text-xs">Mô phỏng trực tiếp từ danh sách truyện Hot hiện hành.</span>
          <button class="btn btn-ghost btn-sm" type="button" @click="previewModalOpen = false">
            Đóng xem trước
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: ADD STORY TO HOT -->
    <div v-if="addModalOpen" class="admin-modal-overlay" @click.self="addModalOpen = false">
      <div class="admin-modal-box">
        <div class="modal-head">
          <div>
            <h3>Thêm Truyện Vào Danh Sách Hot 🔥</h3>
            <p>Tìm kiếm truyện trong kho để đưa vào danh sách Hot</p>
          </div>
          <button class="btn-close-modal" type="button" @click="addModalOpen = false">
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="search-box modal-search">
            <i class="ri-search-line"></i>
            <input
              v-model="addSearch"
              type="search"
              placeholder="Nhập tên truyện, tác giả hoặc thể loại để tìm..."
              @input="debounceSearchAll"
            />
            <button v-if="addSearch" class="btn-clear-search" type="button" @click="addSearch = ''; searchAllSeries()">
              <i class="ri-close-line"></i>
            </button>
          </div>

          <div class="modal-series-list">
            <div v-if="searchLoading" class="modal-loading">
              <i class="ri-loader-4-line ri-spin"></i>
              <span>Đang tìm kiếm...</span>
            </div>

            <div v-else-if="!candidateSeries.length" class="modal-empty">
              <i class="ri-search-eye-line"></i>
              <span>Không tìm thấy truyện phù hợp</span>
            </div>

            <div
              v-for="s in candidateSeries"
              v-else
              :key="s.id"
              class="candidate-row"
            >
              <img
                :src="s.cover_url || '/android-chrome-512x512.png'"
                :alt="s.title"
                class="candidate-thumb"
                @error="handleImgError"
              />
              <div class="candidate-info">
                <strong>{{ s.title }}</strong>
                <div class="candidate-meta">
                  <span>{{ s.category }}</span>
                  <span>•</span>
                  <span>{{ s.author || 'Đang cập nhật' }}</span>
                  <span>•</span>
                  <span>{{ formatNumber(s.total_listens || s.listen_count || 0) }} lượt nghe</span>
                </div>
              </div>

              <div class="candidate-actions">
                <span v-if="isAlreadyHot(s.id)" class="badge-already-hot">
                  <i class="ri-check-line"></i> Đã ở trong Hot
                </span>
                <template v-else>
                  <button
                    class="btn btn-ghost btn-xs"
                    type="button"
                    title="Thêm vào vị trí đầu tiên (Top 1)"
                    @click="addStoryToHot(s, 'top')"
                  >
                    <i class="ri-skip-up-line"></i>
                    <span>Thêm vào Đầu</span>
                  </button>
                  <button
                    class="btn btn-primary btn-xs"
                    type="button"
                    title="Thêm vào cuối danh sách"
                    @click="addStoryToHot(s, 'bottom')"
                  >
                    <i class="ri-add-line"></i>
                    <span>Thêm vào Cuối</span>
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-foot">
          <span class="text-muted text-xs">Mẹo: Kéo thả lại vị trí sau khi thêm để khớp thứ tự mong muốn.</span>
          <button class="btn btn-ghost btn-sm" type="button" @click="addModalOpen = false">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue'
import AdminService from '@/services/AdminService'
import { extractApiPayload, formatNumber } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const toast = useToastStore()

// State
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('all')
const presetMenuOpen = ref(false)
const previewModalOpen = ref(false)
const previewDevice = ref('desktop')
const autoSaveEnabled = ref(localStorage.getItem('admin_hot_autosave') !== 'false')
let autoSaveTimer = null

// Lists & Lookups
const hotItems = ref([])
const originalMap = ref(new Map()) // id -> original 1-based index

// Selection & Pinning State
const selectedIds = reactive(new Set())
const pinnedIds = reactive(new Set())

// Drag and drop state
const draggedId = ref(null)
const dropTargetId = ref(null)

// History Stack for Multi-level Undo & Redo
const historyStack = ref([])
const redoStack = ref([])

// Audio Sneak Peek State
const playingStoryId = ref(null)
const audioElementRef = ref(null)

// Add Modal State
const addModalOpen = ref(false)
const addSearch = ref('')
const searchLoading = ref(false)
const candidateSeries = ref([])
let debounceTimeout = null

// ─── Fast O(1) Rank Lookup Map ──────────────────────────────────────────────
const rankMap = computed(() => {
  const map = new Map()
  hotItems.value.forEach((item, idx) => {
    map.set(item.id, idx + 1)
  })
  return map
})

// ─── Auto-Save Computed & Handlers ──────────────────────────────────────────
const saveStatusText = computed(() => {
  if (saving.value) return 'Đang lưu...'
  if (hasChanges.value && !autoSaveEnabled.value) return 'Chưa lưu'
  if (autoSaveEnabled.value) return 'Tự động lưu: BẬT'
  return 'Đã đồng bộ'
})

const saveStatusClass = computed(() => {
  if (saving.value) return 'status-saving'
  if (hasChanges.value && !autoSaveEnabled.value) return 'status-unsaved'
  return 'status-saved'
})

function toggleAutoSave() {
  autoSaveEnabled.value = !autoSaveEnabled.value
  localStorage.setItem('admin_hot_autosave', String(autoSaveEnabled.value))
  if (autoSaveEnabled.value) {
    toast.success('Đã BẬT tự động lưu khi kéo thả!')
    if (hasChanges.value) {
      saveOrder({ isAuto: true })
    }
  } else {
    toast.info('Đã TẮT tự động lưu. Bạn cần nhấn "Lưu thứ tự" thủ công.')
  }
}

function triggerAutoSave() {
  if (!autoSaveEnabled.value) return
  clearTimeout(autoSaveTimer)
  autoSaveTimer = setTimeout(() => {
    saveOrder({ isAuto: true })
  }, 120)
}

// ─── Computed Properties ────────────────────────────────────────────────────
const totalListens = computed(() => {
  return hotItems.value.reduce((sum, item) => sum + (Number(item.total_listens || item.listen_count || 0)), 0)
})

const averageScore = computed(() => {
  if (!hotItems.value.length) return 5
  const sum = hotItems.value.reduce((s, item) => s + (Number(item.average_rating || 5)), 0)
  return sum / hotItems.value.length
})

const availableCategories = computed(() => {
  const map = {}
  hotItems.value.forEach((item) => {
    const cat = item.category || 'Khác'
    map[cat] = (map[cat] || 0) + 1
  })
  return Object.keys(map).map((name) => ({ name, count: map[name] }))
})

const hasChanges = computed(() => {
  if (hotItems.value.length !== originalMap.value.size) return true
  for (let i = 0; i < hotItems.value.length; i++) {
    if (originalMap.value.get(hotItems.value[i].id) !== i + 1) return true
  }
  return false
})

const canUndo = computed(() => historyStack.value.length > 0)
const canRedo = computed(() => redoStack.value.length > 0)

const filteredItems = computed(() => {
  let list = hotItems.value
  if (selectedCategory.value !== 'all') {
    list = list.filter((item) => item.category === selectedCategory.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(
      (item) =>
        item.title?.toLowerCase().includes(q) ||
        item.author?.toLowerCase().includes(q) ||
        item.category?.toLowerCase().includes(q)
    )
  }
  return list
})

const isAllSelected = computed(() => {
  if (!filteredItems.value.length) return false
  return filteredItems.value.every((item) => selectedIds.has(item.id))
})

const isIndeterminate = computed(() => {
  if (!filteredItems.value.length) return false
  const count = filteredItems.value.filter((item) => selectedIds.has(item.id)).length
  return count > 0 && count < filteredItems.value.length
})

function isAlreadyHot(id) {
  return rankMap.value.has(id)
}

// ─── Rank Delta Calculations ────────────────────────────────────────────────
function getRankDelta(id) {
  const originalRank = originalMap.value.get(id)
  if (!originalRank) return 0
  const currentRank = rankMap.value.get(id) || 0
  return originalRank - currentRank
}

function getRankDeltaText(id) {
  const delta = getRankDelta(id)
  if (delta > 0) return `▲ +${delta}`
  if (delta < 0) return `▼ ${delta}`
  return '='
}

function getRankDeltaClass(id) {
  const delta = getRankDelta(id)
  if (delta > 0) return 'delta-up'
  if (delta < 0) return 'delta-down'
  return 'delta-none'
}

function getRankDeltaTooltip(id) {
  const originalRank = originalMap.value.get(id)
  const currentRank = rankMap.value.get(id) || 0
  if (!originalRank) return 'Mới thêm vào'
  if (originalRank === currentRank) return `Vị trí ban đầu: #${originalRank}`
  return `Vị trí ban đầu: #${originalRank} -> Hiện tại: #${currentRank}`
}

// ─── History / Snapshot Management (Undo & Redo) ────────────────────────────
function pushHistorySnapshot() {
  const snapshot = hotItems.value.map((item) => ({ ...item }))
  historyStack.value.push(snapshot)
  if (historyStack.value.length > 30) {
    historyStack.value.shift()
  }
  redoStack.value = []
}

function undo() {
  if (!canUndo.value) return
  const currentSnapshot = hotItems.value.map((item) => ({ ...item }))
  redoStack.value.push(currentSnapshot)

  const prev = historyStack.value.pop()
  hotItems.value = prev
  triggerAutoSave()
  toast.info('Đã hoàn tác thao tác vừa rồi')
}

function redo() {
  if (!canRedo.value) return
  const currentSnapshot = hotItems.value.map((item) => ({ ...item }))
  historyStack.value.push(currentSnapshot)

  const next = redoStack.value.pop()
  hotItems.value = next
  triggerAutoSave()
  toast.info('Đã làm lại thao tác')
}

// ─── Fetch Data ─────────────────────────────────────────────────────────────
async function fetchHotSeries() {
  loading.value = true
  try {
    const res = await AdminService.getHotSeries()
    const payload = extractApiPayload(res)
    const items = payload?.items || (Array.isArray(payload) ? payload : [])
    hotItems.value = [...items]

    originalMap.value.clear()
    items.forEach((item, index) => {
      originalMap.value.set(item.id, index + 1)
    })

    historyStack.value = []
    redoStack.value = []
  } catch (err) {
    console.error('Failed to load hot series', err)
    toast.error('Không thể tải danh sách truyện hot')
  } finally {
    loading.value = false
  }
}

// ─── Drag & Drop Handlers with FLIP Transition ──────────────────────────────
function onDragStart(event, id) {
  draggedId.value = id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', id)
}

function onDragOver(event, id) {
  event.preventDefault()
  if (draggedId.value === id) return
  dropTargetId.value = id
}

function onDragLeave(event, id) {
  if (dropTargetId.value === id) {
    dropTargetId.value = null
  }
}

function onDrop(event, targetId) {
  event.preventDefault()
  const sourceId = draggedId.value
  if (!sourceId || sourceId === targetId) {
    draggedId.value = null
    dropTargetId.value = null
    return
  }

  pushHistorySnapshot()

  const sourceIndex = hotItems.value.findIndex((item) => item.id === sourceId)
  const targetIndex = hotItems.value.findIndex((item) => item.id === targetId)

  if (sourceIndex !== -1 && targetIndex !== -1) {
    const [movedItem] = hotItems.value.splice(sourceIndex, 1)
    hotItems.value.splice(targetIndex, 0, movedItem)
    triggerAutoSave()
  }

  draggedId.value = null
  dropTargetId.value = null
}

function onDragEnd() {
  draggedId.value = null
  dropTargetId.value = null
}

// ─── Quick Movement Actions ─────────────────────────────────────────────────
function moveUp(id) {
  const index = hotItems.value.findIndex((item) => item.id === id)
  if (index > 0) {
    pushHistorySnapshot()
    const [item] = hotItems.value.splice(index, 1)
    hotItems.value.splice(index - 1, 0, item)
    triggerAutoSave()
  }
}

function moveDown(id) {
  const index = hotItems.value.findIndex((item) => item.id === id)
  if (index < hotItems.value.length - 1 && index !== -1) {
    pushHistorySnapshot()
    const [item] = hotItems.value.splice(index, 1)
    hotItems.value.splice(index + 1, 0, item)
    triggerAutoSave()
  }
}

function moveToTop(id) {
  const index = hotItems.value.findIndex((item) => item.id === id)
  if (index > 0) {
    pushHistorySnapshot()
    const [item] = hotItems.value.splice(index, 1)
    hotItems.value.unshift(item)
    triggerAutoSave()
  }
}

function moveToBottom(id) {
  const index = hotItems.value.findIndex((item) => item.id === id)
  if (index < hotItems.value.length - 1 && index !== -1) {
    pushHistorySnapshot()
    const [item] = hotItems.value.splice(index, 1)
    hotItems.value.push(item)
    triggerAutoSave()
  }
}

function onDirectRankChange(id, newRankStr) {
  let newRank = parseInt(newRankStr, 10)
  if (isNaN(newRank) || newRank < 1) newRank = 1
  if (newRank > hotItems.value.length) newRank = hotItems.value.length

  const currentIndex = hotItems.value.findIndex((item) => item.id === id)
  const targetIndex = newRank - 1

  if (currentIndex !== -1 && currentIndex !== targetIndex) {
    pushHistorySnapshot()
    const [item] = hotItems.value.splice(currentIndex, 1)
    hotItems.value.splice(targetIndex, 0, item)
    triggerAutoSave()
  }
}

// ─── Pinning Stories ────────────────────────────────────────────────────────
function togglePin(id) {
  if (pinnedIds.has(id)) {
    pinnedIds.delete(id)
    toast.info('Đã bỏ ghim truyện')
  } else {
    pinnedIds.add(id)
    toast.success('Đã ghim vị trí truyện này!')
  }
}

// ─── Multi-Selection & Batch Actions ────────────────────────────────────────
function toggleSelectItem(id) {
  if (selectedIds.has(id)) {
    selectedIds.delete(id)
  } else {
    selectedIds.add(id)
  }
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedIds.clear()
  } else {
    filteredItems.value.forEach((item) => selectedIds.add(item.id))
  }
}

function batchMoveToTop() {
  if (!selectedIds.size) return
  pushHistorySnapshot()

  const selected = []
  const remaining = []

  hotItems.value.forEach((item) => {
    if (selectedIds.has(item.id)) {
      selected.push(item)
    } else {
      remaining.push(item)
    }
  })

  hotItems.value = [...selected, ...remaining]
  triggerAutoSave()
  toast.success(`Đã đưa ${selected.length} truyện đã chọn lên Đầu`)
}

function batchMoveToBottom() {
  if (!selectedIds.size) return
  pushHistorySnapshot()

  const selected = []
  const remaining = []

  hotItems.value.forEach((item) => {
    if (selectedIds.has(item.id)) {
      selected.push(item)
    } else {
      remaining.push(item)
    }
  })

  hotItems.value = [...remaining, ...selected]
  triggerAutoSave()
  toast.success(`Đã đưa ${selected.length} truyện đã chọn xuống Cuối`)
}

async function batchRemoveFromHot() {
  if (!selectedIds.size) return
  if (!confirm(`Bạn có chắc chắn muốn bỏ ${selectedIds.size} truyện đã chọn khỏi danh sách Hot?`)) {
    return
  }

  const idsToRemove = Array.from(selectedIds)
  try {
    for (const id of idsToRemove) {
      await AdminService.toggleSeriesHot(id)
    }
    pushHistorySnapshot()
    hotItems.value = hotItems.value.filter((item) => !selectedIds.has(item.id))
    selectedIds.clear()
    triggerAutoSave()
    toast.success(`Đã bỏ ${idsToRemove.length} truyện khỏi danh sách Hot`)
  } catch (err) {
    console.error('Batch remove from hot failed', err)
    toast.error('Có lỗi xảy ra khi gỡ bỏ truyện hot')
  }
}

// ─── Single Remove from Hot ─────────────────────────────────────────────────
async function removeFromHot(item) {
  if (!confirm(`Bạn có chắc chắn muốn bỏ "${item.title}" khỏi danh sách Truyện Hot?`)) {
    return
  }

  try {
    await AdminService.toggleSeriesHot(item.id)
    pushHistorySnapshot()
    hotItems.value = hotItems.value.filter((i) => i.id !== item.id)
    originalMap.value.delete(item.id)
    selectedIds.delete(item.id)
    pinnedIds.delete(item.id)
    triggerAutoSave()
    toast.success(`Đã bỏ "${item.title}" khỏi danh sách Hot`)
  } catch (err) {
    console.error('Failed to remove from hot', err)
    toast.error('Không thể cập nhật trạng thái Hot')
  }
}

// ─── Smart Auto-Sort Presets (Respects Pinned Items) ─────────────────────────
function applyPreset(type) {
  pushHistorySnapshot()

  const pinnedPositions = []
  const unpinnedItems = []

  hotItems.value.forEach((item, index) => {
    if (pinnedIds.has(item.id)) {
      pinnedPositions.push({ item, index })
    } else {
      unpinnedItems.push(item)
    }
  })

  if (type === 'listens') {
    unpinnedItems.sort((a, b) => (Number(b.total_listens || b.listen_count || 0)) - (Number(a.total_listens || a.listen_count || 0)))
    toast.info('Đã sắp xếp theo Lượt nghe (Cao -> Thấp)')
  } else if (type === 'rating') {
    unpinnedItems.sort((a, b) => (Number(b.average_rating || 5)) - (Number(a.average_rating || 5)))
    toast.info('Đã sắp xếp theo Đánh giá (Cao -> Thấp)')
  } else if (type === 'latest') {
    unpinnedItems.sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at))
    toast.info('Đã sắp xếp theo Mới cập nhật')
  }

  const result = [...unpinnedItems]
  pinnedPositions.forEach(({ item, index }) => {
    result.splice(index, 0, item)
  })

  hotItems.value = result
  triggerAutoSave()
}

// ─── Reset & Save ───────────────────────────────────────────────────────────
function resetChanges() {
  fetchHotSeries()
  toast.info('Đã khôi phục lại thứ tự ban đầu')
}

async function saveOrder({ isAuto = false } = {}) {
  if (saving.value) return
  saving.value = true
  try {
    const itemsPayload = hotItems.value.map((item, index) => ({
      id: item.id,
      hot_order: index + 1,
    }))

    const res = await AdminService.reorderHotSeries(itemsPayload)
    const payload = extractApiPayload(res)
    const items = payload?.items || (Array.isArray(payload) ? payload : [])
    
    if (items.length) {
      hotItems.value = [...items]
      originalMap.value.clear()
      items.forEach((item, index) => {
        originalMap.value.set(item.id, index + 1)
      })
    } else {
      originalMap.value.clear()
      hotItems.value.forEach((item, index) => {
        originalMap.value.set(item.id, index + 1)
      })
    }

    if (!isAuto) {
      historyStack.value = []
      redoStack.value = []
      toast.success('Đã lưu thứ tự truyện Hot thành công! 🔥')
    } else {
      toast.success('Đã tự động lưu vị trí mới! ⚡')
    }
  } catch (err) {
    console.error('Failed to save hot order', err)
    toast.error('Lưu thứ tự thất bại, vui lòng thử lại!')
  } finally {
    saving.value = false
  }
}

// ─── Audio Sneak Peek ───────────────────────────────────────────────────────
async function toggleAudioPreview(item) {
  if (playingStoryId.value === item.id) {
    audioElementRef.value?.pause()
    playingStoryId.value = null
    return
  }

  try {
    const res = await AdminService.getSeriesDetail(item.id)
    const payload = extractApiPayload(res)
    const firstEp = payload?.episodes?.[0]
    const audioUrl = firstEp?.storage_audio_url || firstEp?.audio_path

    if (!audioUrl) {
      toast.info('Truyện này chưa có tập audio để phát thử')
      return
    }

    if (audioElementRef.value) {
      audioElementRef.value.src = audioUrl
      audioElementRef.value.play()
      playingStoryId.value = item.id
      toast.info(`Đang nghe thử: ${item.title}`)
    }
  } catch (err) {
    console.error('Failed to preview audio', err)
    toast.error('Không thể phát thử audio của truyện này')
  }
}

function onAudioEnded() {
  playingStoryId.value = null
}

// ─── Add to Hot Modal ───────────────────────────────────────────────────────
function openAddModal() {
  addModalOpen.value = true
  addSearch.value = ''
  searchAllSeries()
}

function debounceSearchAll() {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    searchAllSeries()
  }, 300)
}

async function searchAllSeries() {
  searchLoading.value = true
  try {
    const res = await AdminService.getSeries({
      search: addSearch.value,
      per_page: 20,
    })
    const payload = extractApiPayload(res)
    candidateSeries.value = payload?.items || []
  } catch (err) {
    console.error('Failed to search candidate series', err)
  } finally {
    searchLoading.value = false
  }
}

async function addStoryToHot(series, position = 'bottom') {
  try {
    if (!series.is_hot) {
      await AdminService.toggleSeriesHot(series.id)
    }

    pushHistorySnapshot()
    if (position === 'top') {
      hotItems.value.unshift(series)
    } else {
      hotItems.value.push(series)
    }

    triggerAutoSave()
    toast.success(`Đã thêm "${series.title}" vào danh sách Hot (${position === 'top' ? 'Đầu' : 'Cuối'})`)
  } catch (err) {
    console.error('Failed to add story to hot', err)
    toast.error('Không thể thêm truyện vào danh sách Hot')
  }
}

function handleImgError(e) {
  e.target.src = '/android-chrome-512x512.png'
}

// ─── Hotkey Support (Ctrl+S, Ctrl+Z, Ctrl+Y) ────────────────────────────────
function onKeyDown(e) {
  const isCtrl = e.ctrlKey || e.metaKey

  if (isCtrl && e.key === 's') {
    e.preventDefault()
    if (hasChanges.value) saveOrder()
  } else if (isCtrl && !e.shiftKey && e.key.toLowerCase() === 'z') {
    e.preventDefault()
    undo()
  } else if (isCtrl && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) {
    e.preventDefault()
    redo()
  }
}

onMounted(() => {
  fetchHotSeries()
  window.addEventListener('keydown', onKeyDown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeyDown)
  if (audioElementRef.value) {
    audioElementRef.value.pause()
  }
})
</script>

<style scoped>
.hot-order-manager {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
  box-sizing: border-box;
}

/* ─── Header Card ─── */
.hot-header-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 16px;
  padding: 20px 24px;
  backdrop-filter: blur(16px);
  position: relative;
  overflow: hidden;
  box-shadow: var(--admin-glass-shadow);
}

.hot-header-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #f59e0b, #ec4899, #a855f7);
}

.badge-fire-glow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.25);
  color: #fbbf24;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.hot-title {
  margin: 0 0 6px 0;
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}

.hot-desc {
  margin: 0;
  font-size: 13px;
  color: var(--admin-muted);
  max-width: 600px;
  line-height: 1.5;
}

.hot-stats-pills {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.stat-pill {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  padding: 8px 14px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 105px;
  text-align: center;
}

.pill-label {
  font-size: 11px;
  color: var(--admin-faint);
  font-weight: 600;
}

.pill-val {
  font-size: 16px;
  font-weight: 800;
  font-family: var(--admin-font-mono);
}

.pill-val.purple { color: #c084fc; }
.pill-val.emerald { color: #34d399; }
.pill-val.amber { color: #fbbf24; }
.pill-val.cyan { color: #38bdf8; }

/* ─── Controls Toolbar Card ─── */
.manager-toolbar-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 12px 16px;
  backdrop-filter: blur(20px);
  box-shadow: var(--admin-glass-shadow);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  flex: 1;
}

.hot-search {
  max-width: 220px;
}

.filter-select {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  padding: 8px 12px;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.filter-select:focus {
  border-color: var(--admin-primary);
}

.filter-select option {
  background: #121624;
  color: #fff;
}

.undo-redo-group {
  display: flex;
  gap: 4px;
  background: rgba(255, 255, 255, 0.03);
  padding: 3px;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
}

.btn-icon-small {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: none;
  border: none;
  color: var(--admin-muted);
  display: grid;
  place-items: center;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-icon-small:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.btn-icon-small:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.preset-dropdown {
  position: relative;
}

.preset-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 240px;
  background: #141724;
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  padding: 6px;
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.6);
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.preset-menu button {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  background: none;
  border: none;
  color: #fff;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s ease;
  width: 100%;
}

.preset-menu button:hover {
  background: rgba(255, 255, 255, 0.06);
}

.preset-menu button i {
  font-size: 18px;
}

.preset-menu button strong {
  display: block;
  font-size: 12px;
}

.preset-menu button small {
  display: block;
  font-size: 10px;
  color: var(--admin-muted);
}

.btn-autosave {
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--admin-muted);
  transition: all 0.2s ease;
}

.btn-autosave.autosave-active {
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.35);
  color: #34d399;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.save-status-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.save-status-badge.status-saved {
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #34d399;
}

.save-status-badge.status-saving {
  background: rgba(168, 85, 247, 0.15);
  border: 1px solid rgba(168, 85, 247, 0.35);
  color: #c084fc;
}

.save-status-badge.status-unsaved {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fbbf24;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  box-shadow: 0 0 6px currentColor;
}

.save-status-badge.status-saving .status-dot {
  animation: pulseGlow 1s infinite alternate;
}

.save-status-badge.status-unsaved .status-dot {
  animation: pulseGlow 1.5s infinite;
}

.hotkey-tip {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.25);
  color: rgba(255, 255, 255, 0.8);
  margin-left: 4px;
}

/* ─── Batch Action Toolbar ─── */
.batch-action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background: linear-gradient(90deg, #1e1b4b 0%, #172554 100%);
  border: 1px solid rgba(168, 85, 247, 0.4);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}

.batch-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #fff;
}

.batch-actions {
  display: flex;
  gap: 8px;
}

.btn-batch-danger:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

/* ─── Main List Panel ─── */
.hot-list-panel {
  padding: 16px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 16px;
  box-shadow: var(--admin-glass-shadow);
}

.list-sub-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px 12px 8px;
  border-bottom: 1px solid var(--admin-border);
  margin-bottom: 12px;
}

.select-all-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--admin-muted);
  cursor: pointer;
}

.drag-hint {
  font-size: 11px;
  color: var(--admin-faint);
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ─── FLIP ANIMATION TRANSITIONS ─── */
.reorder-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
  position: relative;
}

.reorder-flip-move {
  transition: transform 0.3s cubic-bezier(0.2, 1, 0.3, 1);
}

/* ─── Item Card Structure: Left Group / Center Group / Right Group ─── */
.hot-item-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: rgba(255, 255, 255, 0.025);
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  padding: 8px 14px;
  transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
  position: relative;
  user-select: none;
  min-width: 0;
}

.hot-item-card:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.12);
}

.hot-item-card.is-selected {
  background: rgba(168, 85, 247, 0.08);
  border-color: rgba(168, 85, 247, 0.4);
}

.hot-item-card.is-pinned {
  border-left: 3px solid #38bdf8;
}

.hot-item-card.is-dragging {
  opacity: 0.35;
  border: 1px dashed var(--admin-primary);
  transform: scale(0.98);
}

.hot-item-card.is-drop-target {
  border-color: #a855f7;
  background: rgba(168, 85, 247, 0.14);
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.3);
}

/* Top 3 Glow Styles */
.hot-item-card.top-1 {
  border-color: rgba(245, 158, 11, 0.35);
  background: linear-gradient(90deg, rgba(245, 158, 11, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
}

.hot-item-card.top-2 {
  border-color: rgba(203, 213, 225, 0.25);
  background: linear-gradient(90deg, rgba(203, 213, 225, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
}

.hot-item-card.top-3 {
  border-color: rgba(180, 83, 9, 0.25);
  background: linear-gradient(90deg, rgba(180, 83, 9, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
}

/* Left Group */
.card-left-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.item-checkbox-wrap {
  cursor: pointer;
  display: grid;
  place-items: center;
}

.drag-handle {
  cursor: grab;
  color: var(--admin-faint);
  font-size: 18px;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.15s ease, background 0.15s ease;
}

.drag-handle:hover {
  color: #c084fc;
  background: rgba(255, 255, 255, 0.06);
}

.drag-handle:active {
  cursor: grabbing;
}

.rank-indicator {
  min-width: 40px;
  height: 34px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
  font-weight: 800;
  font-size: 13px;
  font-family: var(--admin-font-mono);
  color: var(--admin-muted);
  flex-shrink: 0;
}

.rank-pos-1 {
  background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #b45309 100%);
  color: #1c1917;
  box-shadow: 0 0 16px rgba(245, 158, 11, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.5);
  font-weight: 900;
}

.rank-pos-2 {
  background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #64748b 100%);
  color: #0f172a;
  box-shadow: 0 0 14px rgba(203, 213, 225, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.5);
  font-weight: 900;
}

.rank-pos-3 {
  background: linear-gradient(135deg, #fdba74 0%, #ea580c 50%, #9a3412 100%);
  color: #fff7ed;
  box-shadow: 0 0 14px rgba(234, 88, 12, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.4);
  font-weight: 900;
}

.rank-crown {
  font-size: 10px;
}

.rank-delta-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 6px;
  font-family: var(--admin-font-mono);
  min-width: 32px;
  text-align: center;
  flex-shrink: 0;
}

.delta-up {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
}

.delta-down {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
}

.delta-none {
  background: rgba(255, 255, 255, 0.04);
  color: var(--admin-faint);
}

.story-thumb-wrap {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.story-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.btn-audio-preview {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: grid;
  place-items: center;
  color: #fff;
  border: none;
  font-size: 18px;
  opacity: 0;
  cursor: pointer;
  transition: opacity 0.15s ease;
}

.story-thumb-wrap:hover .btn-audio-preview,
.btn-audio-preview.is-playing {
  opacity: 1;
}

.btn-audio-preview.is-playing {
  background: rgba(168, 85, 247, 0.7);
}

.vip-mini-tag {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #f59e0b;
  color: #000;
  font-size: 8px;
  font-weight: 800;
  padding: 1px 3px;
  border-radius: 3px;
}

/* Center Details Group */
.card-center-group {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.story-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.story-title {
  font-size: 13px;
  color: #f8fafc;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.category-pill {
  font-size: 10px;
  padding: 1px 6px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.06);
  color: var(--admin-muted);
  flex-shrink: 0;
}

.pin-active-tag {
  font-size: 10px;
  color: #38bdf8;
  display: inline-flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
}

.story-meta-row {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 11px;
  color: var(--admin-faint);
  flex-wrap: wrap;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* Right Actions Group */
.card-right-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.quick-rank-jump {
  display: flex;
  align-items: center;
  gap: 4px;
  background: rgba(0, 0, 0, 0.2);
  padding: 3px 6px;
  border-radius: 6px;
  border: 1px solid var(--admin-border);
}

.rank-input-label {
  font-size: 10px;
  color: var(--admin-faint);
}

.rank-num-input {
  width: 36px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  border-radius: 4px;
  padding: 2px 4px;
  color: #fff;
  font-size: 12px;
  font-family: var(--admin-font-mono);
  font-weight: 700;
  text-align: center;
}

.rank-num-input:focus {
  outline: none;
  border-color: var(--admin-primary);
}

.btn-pin.active {
  color: #38bdf8;
  background: rgba(56, 189, 248, 0.15);
  border-color: rgba(56, 189, 248, 0.3);
}

.reorder-actions {
  display: flex;
  align-items: center;
  gap: 3px;
}

.btn-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border);
  color: var(--admin-muted);
  display: grid;
  place-items: center;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-icon:hover:not(:disabled) {
  background: rgba(168, 85, 247, 0.15);
  color: #fff;
  border-color: rgba(168, 85, 247, 0.3);
}

.btn-icon:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.btn-icon.btn-remove:hover {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
}

/* ─── Preview Modal ─── */
.preview-modal-box {
  max-width: 900px;
  max-height: 90vh;
}

.preview-head-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.live-dot-pulse {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 10px #10b981;
  animation: pulseGlow 1.5s infinite;
}

.preview-head-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.device-switch {
  display: flex;
  gap: 4px;
  background: rgba(0, 0, 0, 0.3);
  padding: 3px;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
}

.device-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  background: none;
  border: none;
  color: var(--admin-muted);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.device-btn.active {
  background: rgba(168, 85, 247, 0.25);
  color: #fff;
}

.preview-modal-body {
  background: #07090e;
  padding: 24px;
  overflow-y: auto;
}

.preview-canvas {
  background: #0e121e;
  border-radius: 14px;
  border: 1px solid var(--admin-border);
  padding: 20px;
  margin: 0 auto;
  transition: max-width 0.3s ease;
}

.preview-canvas.device-desktop {
  width: 100%;
}

.preview-canvas.device-mobile {
  max-width: 360px;
  border: 2px solid rgba(255, 255, 255, 0.18);
  border-radius: 24px;
  padding: 14px;
}

.mock-client-page {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.mock-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.mock-logo {
  font-size: 13px;
  font-weight: 800;
  color: #c084fc;
  display: flex;
  align-items: center;
  gap: 6px;
}

.mock-search-bar {
  font-size: 11px;
  color: var(--admin-faint);
  background: rgba(255, 255, 255, 0.04);
  padding: 4px 10px;
  border-radius: 6px;
}

.mock-section-title {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.mock-badge {
  font-size: 14px;
  font-weight: 800;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 6px;
}

.mock-sub {
  font-size: 11px;
  color: var(--admin-faint);
}

.mock-story-cards-grid {
  display: grid;
  gap: 12px;
}

.mock-story-cards-grid.grid-desktop {
  grid-template-columns: repeat(4, 1fr);
}

.mock-story-cards-grid.grid-mobile {
  grid-template-columns: repeat(2, 1fr);
}

.mock-story-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.mock-cover-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16/10;
  overflow: hidden;
}

.mock-cover-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mock-rank-pill {
  position: absolute;
  top: 6px;
  left: 6px;
  font-size: 10px;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.75);
  color: #fff;
  font-family: var(--admin-font-mono);
}

.mock-rank-pill.rank-1 {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #000;
}

.mock-rank-pill.rank-2 {
  background: linear-gradient(135deg, #94a3b8, #64748b);
  color: #fff;
}

.mock-rank-pill.rank-3 {
  background: linear-gradient(135deg, #b45309, #78350f);
  color: #fef3c7;
}

.mock-vip-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  background: #f59e0b;
  color: #000;
  font-size: 8px;
  font-weight: 800;
  padding: 1px 4px;
  border-radius: 3px;
}

.mock-card-body {
  padding: 8px 10px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.mock-card-title {
  font-size: 12px;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mock-card-author {
  font-size: 10px;
  color: var(--admin-faint);
}

.mock-card-footer {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  color: var(--admin-faint);
  margin-top: 4px;
  padding-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

/* ─── Modal Styles ─── */
.admin-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  display: grid;
  place-items: center;
  z-index: 100;
  padding: 20px;
}

.admin-modal-box {
  background: #111420;
  border: 1px solid var(--admin-border);
  border-radius: 16px;
  width: 100%;
  max-width: 640px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.7);
  overflow: hidden;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--admin-border);
}

.modal-head h3 {
  margin: 0 0 2px 0;
  font-size: 16px;
  color: #fff;
}

.modal-head p {
  margin: 0;
  font-size: 12px;
  color: var(--admin-muted);
}

.btn-close-modal {
  background: none;
  border: none;
  color: var(--admin-muted);
  font-size: 20px;
  cursor: pointer;
}

.btn-close-modal:hover {
  color: #fff;
}

.modal-body {
  padding: 16px 20px;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-search input {
  width: 100%;
}

.modal-series-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 360px;
  overflow-y: auto;
}

.candidate-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  transition: background 0.15s ease;
}

.candidate-row:hover {
  background: rgba(255, 255, 255, 0.05);
}

.candidate-thumb {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  object-fit: cover;
}

.candidate-info {
  flex: 1;
  min-width: 0;
}

.candidate-info strong {
  display: block;
  font-size: 13px;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.candidate-meta {
  display: flex;
  gap: 6px;
  font-size: 11px;
  color: var(--admin-faint);
}

.candidate-actions {
  display: flex;
  gap: 6px;
}

.badge-already-hot {
  font-size: 11px;
  color: #10b981;
  padding: 4px 8px;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.modal-foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-top: 1px solid var(--admin-border);
  background: rgba(0, 0, 0, 0.15);
}

.modal-loading,
.modal-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 40px;
  color: var(--admin-muted);
  font-size: 13px;
}

.modal-loading i,
.modal-empty i {
  font-size: 28px;
}

/* ─── Mobile Responsiveness ─── */
@media (max-width: 900px) {
  .hot-item-card {
    flex-wrap: wrap;
  }
  .card-center-group {
    order: 3;
    width: 100%;
  }
  .card-right-group {
    order: 2;
  }
  .mock-story-cards-grid.grid-desktop {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .hot-header-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .hot-stats-pills {
    width: 100%;
    justify-content: space-between;
  }
  .manager-toolbar-card {
    flex-direction: column;
    align-items: stretch;
  }
  .toolbar-left, .toolbar-right {
    justify-content: space-between;
    width: 100%;
  }
  .hot-search {
    max-width: 100%;
    width: 100%;
  }
}
</style>

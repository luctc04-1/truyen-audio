<template>
  <div class="community-manager">
    <!-- Top Summary Banner -->
    <div class="comm-header-card">
      <div class="comm-header-info">
        <div class="badge-comm-glow">
          <i class="ri-shield-user-fill"></i>
          <span>Kiểm Duyệt & Cộng Đồng</span>
        </div>
        <h2 class="comm-title">Trung Tâm Kiểm Duyệt Nội Dung & Cộng Đồng 🛡️</h2>
        <p class="comm-desc">
          Quản lý toàn diện các bài đăng thảo luận, bình luận truyện, phản hồi của độc giả và tự động quét lọc nội dung nhạy cảm, spam trên hệ thống.
        </p>
      </div>

      <div class="comm-stats-pills">
        <div class="stat-pill">
          <span class="pill-label">Bài viết cộng đồng</span>
          <strong class="pill-val purple">{{ stats.total_posts || 0 }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Bình luận truyện</span>
          <strong class="pill-val emerald">{{ stats.total_series_comments || 0 }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Bình luận bài viết</span>
          <strong class="pill-val blue">{{ stats.total_post_comments || 0 }}</strong>
        </div>
        <div class="stat-pill">
          <span class="pill-label">Cảnh báo nhạy cảm</span>
          <strong class="pill-val amber">⚠️ {{ flaggedItemsCount }}</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Moderation Tabs -->
    <div class="comm-nav-bar">
      <div class="tabs-group">
        <button
          class="nav-tab-btn"
          :class="{ active: currentTab === 'posts' }"
          type="button"
          @click="switchTab('posts')"
        >
          <i class="ri-article-line"></i>
          <span>Bài viết Cộng đồng</span>
          <span class="tab-badge">{{ stats.total_posts || posts.length }}</span>
        </button>

        <button
          class="nav-tab-btn"
          :class="{ active: currentTab === 'series_comments' }"
          type="button"
          @click="switchTab('series_comments')"
        >
          <i class="ri-chat-3-line"></i>
          <span>Bình luận Truyện</span>
          <span class="tab-badge">{{ stats.total_series_comments || 0 }}</span>
        </button>

        <button
          class="nav-tab-btn"
          :class="{ active: currentTab === 'post_comments' }"
          type="button"
          @click="switchTab('post_comments')"
        >
          <i class="ri-discuss-line"></i>
          <span>Bình luận Bài viết</span>
          <span class="tab-badge">{{ stats.total_post_comments || 0 }}</span>
        </button>

        <button
          class="nav-tab-btn"
          :class="{ active: currentTab === 'scanner' }"
          type="button"
          @click="switchTab('scanner')"
        >
          <i class="ri-radar-line text-amber"></i>
          <span>Quét Từ Khóa Vi Phạm</span>
          <span v-if="flaggedItemsCount > 0" class="tab-badge badge-danger">{{ flaggedItemsCount }}</span>
        </button>
      </div>

      <div class="nav-extra-actions">
        <!-- View mode toggle (for posts) -->
        <div v-if="currentTab === 'posts'" class="view-mode-switch">
          <button
            class="mode-btn"
            :class="{ active: viewMode === 'table' }"
            type="button"
            title="Dạng bảng dữ liệu"
            @click="viewMode = 'table'"
          >
            <i class="ri-table-line"></i>
          </button>
          <button
            class="mode-btn"
            :class="{ active: viewMode === 'card' }"
            type="button"
            title="Dạng thẻ mạng xã hội"
            @click="viewMode = 'card'"
          >
            <i class="ri-grid-fill"></i>
          </button>
        </div>

        <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="refreshCurrentTab">
          <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
          <span>Làm mới</span>
        </button>
      </div>
    </div>

    <!-- Sub-Toolbar Filters -->
    <div v-if="currentTab !== 'scanner'" class="comm-toolbar-card">
      <div class="toolbar-left">
        <!-- Search Box -->
        <div class="search-box">
          <i class="ri-search-line"></i>
          <input
            v-model="searchKeyword"
            type="search"
            :placeholder="getSearchPlaceholder()"
            @input="debounceFetch"
          />
          <button v-if="searchKeyword" class="btn-clear-search" type="button" @click="searchKeyword = ''; fetchCurrentTabData(1)">
            <i class="ri-close-line"></i>
          </button>
        </div>

        <!-- Tag Filter Pills (For Posts tab) -->
        <div v-if="currentTab === 'posts'" class="tag-filter-pills">
          <button
            class="tag-pill-btn"
            :class="{ active: selectedTag === 'all' }"
            type="button"
            @click="setTagFilter('all')"
          >
            Tất cả
          </button>
          <button
            v-for="(label, key) in availableTags"
            :key="key"
            class="tag-pill-btn"
            :class="[{ active: selectedTag === key }, `tag-${key}`]"
            type="button"
            @click="setTagFilter(key)"
          >
            {{ label }}
          </button>
        </div>
      </div>

      <div class="toolbar-right">
        <span class="text-muted text-xs">
          Hiển thị trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} mục)
        </span>
      </div>
    </div>

    <!-- Floating Batch Action Toolbar -->
    <transition name="fade-slide">
      <div v-if="selectedIds.size > 0" class="batch-action-bar">
        <div class="batch-info">
          <i class="ri-checkbox-circle-fill text-purple"></i>
          <strong>Đã chọn {{ selectedIds.size }} mục</strong>
        </div>
        <div class="batch-actions">
          <button class="btn btn-ghost btn-xs btn-batch-danger" type="button" @click="batchDeleteSelected">
            <i class="ri-delete-bin-6-line"></i>
            <span>Xóa các mục đã chọn</span>
          </button>
          <button class="btn btn-ghost btn-xs" type="button" @click="selectedIds.clear()">
            <i class="ri-close-line"></i>
            <span>Bỏ chọn</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: BÀI VIẾT CỘNG ĐỒNG (COMMUNITY POSTS)                            -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div v-if="currentTab === 'posts'" class="tab-content-panel">
      <!-- Loading Skeleton -->
      <AdminTableSkeleton v-if="loading && !posts.length" :columns="6" :rows="6" />

      <!-- Empty State -->
      <div v-else-if="!posts.length && !loading" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-article-line"></i>
        </div>
        <h4>Không tìm thấy bài viết cộng đồng nào</h4>
        <p>Thử thay đổi từ khóa tìm kiếm hoặc chọn thẻ tag khác</p>
      </div>

      <!-- DUAL VIEW: TABLE VIEW -->
      <div v-else-if="viewMode === 'table'" class="card-panel table-panel">
        <div v-if="loading" class="table-loading-strip"></div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th width="40">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                  />
                </th>
                <th>Tác giả</th>
                <th>Chủ đề</th>
                <th>Nội dung bài đăng</th>
                <th>Truyện đính kèm</th>
                <th>Tương tác</th>
                <th>Thời gian</th>
                <th width="110">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in posts"
                :key="p.id"
                :class="{ 'row-flagged': checkIsFlagged(p.content) }"
              >
                <td>
                  <input
                    type="checkbox"
                    :checked="selectedIds.has(p.id)"
                    @change="toggleSelectItem(p.id)"
                  />
                </td>
                <td>
                  <div class="user-cell">
                    <div class="avatar-mini">
                      <img v-if="p.user?.avatar_url" :src="p.user.avatar_url" alt="" />
                      <span v-else>{{ (p.user?.username || 'U').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                      <div class="user-name-line">
                        <strong>{{ p.user?.username || 'Thành viên' }}</strong>
                        <span v-if="p.user?.is_admin" class="badge-admin-mini">Admin</span>
                      </div>
                      <small class="user-email">{{ p.user?.email || 'N/A' }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="tag-badge-solid" :class="`badge-tag-${p.tag}`">
                    {{ formatTagLabel(p.tag) }}
                  </span>
                </td>
                <td>
                  <div class="post-content-preview" @click="openPostDetail(p)">
                    <span v-if="checkIsFlagged(p.content)" class="warning-flag" title="Phát hiện từ khóa nhạy cảm">⚠️</span>
                    <span v-html="highlightSensitiveWords(p.content)"></span>
                  </div>
                </td>
                <td>
                  <div v-if="p.series" class="series-chip" :title="p.series.title">
                    <img :src="p.series.cover_url || '/android-chrome-512x512.png'" alt="" />
                    <span>{{ p.series.title }}</span>
                  </div>
                  <span v-else class="text-faint">—</span>
                </td>
                <td>
                  <div class="interaction-stats">
                    <span class="stat-like" title="Lượt thích"><i class="ri-heart-3-fill"></i> {{ p.likes_count || 0 }}</span>
                    <span class="stat-comment" title="Bình luận"><i class="ri-chat-1-fill"></i> {{ p.comments_count || 0 }}</span>
                  </div>
                </td>
                <td>
                  <span class="text-muted text-xs">{{ formatDate(p.created_at) }}</span>
                </td>
                <td>
                  <div class="row-actions">
                    <button
                      class="btn-icon"
                      type="button"
                      title="Mở Drawer kiểm tra chi tiết"
                      @click="openPostDetail(p)"
                    >
                      <i class="ri-layout-right-line"></i>
                    </button>
                    <button
                      class="btn-icon btn-danger-icon"
                      type="button"
                      title="Xóa bài viết"
                      @click="deletePost(p)"
                    >
                      <i class="ri-delete-bin-6-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- DUAL VIEW: SOCIAL CARD FEED VIEW -->
      <div v-else class="card-feed-grid">
        <div
          v-for="p in posts"
          :key="p.id"
          class="post-feed-card"
          :class="{ 'card-flagged': checkIsFlagged(p.content), 'card-selected': selectedIds.has(p.id) }"
        >
          <div class="post-card-header">
            <div class="post-author-box">
              <input
                type="checkbox"
                class="card-select-checkbox"
                :checked="selectedIds.has(p.id)"
                @change="toggleSelectItem(p.id)"
              />
              <div class="avatar-mini">
                <img v-if="p.user?.avatar_url" :src="p.user.avatar_url" alt="" />
                <span v-else>{{ (p.user?.username || 'U').charAt(0).toUpperCase() }}</span>
              </div>
              <div class="author-details">
                <div class="user-name-line">
                  <strong>{{ p.user?.username || 'Thành viên' }}</strong>
                  <span v-if="p.user?.is_admin" class="badge-admin-mini">Admin</span>
                </div>
                <small class="post-time">{{ formatDate(p.created_at) }}</small>
              </div>
            </div>

            <span class="tag-badge-solid" :class="`badge-tag-${p.tag}`">
              {{ formatTagLabel(p.tag) }}
            </span>
          </div>

          <div class="post-card-body" @click="openPostDetail(p)">
            <p class="post-text" v-html="highlightSensitiveWords(p.content)"></p>

            <div v-if="p.series" class="post-series-banner">
              <img :src="p.series.cover_url || '/android-chrome-512x512.png'" alt="" />
              <div>
                <small>Truyện gắn kèm</small>
                <strong>{{ p.series.title }}</strong>
              </div>
            </div>
          </div>

          <div class="post-card-footer">
            <div class="feed-interactions">
              <span><i class="ri-heart-3-fill text-danger"></i> {{ p.likes_count || 0 }} thích</span>
              <span><i class="ri-chat-3-line text-purple"></i> {{ p.comments_count || 0 }} bình luận</span>
            </div>

            <div class="feed-actions">
              <button class="btn btn-ghost btn-xs" type="button" @click="openPostDetail(p)">
                <i class="ri-layout-right-line"></i> Chi tiết
              </button>
              <button class="btn btn-ghost btn-xs btn-batch-danger" type="button" @click="deletePost(p)">
                <i class="ri-delete-bin-6-line"></i> Xóa
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2 & 3: BÌNH LUẬN TRUYỆN HOẶC BÌNH LUẬN BÀI VIẾT                    -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div v-else-if="currentTab === 'series_comments' || currentTab === 'post_comments'" class="tab-content-panel">
      <!-- Loading Skeleton -->
      <AdminTableSkeleton v-if="loading && !commentsList.length" :columns="6" :rows="6" />

      <!-- Empty State -->
      <div v-else-if="!commentsList.length && !loading" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-chat-smile-3-line"></i>
        </div>
        <h4>Không tìm thấy bình luận nào</h4>
        <p>Hệ thống không phát hiện bình luận phù hợp</p>
      </div>

      <!-- Table View for Comments -->
      <div v-else class="card-panel table-panel">
        <div v-if="loading" class="table-loading-strip"></div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th width="40">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                  />
                </th>
                <th>Người gửi</th>
                <th>{{ currentTab === 'series_comments' ? 'Bộ truyện' : 'Bài viết gốc' }}</th>
                <th>Nội dung bình luận</th>
                <th>Thời gian</th>
                <th width="80">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="c in commentsList"
                :key="c.id"
                :class="{ 'row-flagged': checkIsFlagged(c.content) }"
              >
                <td>
                  <input
                    type="checkbox"
                    :checked="selectedIds.has(c.id)"
                    @change="toggleSelectItem(c.id)"
                  />
                </td>
                <td>
                  <div class="user-cell">
                    <div class="avatar-mini">
                      <img v-if="c.user?.avatar_url" :src="c.user.avatar_url" alt="" />
                      <span v-else>{{ (c.user?.username || 'U').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                      <div class="user-name-line">
                        <strong>{{ c.user?.username || 'Độc giả' }}</strong>
                        <span v-if="c.user?.is_admin" class="badge-admin-mini">Admin</span>
                      </div>
                      <small class="user-email">{{ c.user?.email || 'N/A' }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div v-if="currentTab === 'series_comments'" class="target-title">
                    <i class="ri-book-open-line text-purple"></i>
                    <span>{{ c.series?.title || 'Truyện không xác định' }}</span>
                  </div>
                  <div v-else class="target-title">
                    <i class="ri-article-line text-emerald"></i>
                    <span>{{ c.post?.content?.substring(0, 40) || 'Bài viết gốc' }}...</span>
                  </div>
                </td>
                <td>
                  <div class="comment-text-wrap">
                    <span v-if="checkIsFlagged(c.content)" class="warning-flag" title="Phát hiện từ khóa nhạy cảm">⚠️</span>
                    <p class="comment-content" v-html="highlightSensitiveWords(c.content)"></p>
                  </div>
                </td>
                <td>
                  <span class="text-muted text-xs">{{ formatDate(c.created_at) }}</span>
                </td>
                <td>
                  <div class="row-actions">
                    <button
                      class="btn-icon btn-danger-icon"
                      type="button"
                      title="Xóa bình luận vi phạm"
                      @click="deleteComment(c)"
                    >
                      <i class="ri-delete-bin-6-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 4: QUÉT & LỌC TỪ KHÓA NHẠY CẢM (SMART SENSITIVE SCANNER)           -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div v-else-if="currentTab === 'scanner'" class="scanner-panel">
      <!-- Scanner Config & Control Card -->
      <div class="scanner-config-card">
        <div class="config-header">
          <div>
            <div class="badge-fire-glow">
              <i class="ri-radar-fill"></i>
              <span>Smart Keyword Scanner</span>
            </div>
            <h3>Bộ lọc Từ Khóa Nhạy Cảm & Tự Động Quét Vi Phạm</h3>
            <p class="text-muted text-xs">
              Hệ thống tự động phát hiện các bài viết và bình luận chứa từ khóa vi phạm chính sách cộng đồng (quảng cáo, link rác, từ thô tục, lừa đảo).
            </p>
          </div>

          <button class="btn btn-primary btn-sm btn-glow" type="button" :disabled="scanning" @click="runFullScan">
            <i class="ri-scan-2-line" :class="{ 'ri-spin': scanning }"></i>
            <span>{{ scanning ? 'Đang quét dữ liệu...' : 'Bắt đầu quét vi phạm' }}</span>
          </button>
        </div>

        <!-- Blacklist Tags Input/Display -->
        <div class="blacklist-box">
          <div class="blacklist-head">
            <label><strong>Danh sách từ khóa cấm / nhạy cảm:</strong></label>
            <span class="badge badge-neutral">{{ sensitiveKeywords.length }} từ khóa</span>
          </div>

          <div class="keywords-wrap">
            <span v-for="(kw, idx) in sensitiveKeywords" :key="kw" class="keyword-pill">
              {{ kw }}
              <i class="ri-close-line" @click="removeKeyword(idx)"></i>
            </span>

            <div class="add-keyword-inline">
              <input
                v-model="newKeyword"
                type="text"
                placeholder="Thêm từ khóa mới rồi nhấn Enter..."
                @keydown.enter.prevent="addKeyword"
              />
              <button class="btn btn-ghost btn-xs" type="button" @click="addKeyword">
                <i class="ri-add-line"></i> Thêm
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Scan Results Table -->
      <div class="card-panel table-panel">
        <div class="panel-head">
          <div>
            <h3>Kết quả phát hiện nội dung nghi vấn ({{ flaggedResults.length }})</h3>
            <span class="panel-subtitle">Các bài viết và bình luận bị gắn cờ cảnh báo</span>
          </div>

          <button
            v-if="flaggedResults.length"
            class="btn btn-ghost btn-sm btn-batch-danger"
            type="button"
            @click="cleanAllFlagged"
          >
            <i class="ri-delete-bin-7-line"></i>
            <span>Xóa sạch toàn bộ vi phạm</span>
          </button>
        </div>

        <div v-if="!flaggedResults.length" class="empty-state">
          <div class="empty-icon-wrap emerald-icon">
            <i class="ri-shield-check-fill"></i>
          </div>
          <h4>Không phát hiện vi phạm nào!</h4>
          <p>Tất cả bài viết và bình luận hiện tại đều sạch và phù hợp tiêu chuẩn cộng đồng.</p>
        </div>

        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Loại</th>
                <th>Người gửi</th>
                <th>Từ khóa vi phạm</th>
                <th>Nội dung trích đoạn</th>
                <th>Thời gian</th>
                <th width="100">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in flaggedResults" :key="item.id" class="row-flagged">
                <td>
                  <span class="badge" :class="item.type === 'post' ? 'badge-tag-thaoluan' : 'badge-neutral'">
                    {{ item.type === 'post' ? 'Bài viết' : 'Bình luận' }}
                  </span>
                </td>
                <td>
                  <div class="user-cell">
                    <strong>{{ item.user?.username || 'N/A' }}</strong>
                  </div>
                </td>
                <td>
                  <span class="badge-flagged-keyword">{{ item.matchedKeyword }}</span>
                </td>
                <td>
                  <p class="comment-content" v-html="highlightSensitiveWords(item.content)"></p>
                </td>
                <td>
                  <span class="text-muted text-xs">{{ formatDate(item.created_at) }}</span>
                </td>
                <td>
                  <button class="btn btn-ghost btn-xs btn-batch-danger" type="button" @click="deleteFlaggedItem(item)">
                    <i class="ri-delete-bin-6-line"></i> Xóa
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination Controls (For Tabs 1, 2, 3) -->
    <div v-if="currentTab !== 'scanner' && pagination.total > 0" class="pagination-footer">
      <span class="page-info">
        Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} mục)
      </span>
      <div class="pagination-controls">
        <button
          class="btn btn-ghost btn-sm"
          type="button"
          :disabled="pagination.current_page <= 1 || loading"
          @click="fetchCurrentTabData(pagination.current_page - 1)"
        >
          <i class="ri-arrow-left-s-line"></i> Trước
        </button>
        <button
          class="btn btn-ghost btn-sm"
          type="button"
          :disabled="pagination.current_page >= pagination.last_page || loading"
          @click="fetchCurrentTabData(pagination.current_page + 1)"
        >
          Sau <i class="ri-arrow-right-s-line"></i>
        </button>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- SLIDING SIDE DRAWER: CHI TIẾT BÀI VIẾT, LIKES LIST & BÌNH LUẬN        -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div
      v-if="detailDrawerOpen"
      class="admin-drawer-overlay"
      @click.self="detailDrawerOpen = false"
    >
      <div class="admin-side-drawer">
        <!-- Drawer Header with Quick Actions -->
        <div class="drawer-header">
          <div class="drawer-user-info">
            <div class="avatar-mini drawer-avatar">
              <img v-if="inspectingPost?.user?.avatar_url" :src="inspectingPost.user.avatar_url" alt="" />
              <span v-else>{{ (inspectingPost?.user?.username || 'U').charAt(0).toUpperCase() }}</span>
            </div>
            <div>
              <div class="user-name-line">
                <strong class="drawer-user-name">{{ inspectingPost?.user?.username || 'Thành viên' }}</strong>
                <span v-if="inspectingPost?.user?.is_admin" class="badge-admin-mini">Admin</span>
              </div>
              <small class="drawer-user-sub">{{ inspectingPost?.user?.email }} • {{ formatDate(inspectingPost?.created_at) }}</small>
            </div>
          </div>

          <div class="drawer-head-actions">
            <a
              href="/community"
              target="_blank"
              class="btn-drawer-action"
              title="Xem bài viết này ngoài trang người dùng"
            >
              <i class="ri-external-link-line"></i>
              <span>Xem trên Web</span>
            </a>
            <button class="btn-close-drawer" type="button" title="Đóng (Esc)" @click="detailDrawerOpen = false">
              <i class="ri-close-line"></i>
            </button>
          </div>
        </div>

        <!-- Drawer Body Scrollable Content -->
        <div class="drawer-body">
          <!-- Main Post Details Card -->
          <div class="drawer-post-card">
            <div class="drawer-badge-row">
              <span class="tag-badge-solid" :class="`badge-tag-${inspectingPost?.tag}`">
                {{ formatTagLabel(inspectingPost?.tag) }}
              </span>
              <span class="stat-like"><i class="ri-heart-3-fill text-danger"></i> {{ inspectingPost?.likes_count || 0 }} thích</span>
            </div>

            <p class="drawer-post-text" v-html="highlightSensitiveWords(inspectingPost?.content || '')"></p>

            <div v-if="inspectingPost?.series" class="drawer-series-banner">
              <img :src="inspectingPost.series.cover_url || '/android-chrome-512x512.png'" alt="" />
              <div class="drawer-series-info">
                <small>Truyện được gắn kèm:</small>
                <strong>{{ inspectingPost.series.title }}</strong>
                <span class="category-pill">{{ inspectingPost.series.category }}</span>
              </div>
            </div>
          </div>

          <!-- Section: Danh Sách Người Đã Thích (Likes List) -->
          <div class="drawer-likes-section">
            <div class="section-title-row">
              <i class="ri-heart-3-fill text-danger"></i>
              <strong>Người đã thích bài viết ({{ inspectingPost?.likes?.length || inspectingPost?.likes_count || 0 }})</strong>
            </div>

            <div v-if="!inspectingPost?.likes?.length" class="empty-likes-tip">
              <span>Chưa có ai thích bài viết này</span>
            </div>

            <div v-else class="likes-avatar-group">
              <div
                v-for="like in inspectingPost.likes"
                :key="like.id"
                class="like-user-chip"
                :title="like.user?.username || 'Thành viên'"
              >
                <div class="avatar-mini chip-avatar">
                  <img v-if="like.user?.avatar_url" :src="like.user.avatar_url" alt="" />
                  <span v-else>{{ (like.user?.username || 'U').charAt(0).toUpperCase() }}</span>
                </div>
                <span>{{ like.user?.username || 'Thành viên' }}</span>
              </div>
            </div>
          </div>

          <!-- Section: Chuỗi Bình Luận Thảo Luận (Comments Thread) -->
          <div class="drawer-comments-section">
            <div class="section-title-row">
              <i class="ri-chat-3-fill text-purple"></i>
              <strong>Bình luận & Phản hồi ({{ inspectingPost?.comments?.length || inspectingPost?.comments_count || 0 }})</strong>
            </div>

            <div v-if="!inspectingPost?.comments?.length" class="empty-thread">
              <i class="ri-chat-smile-2-line"></i>
              <span>Bài viết này chưa có bình luận nào.</span>
            </div>

            <div v-else class="drawer-thread-list">
              <div
                v-for="c in inspectingPost.comments"
                :key="c.id"
                class="drawer-comment-item"
                :class="{ 'row-flagged': checkIsFlagged(c.content) }"
              >
                <div class="avatar-mini comment-avatar">
                  <img v-if="c.user?.avatar_url" :src="c.user.avatar_url" alt="" />
                  <span v-else>{{ (c.user?.username || 'U').charAt(0).toUpperCase() }}</span>
                </div>

                <div class="drawer-comment-main">
                  <div class="drawer-comment-head">
                    <div class="user-name-line">
                      <strong>{{ c.user?.username || 'Thành viên' }}</strong>
                      <span v-if="c.user?.is_admin" class="badge-admin-mini">Admin</span>
                    </div>
                    <small>{{ formatDate(c.created_at) }}</small>
                  </div>
                  <p class="drawer-comment-text" v-html="highlightSensitiveWords(c.content)"></p>
                </div>

                <button
                  class="btn-icon btn-danger-icon"
                  type="button"
                  title="Xóa bình luận phản hồi này"
                  @click="deleteCommentInThread(c)"
                >
                  <i class="ri-delete-bin-6-line"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Drawer Footer -->
        <div class="drawer-footer">
          <button class="btn btn-ghost btn-sm btn-batch-danger" type="button" @click="deletePost(inspectingPost); detailDrawerOpen = false">
            <i class="ri-delete-bin-6-line"></i> Xóa toàn bộ bài viết
          </button>
          <button class="btn btn-ghost btn-sm" type="button" @click="detailDrawerOpen = false">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import AdminService from '@/services/AdminService'
import AdminTableSkeleton from './AdminTableSkeleton.vue'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const toast = useToastStore()

// State
const currentTab = ref('posts') // 'posts' | 'series_comments' | 'post_comments' | 'scanner'
const viewMode = ref('table') // 'table' | 'card'
const loading = ref(false)
const searchKeyword = ref('')
const selectedTag = ref('all')
let debounceTimer = null

// Selection & Batch Actions
const selectedIds = reactive(new Set())

// Stats
const stats = reactive({
  total_posts: 0,
  total_series_comments: 0,
  total_post_comments: 0,
  posts_by_tag: {},
})

// Data Lists
const posts = ref([])
const commentsList = ref([])
const availableTags = ref({
  thaoluan: '🔥 Thảo luận',
  dexuat: '🎧 Đề xuất',
  hoidap: '❓ Hỏi đáp',
  baoloi: '🐞 Báo lỗi',
  gantruyen: '📖 Gắn truyện',
})

// Pagination
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
})

// Sliding Side Drawer State
const detailDrawerOpen = ref(false)
const inspectingPost = ref(null)

// Smart Scanner State
const scanning = ref(false)
const sensitiveKeywords = ref([
  'lừa đảo', 'đánh bạc', 'cờ bạc', 'hack', 'lộ clip', 'zalo', 'telegram',
  'tục', 'cmm', 'dcm', 'đm', 'dm', 'fake', 'link rác', 'mua bán nick'
])
const newKeyword = ref('')
const flaggedResults = ref([])

// ─── Computed ───────────────────────────────────────────────────────────────
const isAllSelected = computed(() => {
  const currentList = currentTab.value === 'posts' ? posts.value : commentsList.value
  if (!currentList.length) return false
  return currentList.every((item) => selectedIds.has(item.id))
})

const isIndeterminate = computed(() => {
  const currentList = currentTab.value === 'posts' ? posts.value : commentsList.value
  if (!currentList.length) return false
  const count = currentList.filter((item) => selectedIds.has(item.id)).length
  return count > 0 && count < currentList.length
})

const flaggedItemsCount = computed(() => {
  let count = 0
  posts.value.forEach((p) => {
    if (checkIsFlagged(p.content)) count++
  })
  commentsList.value.forEach((c) => {
    if (checkIsFlagged(c.content)) count++
  })
  return count
})

// ─── Sensitive Word Highlighter & Check ─────────────────────────────────────
function checkIsFlagged(text) {
  if (!text) return false
  const lower = text.toLowerCase()
  return sensitiveKeywords.value.some((kw) => lower.includes(kw.toLowerCase()))
}

function highlightSensitiveWords(text) {
  if (!text) return ''
  let sanitized = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

  sensitiveKeywords.value.forEach((kw) => {
    if (!kw.trim()) return
    const regex = new RegExp(`(${kw})`, 'gi')
    sanitized = sanitized.replace(regex, '<mark class="highlight-sensitive">$1</mark>')
  })
  return sanitized
}

function addKeyword() {
  const kw = newKeyword.value.trim().toLowerCase()
  if (kw && !sensitiveKeywords.value.includes(kw)) {
    sensitiveKeywords.value.push(kw)
    newKeyword.value = ''
    toast.success(`Đã thêm từ khóa "${kw}" vào danh sách quét`)
    runFullScan()
  }
}

function removeKeyword(index) {
  const [removed] = sensitiveKeywords.value.splice(index, 1)
  toast.info(`Đã gỡ từ khóa "${removed}"`)
  runFullScan()
}

function runFullScan() {
  scanning.value = true
  flaggedResults.value = []

  setTimeout(() => {
    // Scan loaded posts
    posts.value.forEach((p) => {
      sensitiveKeywords.value.forEach((kw) => {
        if (p.content?.toLowerCase().includes(kw.toLowerCase())) {
          flaggedResults.value.push({
            id: p.id,
            type: 'post',
            user: p.user,
            content: p.content,
            matchedKeyword: kw,
            created_at: p.created_at,
          })
        }
      })
    })

    // Scan loaded comments
    commentsList.value.forEach((c) => {
      sensitiveKeywords.value.forEach((kw) => {
        if (c.content?.toLowerCase().includes(kw.toLowerCase())) {
          flaggedResults.value.push({
            id: c.id,
            type: 'comment',
            user: c.user,
            content: c.content,
            matchedKeyword: kw,
            created_at: c.created_at,
          })
        }
      })
    })

    scanning.value = false
    toast.info(`Quét hoàn tất: Phát hiện ${flaggedResults.value.length} nội dung nghi vấn`)
  }, 400)
}

async function cleanAllFlagged() {
  if (!confirm(`Bạn có chắc chắn muốn xóa toàn bộ ${flaggedResults.value.length} mục vi phạm?`)) return

  try {
    for (const item of flaggedResults.value) {
      if (item.type === 'post') {
        await AdminService.deleteCommunityPost(item.id)
      } else {
        await AdminService.deleteComment(item.id, 'series')
      }
    }
    toast.success('Đã dọn dẹp toàn bộ nội dung vi phạm!')
    flaggedResults.value = []
    fetchStats()
    fetchCurrentTabData(1)
  } catch (err) {
    toast.error('Có lỗi xảy ra khi dọn dẹp vi phạm')
  }
}

async function deleteFlaggedItem(item) {
  try {
    if (item.type === 'post') {
      await AdminService.deleteCommunityPost(item.id)
    } else {
      await AdminService.deleteComment(item.id, 'series')
    }
    flaggedResults.value = flaggedResults.value.filter((i) => i.id !== item.id)
    toast.success('Đã xóa nội dung vi phạm!')
    fetchStats()
    fetchCurrentTabData(1)
  } catch (err) {
    toast.error('Không thể xóa nội dung này')
  }
}

// ─── Tab Switching ──────────────────────────────────────────────────────────
function switchTab(tabName) {
  currentTab.value = tabName
  selectedIds.clear()
  searchKeyword.value = ''
  if (tabName === 'scanner') {
    runFullScan()
  } else {
    fetchCurrentTabData(1)
  }
}

function refreshCurrentTab() {
  fetchStats()
  if (currentTab.value === 'scanner') {
    runFullScan()
  } else {
    fetchCurrentTabData(pagination.current_page)
  }
}

function setTagFilter(tag) {
  selectedTag.value = tag
  fetchCurrentTabData(1)
}

function getSearchPlaceholder() {
  if (currentTab.value === 'posts') return 'Tìm nội dung bài đăng, tác giả, truyện...'
  if (currentTab.value === 'series_comments') return 'Tìm nội dung bình luận, người gửi...'
  return 'Tìm bình luận trong bài viết...'
}

function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchCurrentTabData(1)
  }, 300)
}

// ─── Fetch Data Handlers ────────────────────────────────────────────────────
async function fetchStats() {
  try {
    const res = await AdminService.getCommunityStats()
    const payload = extractApiPayload(res)
    Object.assign(stats, payload || {})
  } catch (err) {
    console.error('Failed to load community stats', err)
  }
}

async function fetchCurrentTabData(page = 1) {
  loading.value = true
  try {
    if (currentTab.value === 'posts') {
      const params = {
        page,
        per_page: pagination.per_page,
        tag: selectedTag.value,
      }
      if (searchKeyword.value) params.search = searchKeyword.value

      const res = await AdminService.getCommunityPosts(params)
      const payload = extractApiPayload(res)
      posts.value = payload?.items || []
      if (payload?.tags) availableTags.value = payload.tags
      updatePagination(payload?.pagination)
    } else {
      const type = currentTab.value === 'series_comments' ? 'series' : 'community'
      const params = {
        page,
        per_page: pagination.per_page,
        type,
      }
      if (searchKeyword.value) params.search = searchKeyword.value

      const res = await AdminService.getComments(params)
      const payload = extractApiPayload(res)
      commentsList.value = payload?.items || []
      updatePagination(payload?.pagination)
    }
  } catch (err) {
    console.error('Failed to fetch tab data', err)
    toast.error('Không thể tải dữ liệu kiểm duyệt')
  } finally {
    loading.value = false
  }
}

function updatePagination(meta) {
  if (meta) {
    pagination.current_page = meta.current_page || 1
    pagination.last_page = meta.last_page || 1
    pagination.total = meta.total || 0
  }
}

// ─── Selection & Batch Actions ──────────────────────────────────────────────
function toggleSelectItem(id) {
  if (selectedIds.has(id)) {
    selectedIds.delete(id)
  } else {
    selectedIds.add(id)
  }
}

function toggleSelectAll() {
  const currentList = currentTab.value === 'posts' ? posts.value : commentsList.value
  if (isAllSelected.value) {
    selectedIds.clear()
  } else {
    currentList.forEach((item) => selectedIds.add(item.id))
  }
}

async function batchDeleteSelected() {
  if (!selectedIds.size) return
  if (!confirm(`Bạn có chắc chắn muốn xóa ${selectedIds.size} mục đã chọn?`)) return

  const ids = Array.from(selectedIds)
  try {
    if (currentTab.value === 'posts') {
      await AdminService.batchDeleteCommunityPosts(ids)
      posts.value = posts.value.filter((p) => !selectedIds.has(p.id))
    } else {
      const type = currentTab.value === 'series_comments' ? 'series' : 'community'
      await AdminService.batchDeleteComments(ids, type)
      commentsList.value = commentsList.value.filter((c) => !selectedIds.has(c.id))
    }
    selectedIds.clear()
    toast.success(`Đã xóa thành công ${ids.length} mục!`)
    fetchStats()
  } catch (err) {
    console.error('Batch delete failed', err)
    toast.error('Có lỗi xảy ra khi xóa hàng loạt')
  }
}

// ─── Single Item Actions ────────────────────────────────────────────────────
async function deletePost(post) {
  if (!confirm('Bạn có chắc chắn muốn xóa bài viết này cùng toàn bộ bình luận liên quan?')) return

  try {
    await AdminService.deleteCommunityPost(post.id)
    posts.value = posts.value.filter((p) => p.id !== post.id)
    selectedIds.delete(post.id)
    toast.success('Đã xóa bài viết thành công')
    fetchStats()
  } catch (err) {
    console.error('Failed to delete post', err)
    toast.error('Không thể xóa bài viết này')
  }
}

async function deleteComment(comment) {
  if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) return

  try {
    const type = currentTab.value === 'series_comments' ? 'series' : 'community'
    await AdminService.deleteComment(comment.id, type)
    commentsList.value = commentsList.value.filter((c) => c.id !== comment.id)
    selectedIds.delete(comment.id)
    toast.success('Đã xóa bình luận')
    fetchStats()
  } catch (err) {
    console.error('Failed to delete comment', err)
    toast.error('Không thể xóa bình luận')
  }
}

async function openPostDetail(post) {
  try {
    const res = await AdminService.getCommunityPostDetail(post.id)
    const payload = extractApiPayload(res)
    inspectingPost.value = payload || post
    detailDrawerOpen.value = true
  } catch (err) {
    inspectingPost.value = post
    detailDrawerOpen.value = true
  }
}

async function deleteCommentInThread(comment) {
  if (!confirm('Bạn có chắc muốn xóa bình luận này trong bài viết?')) return

  try {
    await AdminService.deleteComment(comment.id, 'community')
    if (inspectingPost.value?.comments) {
      inspectingPost.value.comments = inspectingPost.value.comments.filter((c) => c.id !== comment.id)
      inspectingPost.value.comments_count = Math.max((inspectingPost.value.comments_count || 1) - 1, 0)
    }
    toast.success('Đã xóa bình luận phản hồi')
    fetchStats()
  } catch (err) {
    toast.error('Không thể xóa bình luận này')
  }
}

// ─── Formatters ─────────────────────────────────────────────────────────────
function formatTagLabel(tag) {
  return availableTags.value[tag] || '🔥 Thảo luận'
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

function onKeydown(e) {
  if (e.key === 'Escape' && detailDrawerOpen.value) {
    detailDrawerOpen.value = false
  }
}

onMounted(() => {
  fetchStats()
  fetchCurrentTabData(1)
  window.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

<style scoped>
.community-manager {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
  box-sizing: border-box;
}

/* ─── Header Card ─── */
.comm-header-card {
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

.comm-header-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #3b82f6, #a855f7, #ec4899);
}

.badge-comm-glow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  background: rgba(59, 130, 246, 0.12);
  border: 1px solid rgba(59, 130, 246, 0.25);
  color: #60a5fa;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.comm-title {
  margin: 0 0 6px 0;
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}

.comm-desc {
  margin: 0;
  font-size: 13px;
  color: var(--admin-muted);
  max-width: 600px;
  line-height: 1.5;
}

.comm-stats-pills {
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
  min-width: 110px;
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
.pill-val.blue { color: #60a5fa; }
.pill-val.amber { color: #fbbf24; }

/* ─── Navigation Tabs Bar ─── */
.comm-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 8px 14px;
  backdrop-filter: blur(16px);
  flex-wrap: wrap;
}

.tabs-group {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.nav-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 10px;
  background: transparent;
  border: 1px solid transparent;
  color: var(--admin-muted);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-tab-btn:hover {
  background: rgba(255, 255, 255, 0.04);
  color: #fff;
}

.nav-tab-btn.active {
  background: rgba(168, 85, 247, 0.18);
  border-color: rgba(168, 85, 247, 0.4);
  color: #fff;
  box-shadow: 0 4px 12px rgba(168, 85, 247, 0.15);
}

.tab-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  color: #c084fc;
}

.tab-badge.badge-danger {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.nav-extra-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.view-mode-switch {
  display: flex;
  gap: 2px;
  background: rgba(0, 0, 0, 0.25);
  padding: 2px;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
}

.mode-btn {
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
}

.mode-btn.active {
  background: rgba(168, 85, 247, 0.3);
  color: #fff;
}

/* ─── Sub-Toolbar Card ─── */
.comm-toolbar-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 10px 16px;
  backdrop-filter: blur(20px);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  flex: 1;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  padding: 6px 12px;
  min-width: 260px;
}

.search-box input {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 13px;
  outline: none;
  width: 100%;
}

.btn-clear-search {
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
}

.tag-filter-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.tag-pill-btn {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border);
  color: var(--admin-muted);
  cursor: pointer;
  transition: all 0.15s ease;
}

.tag-pill-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.tag-pill-btn.active {
  background: #a855f7;
  color: #fff;
  border-color: #a855f7;
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

/* ─── Table Panel & Elements ─── */
.table-panel {
  position: relative;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 16px;
  padding: 16px;
  box-shadow: var(--admin-glass-shadow);
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
  gap: 10px;
}

.avatar-mini {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  color: white;
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 12px;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-mini img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name-line {
  display: flex;
  align-items: center;
  gap: 6px;
}

.user-name-line strong {
  font-size: 13px;
  color: #f8fafc;
}

.badge-admin-mini {
  font-size: 9px;
  font-weight: 800;
  padding: 1px 4px;
  border-radius: 4px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff;
}

.user-email {
  font-size: 11px;
  color: var(--admin-faint);
  display: block;
}

.tag-badge-solid {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  display: inline-block;
  white-space: nowrap;
}

.badge-tag-thaoluan { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
.badge-tag-dexuat { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
.badge-tag-hoidap { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
.badge-tag-baoloi { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
.badge-tag-gantruyen { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

.post-content-preview {
  font-size: 13px;
  color: #e2e8f0;
  max-width: 320px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  cursor: pointer;
  line-height: 1.4;
}

.post-content-preview:hover {
  color: #c084fc;
}

.series-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 2px 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border);
  border-radius: 8px;
  font-size: 11px;
  color: #cbd5e1;
  max-width: 160px;
}

.series-chip img {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  object-fit: cover;
}

.series-chip span {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.interaction-stats {
  display: flex;
  gap: 8px;
  font-size: 12px;
}

.stat-like { color: #f43f5e; display: inline-flex; align-items: center; gap: 3px; }
.stat-comment { color: #a855f7; display: inline-flex; align-items: center; gap: 3px; }

.target-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #cbd5e1;
  max-width: 180px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.comment-content {
  font-size: 13px;
  color: #e2e8f0;
  margin: 0;
  max-width: 380px;
  line-height: 1.4;
}

.row-flagged {
  background: rgba(239, 68, 68, 0.04);
}

.warning-flag {
  margin-right: 4px;
}

:deep(mark.highlight-sensitive) {
  background: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  padding: 1px 4px;
  border-radius: 4px;
  border: 1px solid rgba(239, 68, 68, 0.4);
  font-weight: 700;
}

/* ─── Card Feed View ─── */
.card-feed-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 16px;
}

.post-feed-card {
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  backdrop-filter: blur(16px);
  box-shadow: var(--admin-glass-shadow);
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.post-feed-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.15);
}

.post-feed-card.card-flagged {
  border-color: rgba(239, 68, 68, 0.4);
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.06) 0%, rgba(15, 18, 28, 0.75) 100%);
}

.post-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.post-author-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.author-details strong {
  display: block;
  font-size: 13px;
  color: #fff;
}

.post-time {
  font-size: 10px;
  color: var(--admin-faint);
}

.post-card-body {
  cursor: pointer;
}

.post-text {
  font-size: 13px;
  color: #e2e8f0;
  margin: 0;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-series-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--admin-border);
  border-radius: 8px;
  margin-top: 10px;
}

.post-series-banner img {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  object-fit: cover;
}

.post-series-banner strong {
  display: block;
  font-size: 12px;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.post-series-banner small {
  font-size: 10px;
  color: var(--admin-faint);
}

.post-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.feed-interactions {
  display: flex;
  gap: 10px;
  font-size: 11px;
  color: var(--admin-muted);
}

.feed-actions {
  display: flex;
  gap: 6px;
}

/* ─── Scanner Tab Styles ─── */
.scanner-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.scanner-config-card {
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  backdrop-filter: blur(16px);
}

.config-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.config-header h3 {
  margin: 4px 0 2px 0;
  font-size: 16px;
  color: #fff;
}

.blacklist-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.blacklist-head {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #cbd5e1;
}

.keywords-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}

.keyword-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #f87171;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.keyword-pill i {
  cursor: pointer;
  font-size: 12px;
}

.keyword-pill i:hover {
  color: #fff;
}

.add-keyword-inline {
  display: flex;
  gap: 6px;
}

.add-keyword-inline input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  padding: 4px 10px;
  color: #fff;
  font-size: 12px;
  outline: none;
  min-width: 240px;
}

.badge-flagged-keyword {
  font-size: 11px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
  background: #ef4444;
  color: #fff;
}

.emerald-icon {
  background: rgba(16, 185, 129, 0.15) !important;
  color: #34d399 !important;
}

/* ─── SLIDING SIDE DRAWER ─── */
.admin-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  z-index: 120;
  display: flex;
  justify-content: flex-end;
  animation: fadeInOverlay 0.25s ease forwards;
}

@keyframes fadeInOverlay {
  from { opacity: 0; }
  to { opacity: 1; }
}

.admin-side-drawer {
  width: 100%;
  max-width: 640px;
  height: 100%;
  background: #0f121d;
  border-left: 1px solid var(--admin-border);
  display: flex;
  flex-direction: column;
  box-shadow: -16px 0 48px rgba(0, 0, 0, 0.8);
  animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideInRight {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid var(--admin-border);
  background: rgba(15, 18, 28, 0.95);
}

.drawer-user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.drawer-avatar {
  width: 40px;
  height: 40px;
  font-size: 14px;
}

.drawer-user-name {
  font-size: 15px;
  color: #fff;
}

.drawer-user-sub {
  color: var(--admin-faint);
  font-size: 11px;
  display: block;
}

.drawer-head-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-drawer-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.3);
  color: #c084fc;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-drawer-action:hover {
  background: rgba(168, 85, 247, 0.25);
  color: #fff;
}

.btn-close-drawer {
  background: none;
  border: none;
  color: var(--admin-muted);
  font-size: 24px;
  cursor: pointer;
  display: grid;
  place-items: center;
  padding: 4px;
  border-radius: 6px;
}

.btn-close-drawer:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
}

.drawer-body {
  padding: 20px 24px;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.drawer-post-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.drawer-badge-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.drawer-post-text {
  font-size: 14px;
  color: #f1f5f9;
  line-height: 1.6;
  margin: 0;
  white-space: pre-wrap;
}

.drawer-series-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--admin-border);
  border-radius: 10px;
}

.drawer-series-banner img {
  width: 36px;
  height: 36px;
  border-radius: 6px;
  object-fit: cover;
}

.drawer-series-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.drawer-series-info small {
  font-size: 10px;
  color: var(--admin-faint);
}

.drawer-series-info strong {
  font-size: 13px;
  color: #fff;
}

.category-pill {
  font-size: 10px;
  padding: 1px 6px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.06);
  color: var(--admin-muted);
  width: fit-content;
  margin-top: 2px;
}

/* Likes Section */
.drawer-likes-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 14px 16px;
}

.section-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #fff;
}

.empty-likes-tip {
  font-size: 12px;
  color: var(--admin-faint);
  padding: 6px 0;
}

.likes-avatar-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.like-user-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px 4px 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--admin-border);
  border-radius: 20px;
  font-size: 11px;
  color: #cbd5e1;
}

.chip-avatar {
  width: 20px;
  height: 20px;
  font-size: 9px;
  border-radius: 50%;
}

/* Comments Section */
.drawer-comments-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-thread {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--admin-muted);
  font-size: 12px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 10px;
  justify-content: center;
}

.drawer-thread-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.drawer-comment-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.025);
  border: 1px solid var(--admin-border);
  border-radius: 12px;
}

.comment-avatar {
  width: 32px;
  height: 32px;
  font-size: 11px;
}

.drawer-comment-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.drawer-comment-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.drawer-comment-head strong {
  font-size: 12px;
  color: #fff;
}

.drawer-comment-head small {
  font-size: 10px;
  color: var(--admin-faint);
}

.drawer-comment-text {
  font-size: 12px;
  color: #e2e8f0;
  margin: 0;
  line-height: 1.4;
}

.drawer-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid var(--admin-border);
  background: rgba(15, 18, 28, 0.95);
}

/* ─── Common UI Utilities ─── */
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

.btn-icon:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.btn-danger-icon:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border-color: rgba(239, 68, 68, 0.4);
}

.row-actions {
  display: flex;
  gap: 4px;
}

.pagination-footer {
  padding: 12px 16px;
  background: var(--admin-card-bg);
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.empty-state {
  padding: 50px 20px;
  text-align: center;
  color: var(--admin-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.empty-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: rgba(168, 85, 247, 0.12);
  color: #c084fc;
  display: grid;
  place-items: center;
  font-size: 28px;
}

.empty-state h4 {
  margin: 0;
  font-size: 15px;
  color: #fff;
}

.empty-state p {
  margin: 0;
  font-size: 12px;
}

/* ─── Responsive ─── */
@media (max-width: 768px) {
  .comm-header-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .comm-stats-pills {
    width: 100%;
    justify-content: space-between;
  }
  .config-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .admin-side-drawer {
    max-width: 100%;
  }
}
</style>

<template>
  <div class="series-manager">
    <!-- Controls Toolbar -->
    <div class="manager-toolbar">
      <div class="search-box">
        <i class="ri-search-line"></i>
        <input
          v-model="filters.search"
          type="search"
          placeholder="Tìm tên truyện, tác giả, người đọc..."
          @input="debounceFetch"
        />
        <button v-if="filters.search" class="btn-clear-search" type="button" @click="filters.search = ''; fetchSeries(1)">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="filter-group">
        <select v-model="filters.category" @change="fetchSeries(1)">
          <option value="all">Tất cả thể loại</option>
          <option v-for="cat in categories" :key="cat.category" :value="cat.category">
            {{ cat.category }} ({{ cat.count }})
          </option>
        </select>

        <select v-model="filters.is_premium" @change="fetchSeries(1)">
          <option value="">Tất cả gói</option>
          <option value="true">Chỉ VIP</option>
          <option value="false">Miễn phí</option>
        </select>

        <select v-model="filters.is_hot" @change="fetchSeries(1)">
          <option value="">Tất cả Hot</option>
          <option value="true">Truyện Hot 🔥</option>
          <option value="false">Bình thường</option>
        </select>

        <button class="btn btn-primary btn-glow" type="button" @click="openCreateModal">
          <i class="ri-add-line"></i>
          <span>Thêm truyện mới</span>
        </button>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card-panel table-panel">
      <!-- Loading Progress Indicator Strip -->
      <div v-if="loading && seriesList.length" class="table-loading-strip"></div>

      <div class="panel-head">
        <div>
          <h3>Kho truyện audio</h3>
          <span class="panel-subtitle">Tổng cộng {{ pagination.total || 0 }} bộ truyện</span>
        </div>

        <div class="panel-head-actions">
          <!-- Segmented View Switcher: Table vs Visual Gallery Grid -->
          <div class="view-switcher-group">
            <button
              class="view-switch-btn"
              :class="{ active: viewMode === 'table' }"
              type="button"
              title="Xem dạng Bảng dữ liệu chi tiết"
              @click="viewMode = 'table'"
            >
              <i class="ri-table-line"></i>
              <span>Bảng</span>
            </button>
            <button
              class="view-switch-btn"
              :class="{ active: viewMode === 'grid' }"
              type="button"
              title="Xem dạng Thẻ khối Visual Gallery"
              @click="viewMode = 'grid'"
            >
              <i class="ri-grid-fill"></i>
              <span>Thẻ khối</span>
            </button>
          </div>

          <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchSeries(pagination.current_page)">
            <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
            <span>Làm mới</span>
          </button>
        </div>
      </div>

      <!-- Skeleton Loading State for initial fetch -->
      <AdminTableSkeleton v-if="loading && !seriesList.length" :columns="8" :rows="8" />

      <!-- Empty State -->
      <div v-else-if="!seriesList.length" class="empty-state">
        <div class="empty-icon-wrap">
          <i class="ri-book-3-line"></i>
        </div>
        <h4>Không tìm thấy truyện nào</h4>
        <p>Thử đổi từ khóa tìm kiếm hoặc bộ lọc thể loại khác</p>
      </div>

      <!-- 1. Table View Mode -->
      <div v-else-if="viewMode === 'table'" class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Bìa & Tên truyện</th>
              <th>Thể loại</th>
              <th>Số tập</th>
              <th>Lượt nghe</th>
              <th>Đánh giá</th>
              <th>Gói</th>
              <th>Hot</th>
              <th class="text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in seriesList" :key="item.id">
              <td>
                <div class="story-profile clickable-profile" title="Bấm để xem chi tiết truyện" @click="openDetailModal(item)">
                  <div class="story-cover-wrap">
                    <img :src="item.cover_url || '/android-chrome-512x512.png'" :alt="item.title" loading="lazy" />
                  </div>
                  <div class="story-info">
                    <strong class="story-title" :title="item.title">{{ item.title }}</strong>
                    <div class="story-meta">
                      <span><i class="ri-user-voice-line"></i> {{ item.author || 'Đang cập nhật' }}</span>
                      <span v-if="item.narrator">• {{ item.narrator }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge badge-neutral">{{ item.category || 'Chưa phân loại' }}</span>
              </td>
              <td>
                <div class="episodes-pill" :title="`Đã upload: ${item.episodes_count || 0} tập / Mục tiêu: ${item.total_episodes || item.episodes_count || 0} tập`">
                  <span class="text-strong">{{ formatSeriesEpisodeCount(item) }}</span>
                  <small v-if="item.episodes_count !== undefined && item.total_episodes && item.episodes_count !== item.total_episodes && !isSingleOrZeroEpisodeSeries(item)" class="ep-diff">
                    ({{ item.episodes_count }} file)
                  </small>
                </div>
              </td>
              <td>
                <span class="text-muted">{{ formatNumber(item.total_listens || item.listen_count || 0) }}</span>
              </td>
              <td>
                <div class="rating-pill">
                  <i class="ri-star-fill"></i>
                  <span>{{ Number(item.average_rating || 5).toFixed(1) }}</span>
                </div>
              </td>
              <td>
                <button
                  class="badge-toggle"
                  :class="item.is_premium ? 'badge-vip' : 'badge-free'"
                  type="button"
                  title="Bấm để chuyển đổi VIP"
                  @click="togglePremium(item)"
                >
                  {{ item.is_premium ? 'VIP' : 'Free' }}
                </button>
              </td>
              <td>
                <label class="switch-toggle" title="Bật/tắt truyện Hot">
                  <input type="checkbox" :checked="item.is_hot" @change="toggleHot(item)" />
                  <span class="slider"></span>
                </label>
              </td>
              <td class="text-right">
                <div class="row-actions">
                  <button
                    class="btn-icon"
                    type="button"
                    title="Xem chi tiết truyện & số tập"
                    @click="openDetailModal(item)"
                  >
                    <i class="ri-eye-line"></i>
                  </button>
                  <button
                    class="btn-icon"
                    type="button"
                    title="Quản lý các tập audio"
                    @click="$emit('manage-episodes', item.id)"
                  >
                    <i class="ri-play-list-line"></i>
                  </button>
                  <button
                    class="btn-icon"
                    type="button"
                    title="Chỉnh sửa thông tin"
                    @click="openEditModal(item)"
                  >
                    <i class="ri-edit-line"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 2. Visual Gallery Grid Mode (Audio Cards Studio) -->
      <div v-else-if="viewMode === 'grid'" class="series-gallery-grid">
        <div v-for="item in seriesList" :key="item.id" class="gallery-card">
          <div class="gallery-cover-wrap" @click="openDetailModal(item)">
            <img :src="item.cover_url || '/android-chrome-512x512.png'" :alt="item.title" class="gallery-cover-img" loading="lazy" />
            <div class="gallery-cover-overlay">
              <div class="gallery-top-badges">
                <span class="badge badge-neutral">{{ item.category || 'Chưa phân loại' }}</span>
                <div style="display: flex; gap: 4px;">
                  <span v-if="item.is_premium" class="badge badge-vip">VIP</span>
                  <span v-else class="badge badge-free">Free</span>
                  <span v-if="item.is_hot" class="badge badge-hot">Hot 🔥</span>
                </div>
              </div>
              <div class="gallery-bottom-status">
                <span class="badge" :class="getStatusBadgeClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </div>
            </div>
          </div>

          <div class="gallery-body">
            <h4 class="gallery-title" :title="item.title" @click="openDetailModal(item)">
              {{ item.title }}
            </h4>
            <div class="gallery-meta">
              <span><i class="ri-user-voice-line"></i> {{ item.author || 'Chưa cập nhật' }}</span>
              <span v-if="item.narrator">• {{ item.narrator }}</span>
            </div>

            <div class="gallery-stats">
              <div class="gallery-stat-item">
                <span>Lượt nghe</span>
                <strong><i class="ri-headphone-line" style="color: #34d399;"></i> {{ formatNumber(item.total_listens || item.listen_count || 0) }}</strong>
              </div>
              <div class="gallery-stat-item">
                <span>Số tập</span>
                <strong><i class="ri-play-list-line" style="color: #c084fc;"></i> {{ formatSeriesEpisodeCount(item) }}</strong>
              </div>
            </div>

            <div class="gallery-actions">
              <button class="btn btn-ghost btn-sm" type="button" @click="openDetailModal(item)">
                <i class="ri-eye-line"></i>
                <span>Chi tiết</span>
              </button>
              <button class="btn btn-ghost btn-sm" type="button" @click="$emit('manage-episodes', item.id)">
                <i class="ri-play-circle-line"></i>
                <span>Tập audio</span>
              </button>
              <button class="btn-icon" type="button" title="Sửa thông tin" @click="openEditModal(item)">
                <i class="ri-edit-line"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="pagination-footer">
        <span class="page-info">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} bộ truyện)
        </span>
        <div class="pagination-controls">
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page <= 1 || loading"
            @click="fetchSeries(pagination.current_page - 1)"
          >
            <i class="ri-arrow-left-s-line"></i> Trước
          </button>
          <button
            class="btn btn-ghost btn-sm"
            type="button"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="fetchSeries(pagination.current_page + 1)"
          >
            Sau <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- 🌟 SLIDE-OVER RIGHT INSPECTOR DRAWER: CHI TIẾT TRUYỆN AUDIO -->
    <Teleport to="body">
      <transition name="drawer-fade">
        <div v-if="detailModal.show" class="admin-drawer-overlay" @click.self="detailModal.show = false">
          <div class="admin-right-drawer">
            <div class="drawer-header">
            <div class="detail-header-title">
              <span class="header-icon"><i class="ri-book-read-line"></i></span>
              <div>
                <h3>Chi tiết truyện audio</h3>
                <span class="detail-subtitle">Mã truyện: <code>{{ detailModal.data?.id || '...' }}</code></span>
              </div>
            </div>
            <div class="detail-header-badges">
              <span v-if="detailModal.data?.is_premium" class="badge badge-vip">VIP</span>
              <span v-else class="badge badge-free">Free</span>
              <span v-if="detailModal.data?.is_hot" class="badge badge-hot">Hot 🔥</span>
              <span class="badge" :class="getStatusBadgeClass(detailModal.data?.status)">
                {{ getStatusLabel(detailModal.data?.status) }}
              </span>
              <button class="btn-close" type="button" @click="detailModal.show = false">
                <i class="ri-close-line"></i>
              </button>
            </div>
          </div>

          <!-- Loading State for Detail -->
          <div v-if="detailModal.loading" class="detail-loading-state">
            <i class="ri-loader-4-line ri-spin"></i>
            <p>Đang tải dữ liệu chi tiết truyện và danh sách tập...</p>
          </div>

          <div v-else-if="detailModal.data" class="drawer-body detail-modal-body">
            <!-- 1. Hero Summary Card -->
            <div class="detail-hero-grid">
              <!-- Left Cover -->
              <div class="detail-cover-column">
                <div class="detail-cover-container">
                  <img
                    :src="detailModal.data.cover_url || '/android-chrome-512x512.png'"
                    :alt="detailModal.data.title"
                    @error="handleCoverError"
                  />
                  <div class="cover-quick-badge">
                    {{ detailModal.data.category || 'Chưa phân loại' }}
                  </div>
                </div>
                <div class="detail-cover-actions">
                  <a
                    v-if="detailModal.data.cover_url"
                    :href="detailModal.data.cover_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-cover-link"
                  >
                    <i class="ri-external-link-line"></i> Xem ảnh gốc
                  </a>
                </div>
              </div>

              <!-- Right Info -->
              <div class="detail-info-column">
                <h2 class="detail-story-title">{{ detailModal.data.title }}</h2>
                <div class="detail-story-meta-tags">
                  <span class="meta-tag"><i class="ri-link"></i> /story/{{ detailModal.data.slug || detailModal.data.id }}</span>
                  <span class="meta-tag"><i class="ri-time-line"></i> Tạo: {{ formatDate(detailModal.data.created_at) }}</span>
                </div>

                <!-- Info Grid -->
                <div class="detail-meta-grid">
                  <div class="meta-item">
                    <span class="meta-label">Tác giả</span>
                    <strong class="meta-val"><i class="ri-quill-pen-line"></i> {{ detailModal.data.author || 'Chưa cập nhật' }}</strong>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Người đọc / MC</span>
                    <strong class="meta-val"><i class="ri-mic-line"></i> {{ detailModal.data.narrator || 'Chưa cập nhật' }}</strong>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Thể loại</span>
                    <strong class="meta-val text-purple"><i class="ri-price-tag-3-line"></i> {{ detailModal.data.category || 'Chưa phân loại' }}</strong>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Trạng thái phát hành</span>
                    <strong class="meta-val"><i class="ri-checkbox-circle-line"></i> {{ getStatusLabel(detailModal.data.status) }}</strong>
                  </div>
                </div>

                <!-- Stats KPI Row -->
                <div class="detail-stats-grid">
                  <div class="detail-stat-card stat-episodes">
                    <div class="stat-icon"><i class="ri-play-list-line"></i></div>
                    <div class="stat-body">
                      <span class="stat-num">{{ formatDetailEpisodeStatNum(detailModal.data) }}</span>
                      <span class="stat-title">{{ formatDetailEpisodeStatTitle(detailModal.data) }}</span>
                    </div>
                  </div>

                  <div class="detail-stat-card stat-listens">
                    <div class="stat-icon"><i class="ri-headphone-line"></i></div>
                    <div class="stat-body">
                      <span class="stat-num">{{ formatNumber(detailModal.data.total_listens || detailModal.data.listen_count || 0) }}</span>
                      <span class="stat-title">Lượt nghe tổng</span>
                    </div>
                  </div>

                  <div class="detail-stat-card stat-rating">
                    <div class="stat-icon"><i class="ri-star-fill"></i></div>
                    <div class="stat-body">
                      <span class="stat-num">{{ Number(detailModal.data.average_rating || 5).toFixed(1) }} <small>★</small></span>
                      <span class="stat-title">{{ detailModal.data.ratings_count || 0 }} lượt đánh giá</span>
                    </div>
                  </div>

                  <div class="detail-stat-card stat-comments">
                    <div class="stat-icon"><i class="ri-chat-3-line"></i></div>
                    <div class="stat-body">
                      <span class="stat-num">{{ detailModal.data.comments_count || 0 }}</span>
                      <span class="stat-title">Bình luận</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Story Description -->
            <div class="detail-section-card">
              <div class="section-card-title">
                <i class="ri-file-text-line"></i>
                <h4>Nội dung giới thiệu & Tóm tắt cốt truyện</h4>
              </div>
              <div class="story-desc-box">
                <p v-if="detailModal.data.description">{{ detailModal.data.description }}</p>
                <p v-else class="text-empty-desc">Chưa có mô tả tóm tắt cho bộ truyện này.</p>
              </div>
            </div>

            <!-- 3. Episodes List & Count Details -->
            <div class="detail-section-card">
              <div class="section-card-title flex-between">
                <div class="flex-align gap-8">
                  <i class="ri-disc-line"></i>
                  <h4>Danh sách tập audio</h4>
                  <span class="badge badge-neutral">
                    {{ isSingleOrZeroEpisodeSeries(detailModal.data) ? 'Full (1 tập)' : detailModal.episodesList.length + ' tập' }}
                  </span>
                </div>
                <div class="flex-align gap-10">
                  <input
                    v-model="detailModal.episodeSearch"
                    type="search"
                    placeholder="Lọc số tập, tên tập..."
                    class="ep-mini-search"
                  />
                  <button
                    class="btn btn-sm btn-ghost"
                    type="button"
                    @click="goToEpisodeManager(detailModal.data.id)"
                  >
                    <i class="ri-upload-cloud-2-line"></i>
                    <span>Thêm tập mới</span>
                  </button>
                </div>
              </div>

              <!-- Episodes Table / List -->
              <div v-if="filteredModalEpisodes.length" class="detail-episodes-table-wrap">
                <table class="data-table detail-ep-table">
                  <thead>
                    <tr>
                      <th class="th-ep-num">Số tập</th>
                      <th class="th-ep-title">Tiêu đề tập</th>
                      <th class="th-ep-duration">Thời lượng</th>
                      <th class="th-ep-listens">Lượt nghe</th>
                      <th class="th-ep-plan">Gói</th>
                      <th class="th-ep-action text-right">Nghe thử</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ep in filteredModalEpisodes" :key="ep.id" :class="{ 'row-playing': isPlayingDetail(ep) }">
                      <td>
                        <span class="ep-badge" :class="{ 'badge-full': isEpisodeFull(ep, detailModal.data) }">
                          {{ formatModalEpBadge(ep) }}
                        </span>
                      </td>
                      <td>
                        <div class="ep-title-cell">
                          <strong class="ep-title">{{ ep.title }}</strong>
                          <div v-if="isPlayingDetail(ep)" class="sound-wave-bars">
                            <span></span><span></span><span></span><span></span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="text-muted"><i class="ri-time-line"></i> {{ formatDuration(ep.duration_seconds) }}</span>
                      </td>
                      <td>
                        <span class="text-muted">{{ formatNumber(ep.play_count || ep.listen_count || 0) }}</span>
                      </td>
                      <td>
                        <span class="badge" :class="ep.is_premium ? 'badge-vip' : 'badge-free'">
                          {{ ep.is_premium ? 'VIP' : 'Free' }}
                        </span>
                      </td>
                      <td class="text-right">
                        <button
                          class="btn-play-preview"
                          :class="{ active: isPlayingDetail(ep) }"
                          type="button"
                          :title="isPlayingDetail(ep) ? 'Tạm dừng' : 'Nghe thử tập này'"
                          @click="togglePlayDetailPreview(ep)"
                        >
                          <i :class="isPlayingDetail(ep) ? 'ri-pause-fill' : 'ri-play-fill'"></i>
                          <span>{{ isPlayingDetail(ep) ? 'Đang phát' : 'Nghe thử' }}</span>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-else class="detail-empty-episodes">
                <i class="ri-headphone-line"></i>
                <p v-if="detailModal.episodeSearch">Không tìm thấy tập nào khớp với "{{ detailModal.episodeSearch }}"</p>
                <p v-else>Bộ truyện này hiện chưa có tập audio nào được tải lên hệ thống.</p>
                <button
                  class="btn btn-primary btn-sm"
                  type="button"
                  @click="goToEpisodeManager(detailModal.data.id)"
                >
                  <i class="ri-upload-cloud-2-line"></i>
                  <span>Thêm tập audio ngay</span>
                </button>
              </div>
            </div>

            <!-- Audio Player Bar inside Modal -->
            <transition name="fade-slide">
              <div v-if="detailModal.previewingAudio" class="detail-audio-player-box">
                <div class="player-left">
                  <div class="player-icon"><i class="ri-music-2-fill ri-spin"></i></div>
                  <div>
                    <strong>{{ detailModal.previewingAudio.title }}</strong>
                    <small>{{ formatModalPreviewSub(detailModal.previewingAudio, detailModal.data) }}</small>
                  </div>
                </div>
                <audio
                  :src="detailModal.previewingAudio.storage_audio_url || detailModal.previewingAudio.audio_path"
                  controls
                  autoplay
                  class="native-audio"
                  @ended="detailModal.previewingAudio = null"
                ></audio>
                <button class="btn-close-audio" type="button" @click="detailModal.previewingAudio = null">
                  <i class="ri-close-line"></i>
                </button>
              </div>
            </transition>
          </div>

            <div class="drawer-footer">
              <a
                v-if="detailModal.data.id"
                :href="`/story/${detailModal.data.slug || detailModal.data.id}`"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-ghost btn-sm"
              >
                <i class="ri-external-link-line"></i> Web
              </a>
              <button
                class="btn btn-ghost btn-sm"
                type="button"
                @click="openEditFromDetail"
              >
                <i class="ri-edit-line"></i>
                <span>Sửa truyện</span>
              </button>
              <button
                class="btn btn-primary btn-sm btn-glow"
                type="button"
                @click="goToEpisodeManager(detailModal.data?.id)"
              >
                <i class="ri-play-circle-line"></i>
                <span>Tập audio (Studio)</span>
              </button>
              <button class="btn btn-ghost btn-sm" type="button" @click="detailModal.show = false">
                <span>Đóng</span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Modal Form: Tạo / Sửa Truyện -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="modal.show" class="admin-modal-overlay" @click.self="modal.show = false">
          <div class="admin-modal modal-form-dialog">
            <div class="modal-header">
              <div class="modal-title-wrap">
                <div class="modal-icon-badge" :class="modal.isEdit ? 'badge-amber' : 'badge-purple'">
                  <i :class="modal.isEdit ? 'ri-edit-2-line' : 'ri-add-circle-line'"></i>
                </div>
                <div>
                  <h3>{{ modal.isEdit ? 'Chỉnh sửa bộ truyện' : 'Thêm truyện audio mới' }}</h3>
                  <span class="modal-subtitle">
                    {{ modal.isEdit ? 'Cập nhật thông tin chi tiết, ảnh bìa và phân loại' : 'Điền thông tin bên dưới để khởi tạo bộ truyện mới' }}
                  </span>
                </div>
              </div>
              <button class="btn-close" type="button" title="Đóng modal" @click="modal.show = false">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <form @submit.prevent="saveSeries">
              <div class="modal-body form-body">
                <!-- Section 1: Thông tin cơ bản -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-book-open-line"></i>
                    <span>Thông tin cơ bản</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group full-width">
                      <label>Tên truyện <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-text"></i>
                        <input v-model="form.title" type="text" required placeholder="Nhập tên bộ truyện..." />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Slug (Đường dẫn tĩnh)</label>
                      <div class="input-with-icon">
                        <i class="ri-links-line"></i>
                        <input v-model="form.slug" type="text" placeholder="than-mo (tự tạo nếu để trống)" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Thể loại <span class="required">*</span></label>
                      <div class="input-with-icon">
                        <i class="ri-price-tag-3-line"></i>
                        <input v-model="form.category" type="text" required list="category-suggestions" placeholder="Tiên hiệp, Ngôn tình..." />
                        <datalist id="category-suggestions">
                          <option v-for="cat in categories" :key="cat.category" :value="cat.category" />
                        </datalist>
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Tác giả</label>
                      <div class="input-with-icon">
                        <i class="ri-quill-pen-line"></i>
                        <input v-model="form.author" type="text" placeholder="Tên tác giả..." />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Người đọc / MC</label>
                      <div class="input-with-icon">
                        <i class="ri-mic-line"></i>
                        <input v-model="form.narrator" type="text" placeholder="MC Huyền Vũ, Đình Soạn..." />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Tổng số tập dự kiến</label>
                      <div class="input-with-icon">
                        <i class="ri-play-list-line"></i>
                        <input
                          v-model.number="form.total_episodes"
                          type="number"
                          min="0"
                          placeholder="Ví dụ: 120 (0 hoặc 1 nếu Full)"
                        />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Trạng thái phát hành</label>
                      <div class="input-with-icon">
                        <i class="ri-time-line"></i>
                        <select v-model="form.status">
                          <option value="ongoing">Đang phát (Ongoing)</option>
                          <option value="completed">Hoàn thành (Completed)</option>
                          <option value="draft">Bản nháp (Draft)</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section 2: Ảnh bìa & Mô tả -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-image-line"></i>
                    <span>Ảnh bìa & Tóm tắt</span>
                  </div>
                  <div class="form-grid">
                    <div class="form-group full-width">
                      <label>Link ảnh bìa (Cover URL)</label>
                      <div class="cover-uploader-box">
                        <div class="input-with-icon flex-1">
                          <i class="ri-image-add-line"></i>
                          <input v-model="form.cover_url" type="url" placeholder="https://domain.com/cover.jpg" />
                        </div>
                        <div class="cover-preview-card">
                          <img
                            :src="form.cover_url || '/android-chrome-512x512.png'"
                            alt="Cover Preview"
                            @error="handleCoverError"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="form-group full-width">
                      <label>Mô tả & Tóm tắt cốt truyện</label>
                      <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Nội dung giới thiệu cốt truyện hấp dẫn..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Section 3: Cài đặt quyền hạn & Đánh dấu -->
                <div class="form-section">
                  <div class="form-section-title">
                    <i class="ri-shield-star-line"></i>
                    <span>Phân quyền & Gắn nhãn</span>
                  </div>
                  <div class="toggle-cards-grid">
                    <label class="toggle-card" :class="{ active: form.is_premium }">
                      <input v-model="form.is_premium" type="checkbox" class="sr-only" />
                      <div class="toggle-card-icon vip"><i class="ri-vip-crown-2-line"></i></div>
                      <div class="toggle-card-body">
                        <strong>Gói VIP</strong>
                        <small>Yêu cầu VIP để nghe</small>
                      </div>
                      <div class="switch-pill"></div>
                    </label>

                    <label class="toggle-card" :class="{ active: form.is_hot }">
                      <input v-model="form.is_hot" type="checkbox" class="sr-only" />
                      <div class="toggle-card-icon hot"><i class="ri-fire-line"></i></div>
                      <div class="toggle-card-body">
                        <strong>Truyện Hot 🔥</strong>
                        <small>Ưu tiên lên trang chủ</small>
                      </div>
                      <div class="switch-pill"></div>
                    </label>

                    <label class="toggle-card" :class="{ active: form.is_complete }">
                      <input v-model="form.is_complete" type="checkbox" class="sr-only" />
                      <div class="toggle-card-icon full"><i class="ri-checkbox-circle-line"></i></div>
                      <div class="toggle-card-body">
                        <strong>Full trọn bộ</strong>
                        <small>Đã phát hành đầy đủ</small>
                      </div>
                      <div class="switch-pill"></div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn btn-ghost" type="button" @click="modal.show = false">
                  <span>Hủy bỏ</span>
                </button>
                <button class="btn btn-primary btn-glow" type="submit" :disabled="modal.submitting">
                  <i v-if="modal.submitting" class="ri-loader-4-line ri-spin"></i>
                  <i v-else class="ri-save-line"></i>
                  <span>{{ modal.isEdit ? 'Lưu thay đổi' : 'Tạo truyện mới' }}</span>
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
import { ref, reactive, computed, onMounted } from 'vue'
import AdminService from '@/services/AdminService'
import AdminTableSkeleton from './AdminTableSkeleton.vue'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const emit = defineEmits(['manage-episodes'])
const toast = useToastStore()

const loading = ref(false)
const viewMode = ref('table')
const seriesList = ref([])
const categories = ref([])

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
})

const filters = reactive({
  search: '',
  category: 'all',
  is_premium: '',
  is_hot: '',
  status: 'all',
})

// Story Detail Modal State
const detailModal = reactive({
  show: false,
  loading: false,
  data: null,
  episodesList: [],
  episodeSearch: '',
  previewingAudio: null,
})

const modal = reactive({
  show: false,
  isEdit: false,
  submitting: false,
  editingId: null,
})

const form = reactive({
  title: '',
  slug: '',
  category: '',
  author: '',
  narrator: '',
  cover_url: '',
  description: '',
  status: 'ongoing',
  total_episodes: 0,
  is_premium: false,
  is_hot: false,
  is_complete: false,
})

const filteredModalEpisodes = computed(() => {
  if (!detailModal.episodesList) return []
  if (!detailModal.episodeSearch) return detailModal.episodesList
  const q = detailModal.episodeSearch.toLowerCase().trim()
  return detailModal.episodesList.filter(
    (ep) =>
      ep.title?.toLowerCase().includes(q) ||
      String(ep.episode_number).includes(q)
  )
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchSeries(1)
  }, 300)
}

async function fetchCategories() {
  try {
    const res = await AdminService.getCategories()
    categories.value = extractApiPayload(res) || []
  } catch (err) {
    console.error('Failed to load categories', err)
  }
}

async function fetchSeries(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.per_page,
    }
    if (filters.search) params.search = filters.search
    if (filters.category !== 'all') params.category = filters.category
    if (filters.is_premium !== '') params.is_premium = filters.is_premium
    if (filters.is_hot !== '') params.is_hot = filters.is_hot
    if (filters.status !== 'all') params.status = filters.status

    const res = await AdminService.getSeries(params)
    const payload = extractApiPayload(res)

    seriesList.value = payload.items || []
    if (payload.pagination) {
      pagination.current_page = payload.pagination.current_page
      pagination.last_page = payload.pagination.last_page
      pagination.total = payload.pagination.total
    }
  } catch (err) {
    toast.error('Không thể tải danh sách truyện')
  } finally {
    loading.value = false
  }
}

async function openDetailModal(item) {
  detailModal.show = true
  detailModal.loading = true
  detailModal.data = item
  detailModal.episodesList = []
  detailModal.episodeSearch = ''
  detailModal.previewingAudio = null

  try {
    const res = await AdminService.getSeriesDetail(item.id)
    const payload = extractApiPayload(res)
    if (payload) {
      detailModal.data = payload
      detailModal.episodesList = payload.episodes || []
    }
  } catch (err) {
    console.error('Failed to load full series detail', err)
    // Fallback to table item info
  } finally {
    detailModal.loading = false
  }
}

function openEditFromDetail() {
  if (!detailModal.data) return
  const item = detailModal.data
  detailModal.show = false
  openEditModal(item)
}

function goToEpisodeManager(seriesId) {
  detailModal.show = false
  if (seriesId) {
    emit('manage-episodes', seriesId)
  }
}

function isPlayingDetail(ep) {
  return detailModal.previewingAudio && detailModal.previewingAudio.id === ep.id
}

function togglePlayDetailPreview(ep) {
  if (isPlayingDetail(ep)) {
    detailModal.previewingAudio = null
  } else {
    detailModal.previewingAudio = ep
  }
}

function openCreateModal() {
  modal.isEdit = false
  modal.editingId = null
  Object.assign(form, {
    title: '',
    slug: '',
    category: categories.value[0]?.category || 'Tiên hiệp',
    author: '',
    narrator: '',
    cover_url: '',
    description: '',
    status: 'ongoing',
    total_episodes: 0,
    is_premium: false,
    is_hot: false,
    is_complete: false,
  })
  modal.show = true
}

function openEditModal(item) {
  modal.isEdit = true
  modal.editingId = item.id
  Object.assign(form, {
    title: item.title,
    slug: item.slug,
    category: item.category,
    author: item.author || '',
    narrator: item.narrator || '',
    cover_url: item.cover_url || '',
    description: item.description || '',
    status: item.status || 'ongoing',
    total_episodes: item.total_episodes || item.episodes_count || 0,
    is_premium: Boolean(item.is_premium),
    is_hot: Boolean(item.is_hot),
    is_complete: Boolean(item.is_complete),
  })
  modal.show = true
}

async function saveSeries() {
  modal.submitting = true
  try {
    if (modal.isEdit) {
      await AdminService.updateSeries(modal.editingId, form)
      toast.success('Cập nhật truyện thành công!')
    } else {
      await AdminService.createSeries(form)
      toast.success('Thêm truyện mới thành công!')
    }
    modal.show = false
    fetchSeries(pagination.current_page)
    fetchCategories()
  } catch (err) {
    toast.error(err.message || 'Lỗi khi lưu truyện')
  } finally {
    modal.submitting = false
  }
}

async function toggleHot(item) {
  try {
    await AdminService.toggleSeriesHot(item.id)
    item.is_hot = !item.is_hot
    toast.success(`Đã ${item.is_hot ? 'bật' : 'tắt'} Hot cho "${item.title}"`)
  } catch (err) {
    toast.error('Lỗi khi đổi trạng thái Hot')
  }
}

async function togglePremium(item) {
  try {
    await AdminService.toggleSeriesPremium(item.id)
    item.is_premium = !item.is_premium
    toast.success(`Đã đổi sang ${item.is_premium ? 'VIP' : 'Miễn phí'}`)
  } catch (err) {
    toast.error('Lỗi khi đổi trạng thái VIP')
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'completed': return 'Hoàn thành'
    case 'draft': return 'Bản nháp'
    case 'ongoing':
    default:
      return 'Đang phát'
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'completed': return 'badge-success'
    case 'draft': return 'badge-neutral'
    case 'ongoing':
    default:
      return 'badge-ongoing'
  }
}

function formatDuration(seconds) {
  if (!seconds) return '00:00'
  const m = Math.floor(seconds / 60)
  const s = Math.floor(seconds % 60)
  return `${m}:${s < 10 ? '0' : ''}${s}`
}

function formatDate(dateStr) {
  if (!dateStr) return 'Đang cập nhật'
  try {
    return new Date(dateStr).toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

function formatNumber(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num
}

function handleCoverError(e) {
  e.target.src = '/android-chrome-512x512.png'
}

function isSingleOrZeroEpisodeSeries(item) {
  if (!item) return false
  const total = Number(item.total_episodes)
  const count = Number(item.episodes_count)
  return total === 1 || total === 0 || count === 1 || count === 0
}

function formatSeriesEpisodeCount(item) {
  if (!item) return '0 tập'
  const total = Number(item.total_episodes)
  const count = Number(item.episodes_count)
  if (total === 1 || total === 0 || count === 1 || count === 0 || item.total_episodes === 0) {
    return 'Full'
  }
  return `${item.total_episodes || item.episodes_count || 0} tập`
}

function isEpisodeFull(ep, seriesData = null) {
  if (!ep) return false
  const epNum = Number(ep.episode_number)
  if (epNum === 0 || ep.episode_number === 0 || ep.episode_number === '0') return true
  const total = Number(seriesData?.total_episodes ?? detailModal.data?.total_episodes)
  const count = Number(seriesData?.episodes_count ?? detailModal.data?.episodes_count ?? detailModal.episodesList?.length)
  if (total === 1 || total === 0 || count === 1) return true
  return false
}

function formatModalEpBadge(ep) {
  if (isEpisodeFull(ep, detailModal.data)) {
    return 'Full'
  }
  return `#${ep.episode_number}`
}

function formatModalPreviewSub(ep, seriesData) {
  if (!ep) return ''
  const prefix = isEpisodeFull(ep, seriesData) ? 'Tập Full' : `Tập #${ep.episode_number}`
  return `${prefix} • ${seriesData?.title || 'Truyện Audio'}`
}

function formatDetailEpisodeStatNum(data) {
  if (!data) return '0'
  if (isSingleOrZeroEpisodeSeries(data)) {
    return 'Full'
  }
  return String(data.episodes?.length || data.episodes_count || 0)
}

function formatDetailEpisodeStatTitle(data) {
  if (!data) return 'Tập'
  if (isSingleOrZeroEpisodeSeries(data)) {
    return 'Trọn bộ Full'
  }
  return `Tập đã tải lên / ${data.total_episodes || data.episodes?.length || 0} dự kiến`
}

defineExpose({
  openCreateModal,
  openDetailModal,
})

onMounted(() => {
  fetchCategories()
  fetchSeries(1)
})
</script>

<style>
.series-manager {
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
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-box:focus-within {
  border-color: #a855f7;
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
}

.search-box i {
  color: var(--admin-muted, #94a3b8);
  font-size: 16px;
}

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

.btn-clear-search:hover { color: #f8fafc; }

.filter-group {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
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
  transition: border-color 0.2s ease;
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

.story-profile {
  display: flex;
  align-items: center;
  gap: 14px;
}

.clickable-profile {
  cursor: pointer;
  transition: transform 0.15s ease;
}

.clickable-profile:hover .story-title {
  color: #c084fc;
  text-decoration: underline;
}

.story-cover-wrap {
  width: 44px;
  height: 58px;
  border-radius: 8px;
  overflow: hidden;
  background: #090a0f;
  flex-shrink: 0;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.story-cover-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.story-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.story-title {
  color: #f8fafc;
  font-size: 14px;
  font-weight: 600;
  max-width: 260px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.15s ease;
}

.story-meta {
  font-size: 12px;
  color: var(--admin-faint, #64748b);
  display: flex;
  align-items: center;
  gap: 6px;
}

.episodes-pill {
  font-size: 13px;
  color: #cbd5e1;
}

.ep-diff {
  display: block;
  font-size: 11px;
  color: #a855f7;
  font-weight: 500;
}

.badge-toggle {
  border: none;
  cursor: pointer;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  transition: transform 0.15s ease;
}

.badge-toggle:hover {
  transform: scale(1.06);
}

.badge-free {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.badge-hot {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-ongoing {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
}

.rating-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #fbbf24;
  font-weight: 700;
  font-size: 12px;
}

.switch-toggle {
  position: relative;
  display: inline-block;
  width: 38px;
  height: 22px;
}

.switch-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #334155;
  transition: 0.25s ease;
  border-radius: 22px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.25s ease;
  border-radius: 50%;
}

input:checked + .slider {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
}

input:checked + .slider:before {
  transform: translateX(16px);
}

.row-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.pagination-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-info {
  font-size: 13px;
  color: var(--admin-muted, #94a3b8);
}

.pagination-controls {
  display: flex;
  gap: 8px;
}

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

.empty-state h4 {
  color: #f8fafc;
  font-size: 16px;
  margin: 0;
}

.empty-state p {
  margin: 0;
  font-size: 13px;
}

/* Modals */
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
  max-width: 680px;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.9), 0 0 35px rgba(168, 85, 247, 0.12);
  overflow: hidden;
  position: relative;
  animation: modalPopIn 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-form-dialog {
  max-width: 760px !important;
}

.detail-modal {
  max-width: 960px !important;
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

/* Modal Fade Animation */
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

/* 🌟 STORY DETAIL MODAL SPECIFIC STYLES */
.detail-modal {
  max-width: 900px;
  max-height: calc(100vh - 48px);
}

.detail-header-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  color: white;
  display: grid;
  place-items: center;
  font-size: 20px;
  box-shadow: 0 0 14px rgba(168, 85, 247, 0.4);
}

.detail-subtitle {
  font-size: 11px;
  color: var(--admin-muted, #94a3b8);
}

.detail-subtitle code {
  color: #c084fc;
  font-family: var(--admin-font-mono);
}

.detail-header-badges {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-loading-state {
  padding: 80px 20px;
  text-align: center;
  color: var(--admin-muted, #94a3b8);
  font-size: 15px;
}

.detail-loading-state i {
  font-size: 36px;
  color: #a855f7;
  display: block;
  margin-bottom: 12px;
}

.detail-modal-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 24px;
}

.detail-hero-grid {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 24px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 20px;
}

@media (max-width: 768px) {
  .detail-hero-grid {
    grid-template-columns: 1fr;
  }
}

.detail-cover-container {
  position: relative;
  width: 100%;
  aspect-ratio: 3/4;
  border-radius: 12px;
  overflow: hidden;
  background: #090a0f;
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
}

.detail-cover-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-quick-badge {
  position: absolute;
  bottom: 8px;
  left: 8px;
  right: 8px;
  background: rgba(10, 12, 20, 0.85);
  backdrop-filter: blur(8px);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-align: center;
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-cover-actions {
  margin-top: 8px;
  text-align: center;
}

.btn-cover-link {
  font-size: 12px;
  color: #94a3b8;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-cover-link:hover {
  color: #c084fc;
}

.detail-info-column {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.detail-story-title {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.3;
}

.detail-story-meta-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.meta-tag {
  font-size: 12px;
  color: #94a3b8;
  background: rgba(255, 255, 255, 0.04);
  padding: 3px 10px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.detail-meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
  background: rgba(15, 18, 28, 0.6);
  padding: 14px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.meta-label {
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.meta-val {
  font-size: 13px;
  color: #f1f5f9;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.text-purple {
  color: #c084fc;
}

.detail-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
}

.detail-stat-card {
  background: rgba(18, 22, 34, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.detail-stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(168, 85, 247, 0.3);
}

.stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}

.stat-episodes .stat-icon {
  background: rgba(168, 85, 247, 0.15);
  color: #c084fc;
}

.stat-listens .stat-icon {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
}

.stat-rating .stat-icon {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
}

.stat-comments .stat-icon {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
}

.stat-body {
  display: flex;
  flex-direction: column;
}

.stat-num {
  font-size: 16px;
  font-weight: 700;
  color: #f8fafc;
}

.stat-title {
  font-size: 11px;
  color: #94a3b8;
  white-space: nowrap;
}

.detail-section-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.section-card-title {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #f8fafc;
}

.section-card-title i {
  font-size: 18px;
  color: #a855f7;
}

.section-card-title h4 {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
}

.flex-between {
  justify-content: space-between;
  width: 100%;
}

.flex-align {
  display: flex;
  align-items: center;
}

.gap-8 { gap: 8px; }
.gap-10 { gap: 10px; }

.story-desc-box {
  background: rgba(10, 12, 20, 0.5);
  border-radius: 10px;
  padding: 14px 16px;
  font-size: 13.5px;
  line-height: 1.7;
  color: #cbd5e1;
  max-height: 160px;
  overflow-y: auto;
}

.story-desc-box p {
  margin: 0;
  white-space: pre-line;
}

.text-empty-desc {
  color: #64748b;
  font-style: italic;
}

.ep-mini-search {
  background: rgba(15, 18, 28, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f8fafc;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  outline: none;
  width: 180px;
}

.ep-mini-search:focus {
  border-color: #a855f7;
}

.detail-episodes-table-wrap {
  max-height: 380px;
  overflow: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(10, 13, 22, 0.6);
}

.detail-ep-table {
  width: 100%;
  min-width: 620px;
  border-collapse: separate;
  border-spacing: 0;
}

.detail-ep-table th {
  background: rgba(15, 18, 28, 0.98);
  position: sticky;
  top: 0;
  z-index: 2;
  white-space: nowrap;
  padding: 12px 14px;
  font-size: 11px;
}

.detail-ep-table td {
  padding: 12px 14px;
  font-size: 13px;
  white-space: nowrap;
  vertical-align: middle;
}

.detail-ep-table td:nth-child(2) {
  white-space: normal;
}

.th-ep-num { width: 75px; min-width: 75px; }
.th-ep-title { min-width: 220px; }
.th-ep-duration { width: 105px; min-width: 105px; }
.th-ep-listens { width: 95px; min-width: 95px; }
.th-ep-plan { width: 75px; min-width: 75px; }
.th-ep-action { width: 110px; min-width: 110px; }

.detail-empty-episodes {
  padding: 36px 20px;
  text-align: center;
  color: var(--admin-muted, #94a3b8);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.detail-empty-episodes i {
  font-size: 32px;
  color: #64748b;
}

.detail-empty-episodes p {
  margin: 0;
  font-size: 13px;
}

.detail-audio-player-box {
  background: rgba(18, 22, 34, 0.95);
  border: 1px solid rgba(168, 85, 247, 0.5);
  border-radius: 14px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
}

.player-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 200px;
}

.player-icon {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  display: grid;
  place-items: center;
  font-size: 16px;
}

.player-left strong {
  display: block;
  font-size: 13px;
  color: #f8fafc;
}

.player-left small {
  font-size: 11px;
  color: #94a3b8;
}

.native-audio {
  flex: 1;
  max-width: 450px;
  height: 34px;
}

.btn-close-audio {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  border: none;
  color: #cbd5e1;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.btn-close-audio:hover {
  background: rgba(244, 63, 94, 0.2);
  color: #fb7185;
}

.detail-modal-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-left {
  display: flex;
  gap: 8px;
}

.footer-right {
  display: flex;
  gap: 8px;
}

/* ─── Generic Modal Styles ─── */
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

.modal-header h3 {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
  color: #f8fafc;
}

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
  scrollbar-width: thin;
  scrollbar-color: rgba(168, 85, 247, 0.35) transparent;
}

.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(168, 85, 247, 0.35);
  border-radius: 4px;
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

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.full-width {
  grid-column: 1 / -1;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
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
  font-size: 13px;
  font-weight: 700;
  color: #f1f5f9;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.form-section-title i {
  color: #c084fc;
  font-size: 16px;
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

.modal-icon-badge.badge-amber {
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
  transition: color 0.2s ease;
}

.input-with-icon input,
.input-with-icon select {
  padding-left: 40px !important;
}

.input-with-icon:focus-within > i {
  color: #c084fc;
}

.form-group input,
.form-group select,
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
.form-group select:focus,
.form-group textarea:focus {
  background: rgba(20, 24, 38, 0.95);
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.2), 0 0 16px rgba(168, 85, 247, 0.15);
}

.cover-uploader-box {
  display: flex;
  gap: 14px;
  align-items: center;
}

.flex-1 { flex: 1; }

.cover-preview-card {
  width: 52px;
  height: 68px;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: #0f121c;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.cover-preview-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.toggle-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
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
  background: rgba(168, 85, 247, 0.12);
  border-color: rgba(168, 85, 247, 0.45);
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.15);
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

.toggle-card-icon.vip { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.toggle-card-icon.hot { background: rgba(244, 63, 94, 0.15); color: #fb7185; }
.toggle-card-icon.full { background: rgba(16, 185, 129, 0.15); color: #34d399; }

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
  background: #a855f7;
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

.required {
  color: #fb7185;
}

.ep-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.06);
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 700;
  color: #c084fc;
  font-family: var(--admin-font-mono);
  font-size: 12px;
}

.ep-badge.badge-full {
  background: rgba(16, 185, 129, 0.18);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.35);
  font-weight: 800;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.ep-title-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 200px;
}

.ep-title {
  color: #f8fafc;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.45;
  word-break: normal;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-play-preview {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border-radius: 6px;
  border: 1px solid rgba(168, 85, 247, 0.35);
  background: rgba(168, 85, 247, 0.12);
  color: #c084fc;
  font-size: 11.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-play-preview:hover,
.btn-play-preview.active {
  background: #a855f7;
  color: #ffffff;
  border-color: #a855f7;
  box-shadow: 0 0 12px rgba(168, 85, 247, 0.4);
}

.sound-wave-bars {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: 14px;
}

.sound-wave-bars span {
  width: 3px;
  background: #a855f7;
  border-radius: 2px;
  animation: soundBar 1s infinite ease-in-out alternate;
}

.sound-wave-bars span:nth-child(1) { height: 60%; animation-delay: 0.1s; }
.sound-wave-bars span:nth-child(2) { height: 100%; animation-delay: 0.3s; }
.sound-wave-bars span:nth-child(3) { height: 40%; animation-delay: 0.2s; }
.sound-wave-bars span:nth-child(4) { height: 80%; animation-delay: 0.4s; }

@keyframes soundBar {
  0% { height: 20%; }
  100% { height: 100%; }
}

.row-playing td {
  background: rgba(168, 85, 247, 0.1) !important;
}
</style>

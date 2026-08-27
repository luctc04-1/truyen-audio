<template>
  <div class="setting-manager">
    <!-- Sub-navigation Tabs -->
    <div class="settings-nav-tabs">
      <button
        class="tab-btn"
        :class="{ active: currentTab === 'general' }"
        type="button"
        @click="currentTab = 'general'"
      >
        <i class="ri-settings-4-line"></i>
        <span>Cài đặt hệ thống & Storage</span>
      </button>
      <button
        class="tab-btn"
        :class="{ active: currentTab === 'seo' }"
        type="button"
        @click="currentTab = 'seo'"
      >
        <i class="ri-google-line"></i>
        <span>Tối ưu SEO & Chia sẻ Mạng xã hội</span>
      </button>
    </div>

    <!-- Tab 1: General & Storage Settings -->
    <div v-if="currentTab === 'general'" class="settings-grid">
      <div class="card-panel">
        <div class="panel-head">
          <div>
            <h3>Thông tin Website & Quy tắc Nghe</h3>
            <span class="panel-subtitle">Tên thương hiệu, tên miền và chính sách số tập miễn phí</span>
          </div>
        </div>

        <form class="settings-body" @submit.prevent="saveSettings">
          <div class="form-group">
            <label>Tên website hiển thị <span class="required">*</span></label>
            <input v-model="settings.site_name" type="text" required placeholder="Truyện Audio Hay" />
          </div>

          <div class="form-group">
            <label>Slogan / Khẩu hiệu trang</label>
            <input v-model="settings.site_description" type="text" placeholder="Kho truyện audio chọn lọc chất lượng cao" />
          </div>

          <div class="form-group">
            <label>Tên miền chính (Primary Domain)</label>
            <input v-model="settings.main_domain" type="url" placeholder="http://truyen-audio.me" />
          </div>

          <div class="form-group">
            <label>Số tập nghe thử miễn phí trước khi yêu cầu VIP</label>
            <div class="input-with-badge">
              <input v-model.number="settings.free_episodes_limit" type="number" min="0" placeholder="10" />
              <span class="input-badge">Tập</span>
            </div>
            <small class="help-text">Người dùng chưa có VIP sẽ nghe được từ tập 1 đến số tập này.</small>
          </div>

          <div class="form-group check-group">
            <label class="checkbox-label">
              <input v-model="settings.allow_registration" type="checkbox" />
              <span>Cho phép đăng ký tài khoản thành viên mới</span>
            </label>
          </div>

          <div class="panel-foot">
            <button class="btn btn-primary btn-glow" type="submit" :disabled="saving">
              <i v-if="saving" class="ri-loader-4-line ri-spin"></i>
              <i v-else class="ri-save-3-line"></i>
              <span>{{ saving ? 'Đang lưu...' : 'Lưu cài đặt hệ thống' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Storage Provider Selector Card -->
      <div class="card-panel">
        <div class="panel-head">
          <div>
            <h3>Nhà Lưu Trữ Audio (Storage Provider)</h3>
            <span class="panel-subtitle">Nơi lưu file mp3 và phân phối âm thanh trực tuyến</span>
          </div>
        </div>

        <div class="storage-selection-body">
          <div
            class="storage-card-option"
            :class="{ active: settings.storage_provider === 'local' }"
            @click="settings.storage_provider = 'local'"
          >
            <div class="storage-icon green"><i class="ri-hard-drive-2-line"></i></div>
            <div class="storage-details">
              <strong>Local Server Storage</strong>
              <p>Lưu trực tiếp trong thư mục storage/app/public trên máy chủ Laravel.</p>
            </div>
            <div class="storage-check"><i class="ri-checkbox-circle-fill"></i></div>
          </div>

          <div
            class="storage-card-option"
            :class="{ active: settings.storage_provider === 'supabase' }"
            @click="settings.storage_provider = 'supabase'"
          >
            <div class="storage-icon emerald"><i class="ri-flashlight-fill"></i></div>
            <div class="storage-details">
              <strong>Supabase Storage (CDN)</strong>
              <p>Phát qua Cloud Object Storage Supabase với tốc độ cao không tốn băng thông máy chủ.</p>
            </div>
            <div class="storage-check"><i class="ri-checkbox-circle-fill"></i></div>
          </div>

          <div
            class="storage-card-option"
            :class="{ active: settings.storage_provider === 's3' }"
            @click="settings.storage_provider = 's3'"
          >
            <div class="storage-icon blue"><i class="ri-cloud-fill"></i></div>
            <div class="storage-details">
              <strong>Amazon S3 / Cloudflare R2</strong>
              <p>Khả năng mở rộng vô hạn, tối ưu chi phí phân phối hàng triệu lượt nghe mỗi ngày.</p>
            </div>
            <div class="storage-check"><i class="ri-checkbox-circle-fill"></i></div>
          </div>

          <div class="panel-foot">
            <button class="btn btn-primary btn-glow" type="button" :disabled="saving" @click="saveSettings">
              <i v-if="saving" class="ri-loader-4-line ri-spin"></i>
              <i v-else class="ri-check-line"></i>
              <span>Cập nhật nhà lưu trữ</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 2: SEO Meta & Social Sharing -->
    <div v-else-if="currentTab === 'seo'" class="settings-grid">
      <div class="card-panel">
        <div class="panel-head">
          <div>
            <h3>Cấu hình Thẻ Meta SEO</h3>
            <span class="panel-subtitle">Tối ưu từ khóa và mô tả cho công cụ tìm kiếm</span>
          </div>
        </div>

        <form class="settings-body" @submit.prevent="saveSettings">
          <div class="form-group">
            <label>Meta Title mặc định <span class="required">*</span></label>
            <input v-model="settings.meta_title" type="text" required placeholder="Truyện Audio Hay | Nghe Truyện Online" />
          </div>

          <div class="form-group">
            <label>Meta Description mặc định <span class="required">*</span></label>
            <textarea v-model="settings.meta_description" rows="3" required placeholder="Website nghe truyện audio chọn lọc hay nhất..."></textarea>
          </div>

          <div class="form-group">
            <label>Link ảnh chia sẻ mạng xã hội (OG Image URL)</label>
            <input v-model="settings.og_image" type="text" placeholder="/android-chrome-512x512.png" />
          </div>

          <div class="form-group">
            <label>Chỉ mục tìm kiếm (Robots)</label>
            <select v-model="settings.robots">
              <option value="index,follow">index, follow (Cho phép Google index toàn bộ)</option>
              <option value="noindex,nofollow">noindex, nofollow (Bảo trì / Ẩn khỏi công cụ tìm kiếm)</option>
            </select>
          </div>

          <div class="panel-foot">
            <button class="btn btn-primary btn-glow" type="submit" :disabled="saving">
              <i v-if="saving" class="ri-loader-4-line ri-spin"></i>
              <i v-else class="ri-save-3-line"></i>
              <span>{{ saving ? 'Đang lưu...' : 'Lưu thiết lập SEO' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Live Google Search Preview Snippet -->
      <div class="card-panel">
        <div class="panel-head">
          <div>
            <h3>Google Search Live Preview</h3>
            <span class="panel-subtitle">Mô phỏng hiển thị trên trang kết quả tìm kiếm Google</span>
          </div>
        </div>

        <div class="google-preview-body">
          <div class="google-search-card">
            <div class="google-url-row">
              <span class="google-favicon"><i class="ri-global-line"></i></span>
              <div class="google-url-text">
                <strong>{{ settings.site_name || 'Truyện Audio' }}</strong>
                <small>{{ settings.main_domain || 'https://truyen-audio.me' }}</small>
              </div>
            </div>
            <h4 class="google-title">{{ settings.meta_title || 'Truyện Audio Hay | Nghe Truyện Online' }}</h4>
            <p class="google-desc">{{ settings.meta_description || 'Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya mượt mà...' }}</p>
          </div>

          <div class="social-share-preview">
            <span class="preview-label">Xem trước ảnh Open Graph (Social Card):</span>
            <div class="og-card">
              <img :src="settings.og_image || '/android-chrome-512x512.png'" alt="OG Preview" @error="handleOgError" />
              <div class="og-meta">
                <small>{{ (settings.main_domain || 'truyen-audio.me').replace(/https?:\/\//, '') }}</small>
                <strong>{{ settings.meta_title }}</strong>
                <p>{{ settings.meta_description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminService from '@/services/AdminService'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const toast = useToastStore()

const loading = ref(false)
const saving = ref(false)
const currentTab = ref('general')

const settings = reactive({
  site_name: 'Truyện Audio Hay',
  site_description: 'Kho truyện audio chọn lọc chất lượng cao',
  main_domain: 'http://truyen-audio.me',
  storage_provider: 'local',
  free_episodes_limit: 10,
  allow_registration: true,
  meta_title: 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online',
  meta_description: 'Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya mượt mà.',
  og_image: '/android-chrome-512x512.png',
  robots: 'index,follow',
})

async function fetchSettings() {
  loading.value = true
  try {
    const res = await AdminService.getSettings()
    const payload = extractApiPayload(res)
    if (payload) {
      Object.assign(settings, payload)
    }
  } catch (err) {
    console.error('Failed to load settings', err)
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    await AdminService.updateSettings(settings)
    toast.success('Lưu cấu hình hệ thống thành công!')
  } catch (err) {
    toast.error('Lỗi khi lưu cài đặt')
  } finally {
    saving.value = false
  }
}

function handleOgError(e) {
  e.target.src = '/android-chrome-512x512.png'
}

onMounted(() => {
  fetchSettings()
})
</script>

<style scoped>
.setting-manager {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

.settings-nav-tabs {
  display: flex;
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  border-radius: 14px;
  padding: 6px;
  gap: 6px;
  width: fit-content;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  border-radius: 10px;
  background: transparent;
  border: none;
  color: var(--admin-muted, #94a3b8);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover { color: #f8fafc; }
.tab-btn.active {
  background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35);
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 20px;
}

.settings-body,
.storage-selection-body,
.google-preview-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
  margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  background: #181b26;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f8fafc;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #a855f7;
  box-shadow: 0 0 12px rgba(168, 85, 247, 0.25);
}

.input-with-badge {
  position: relative;
  display: flex;
  align-items: center;
}

.input-badge {
  position: absolute;
  right: 12px;
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
  font-weight: 600;
  pointer-events: none;
}

.help-text {
  display: block;
  margin-top: 4px;
  font-size: 12px;
  color: var(--admin-faint, #64748b);
}

.checkbox-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #e2e8f0;
  cursor: pointer;
}

.required { color: #fb7185; }

.panel-foot {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  justify-content: flex-end;
}

/* Storage Cards */
.storage-card-option {
  background: rgba(24, 27, 38, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.storage-card-option:hover {
  background: rgba(30, 34, 48, 0.85);
  border-color: rgba(255, 255, 255, 0.16);
}

.storage-card-option.active {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(24, 27, 38, 0.9) 100%);
  border-color: #a855f7;
  box-shadow: 0 4px 16px rgba(168, 85, 247, 0.2);
}

.storage-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}

.storage-icon.green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.storage-icon.emerald { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }
.storage-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }

.storage-details {
  flex: 1;
}

.storage-details strong {
  display: block;
  font-size: 14px;
  color: #f8fafc;
  margin-bottom: 2px;
}

.storage-details p {
  margin: 0;
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
  line-height: 1.4;
}

.storage-check {
  font-size: 20px;
  color: #a855f7;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.storage-card-option.active .storage-check {
  opacity: 1;
}

/* Google Search Snippet Card */
.google-search-card {
  background: #202124;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.google-url-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.google-favicon {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #303134;
  color: #9aa0a6;
  display: grid;
  place-items: center;
  font-size: 12px;
}

.google-url-text strong {
  display: block;
  font-size: 12px;
  color: #dadce0;
}

.google-url-text small {
  font-size: 11px;
  color: #9aa0a6;
}

.google-title {
  margin: 0;
  font-size: 17px;
  font-weight: 500;
  color: #8ab4f8;
  line-height: 1.3;
}

.google-desc {
  margin: 0;
  font-size: 13px;
  color: #bdc1c6;
  line-height: 1.4;
}

.social-share-preview {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preview-label {
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
  font-weight: 600;
}

.og-card {
  border-radius: 12px;
  overflow: hidden;
  background: #181b26;
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
}

.og-card img {
  width: 100px;
  height: 80px;
  object-fit: cover;
  flex-shrink: 0;
}

.og-meta {
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.og-meta small {
  font-size: 11px;
  color: #94a3b8;
  text-transform: uppercase;
}

.og-meta strong {
  font-size: 13px;
  color: #f8fafc;
  max-width: 320px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.og-meta p {
  margin: 0;
  font-size: 11px;
  color: #64748b;
  max-width: 320px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>

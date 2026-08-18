<template>
  <div class="sync-manager">
    <!-- Live DB Stats Cards -->
    <div class="sync-stats-grid">
      <div class="sync-card stat-blue">
        <div class="card-glow"></div>
        <div class="card-icon blue"><i class="ri-book-3-line"></i></div>
        <div class="card-body">
          <span class="card-label">Truyện trong Database</span>
          <h3 class="card-val">{{ dbStatus.series_count || 0 }} <small>bộ</small></h3>
          <span class="card-sub">Nguồn Supabase & Crawl</span>
        </div>
      </div>

      <div class="sync-card stat-emerald">
        <div class="card-glow"></div>
        <div class="card-icon emerald"><i class="ri-headphone-fill"></i></div>
        <div class="card-body">
          <span class="card-label">Tập Audio trong DB</span>
          <h3 class="card-val">{{ dbStatus.episodes_count || 0 }} <small>tập</small></h3>
          <span class="card-sub">Đã lưu trữ đường dẫn audio</span>
        </div>
      </div>

      <div class="sync-card stat-purple">
        <div class="card-glow"></div>
        <div class="card-icon purple"><i class="ri-database-2-line"></i></div>
        <div class="card-body">
          <span class="card-label">Trạng thái kết nối</span>
          <h3 class="card-val text-success">
            <span class="pulse-dot"></span> Sẵn sàng
          </h3>
          <span class="card-sub">PostgreSQL Cloud Ready</span>
        </div>
      </div>
    </div>

    <!-- Actions Control Panel -->
    <div class="card-panel">
      <div class="panel-head">
        <div>
          <h3>Trung tâm Đồng bộ & Crawler Dữ liệu</h3>
          <span class="panel-subtitle">Kéo dữ liệu truyện và tập audio từ nguồn ngoài Supabase vào hệ thống</span>
        </div>
        <button class="btn btn-ghost btn-sm" type="button" :disabled="loading" @click="fetchStatus">
          <i class="ri-refresh-line" :class="{ 'ri-spin': loading }"></i>
          <span>Kiểm tra trạng thái</span>
        </button>
      </div>

      <!-- 🔐 SUPABASE AUTH KEY INPUT CARD -->
      <div class="auth-key-card" :class="{ 'has-key': hasValidKey }">
        <div class="auth-key-header">
          <div class="auth-key-title">
            <span class="key-icon"><i class="ri-key-2-fill"></i></span>
            <div>
              <strong>Khóa xác thực Supabase (SUPABASE_AUTH_KEY) <span class="required">*</span></strong>
              <p>Bắt buộc nhập Bearer Token / Service Key để mở khóa các tính năng đồng bộ trên giao diện</p>
            </div>
          </div>
          <div class="key-status-pill" :class="hasValidKey ? 'pill-ready' : 'pill-empty'">
            <span class="status-dot"></span>
            <span>{{ hasValidKey ? 'Đã nhập Key • Sẵn sàng' : 'Chưa có Key • Đang khóa' }}</span>
          </div>
        </div>

        <div class="auth-key-input-wrapper">
          <div class="input-with-actions">
            <i class="ri-shield-keyhole-line prefix-icon"></i>
            <input
              v-model="supabaseAuthKey"
              :type="showKey ? 'text' : 'password'"
              placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9... (Dán SUPABASE_AUTH_KEY vào đây)"
              autocomplete="off"
              spellcheck="false"
              class="auth-key-input"
              @input="onKeyChange"
            />
            <button
              v-if="supabaseAuthKey"
              class="btn-icon-action"
              type="button"
              :title="showKey ? 'Ẩn khóa' : 'Hiện khóa'"
              @click="showKey = !showKey"
            >
              <i :class="showKey ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
            </button>
            <button
              v-if="supabaseAuthKey"
              class="btn-icon-action btn-clear"
              type="button"
              title="Xóa khóa"
              @click="clearKey"
            >
              <i class="ri-close-line"></i>
            </button>
          </div>
        </div>

        <div class="auth-key-help">
          <i class="ri-information-line"></i>
          <span>
            <strong>Ghi chú:</strong> Khi chạy lệnh terminal <code>php artisan sync:supabase</code>, hệ thống sẽ tự động dùng key cấu hình trong file <code>.env</code>.
          </span>
        </div>
      </div>

      <!-- Sync Actions Grid -->
      <div class="sync-actions-grid">
        <div class="action-card action-all" :class="{ 'disabled-card': !hasValidKey }">
          <div class="action-info">
            <div class="action-icon purple">
              <i class="ri-cloud-windy-line"></i>
            </div>
            <div>
              <h4>Đồng bộ toàn bộ dữ liệu</h4>
              <p>Chạy đồng bộ toàn bộ Truyện và tự động kéo toàn bộ Tập audio từ Supabase vào hệ thống.</p>
            </div>
          </div>
          <button
            class="btn btn-primary btn-glow"
            type="button"
            :disabled="isSyncing || !hasValidKey"
            :title="!hasValidKey ? 'Vui lòng nhập SUPABASE_AUTH_KEY để mở khóa' : ''"
            @click="triggerSyncAll"
          >
            <i :class="isSyncing && activeSync === 'all' ? 'ri-loader-4-line ri-spin' : 'ri-play-fill'"></i>
            <span>{{ isSyncing && activeSync === 'all' ? 'Đang đồng bộ...' : 'Chạy đồng bộ tất cả' }}</span>
          </button>
        </div>
      </div>

      <!-- Live Terminal / Sync Log -->
      <div class="sync-logs-wrapper">
        <div class="logs-header">
          <div class="terminal-dots">
            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>
          </div>
          <span class="terminal-title"><i class="ri-terminal-box-line"></i> Live Sync Terminal</span>
          <button v-if="logs.length" class="btn-clear-log" type="button" @click="logs = []">
            <i class="ri-eraser-line"></i> Dọn log màn hình
          </button>
        </div>
        <div class="logs-console">
          <div v-if="!logs.length" class="log-placeholder">
            <span class="prompt">$</span> Nhập SUPABASE_AUTH_KEY và nhấn một trong các nút bên trên để kích hoạt tiến trình đồng bộ dữ liệu...
          </div>
          <div v-for="(log, idx) in logs" :key="idx" class="log-line" :class="`log-${log.type}`">
            <span class="log-time">[{{ log.time }}]</span>
            <span class="log-tag" :class="`tag-${log.type}`">[{{ log.type.toUpperCase() }}]</span>
            <span class="log-msg">{{ log.message }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AdminService from '@/services/AdminService'
import { useToastStore } from '@/stores/toastStore'
import { extractApiPayload } from '@/utils/helpers'

const toast = useToastStore()

const loading = ref(false)
const isSyncing = ref(false)
const activeSync = ref('')
const supabaseAuthKey = ref(sessionStorage.getItem('ADMIN_SUPABASE_AUTH_KEY') || '')
const showKey = ref(false)

const dbStatus = reactive({
  series_count: 0,
  episodes_count: 0,
})

const logs = ref([])

const hasValidKey = computed(() => {
  return Boolean(supabaseAuthKey.value && supabaseAuthKey.value.trim().length > 0)
})

function onKeyChange() {
  if (supabaseAuthKey.value) {
    sessionStorage.setItem('ADMIN_SUPABASE_AUTH_KEY', supabaseAuthKey.value.trim())
  } else {
    sessionStorage.removeItem('ADMIN_SUPABASE_AUTH_KEY')
  }
}

function clearKey() {
  supabaseAuthKey.value = ''
  sessionStorage.removeItem('ADMIN_SUPABASE_AUTH_KEY')
  addLog('Đã xóa SUPABASE_AUTH_KEY khỏi phiên làm việc.', 'info')
}

function addLog(message, type = 'info') {
  const time = new Date().toLocaleTimeString('vi-VN')
  logs.value.unshift({ time, message, type })
}

async function fetchStatus() {
  loading.value = true
  try {
    const res = await AdminService.getSyncStatus()
    const payload = extractApiPayload(res)
    if (payload) {
      dbStatus.series_count = payload.series_count || payload.total_series || 0
      dbStatus.episodes_count = payload.episodes_count || payload.total_episodes || 0
    }
    addLog(`Kiểm tra DB hoàn tất: ${dbStatus.series_count} truyện, ${dbStatus.episodes_count} tập audio.`, 'success')
  } catch (err) {
    addLog('Không thể kết nối đến máy chủ để lấy trạng thái database.', 'error')
  } finally {
    loading.value = false
  }
}

async function triggerSyncAll() {
  if (!hasValidKey.value) {
    toast.error('Vui lòng nhập SUPABASE_AUTH_KEY trước khi đồng bộ!')
    return
  }

  isSyncing.value = true
  activeSync.value = 'all'
  addLog('Bắt đầu tiến trình đồng bộ toàn bộ (Series -> Episodes)...', 'info')

  try {
    const res = await AdminService.syncAll(supabaseAuthKey.value.trim())
    const payload = extractApiPayload(res)
    addLog(`Đồng bộ toàn bộ thành công: ${JSON.stringify(payload)}`, 'success')
    toast.success('Đồng bộ toàn bộ thành công!')
    fetchStatus()
  } catch (err) {
    const errMsg = err.response?.data?.message || err.message || 'Không xác định'
    addLog(`Lỗi đồng bộ: ${errMsg}`, 'error')
    toast.error(errMsg)
  } finally {
    isSyncing.value = false
    activeSync.value = ''
  }
}

onMounted(() => {
  fetchStatus()
})
</script>

<style scoped>
.sync-manager {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

.sync-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.sync-card {
  background: var(--admin-card-bg, rgba(15, 18, 28, 0.75));
  backdrop-filter: blur(16px);
  border: 1px solid var(--admin-border, rgba(255, 255, 255, 0.08));
  border-radius: 16px;
  padding: 20px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  position: relative;
  overflow: hidden;
  transition: all 0.25s var(--ease-spring);
}

.sync-card:hover {
  transform: translateY(-3px);
  border-color: rgba(255, 255, 255, 0.16);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
}

.card-glow {
  position: absolute;
  top: -30px;
  right: -30px;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  filter: blur(30px);
  opacity: 0.25;
  pointer-events: none;
}

.stat-blue .card-glow { background: #3b82f6; }
.stat-emerald .card-glow { background: #10b981; }
.stat-purple .card-glow { background: #a855f7; }

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 22px;
  flex-shrink: 0;
}

.card-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; box-shadow: 0 0 12px rgba(59, 130, 246, 0.2); }
.card-icon.emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; box-shadow: 0 0 12px rgba(16, 185, 129, 0.2); }
.card-icon.purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; box-shadow: 0 0 12px rgba(168, 85, 247, 0.2); }

.card-body {
  display: flex;
  flex-direction: column;
}

.card-label { font-size: 13px; color: var(--admin-muted, #94a3b8); margin-bottom: 2px; font-weight: 500; }
.card-val { margin: 0 0 2px 0; font-size: 22px; font-weight: 800; color: #f8fafc; letter-spacing: -0.02em; }
.card-val small { font-size: 13px; font-weight: 600; color: var(--admin-muted, #94a3b8); }
.card-sub { font-size: 12px; color: var(--admin-faint, #64748b); }

.pulse-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 8px #10b981;
  animation: pulseGlow 1.5s infinite;
  margin-right: 4px;
}

/* 🔐 AUTH KEY CONFIG CARD */
.auth-key-card {
  margin: 20px 24px 8px 24px;
  background: rgba(18, 22, 34, 0.85);
  border: 1px solid rgba(245, 158, 11, 0.35);
  border-radius: 16px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: all 0.25s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.auth-key-card.has-key {
  border-color: rgba(16, 185, 129, 0.4);
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(18, 22, 34, 0.9) 100%);
}

.auth-key-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}

.auth-key-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.key-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}

.auth-key-card.has-key .key-icon {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
}

.auth-key-title strong {
  display: block;
  font-size: 14px;
  color: #f8fafc;
}

.auth-key-title p {
  margin: 2px 0 0 0;
  font-size: 12px;
  color: var(--admin-muted, #94a3b8);
}

.required {
  color: #fb7185;
}

.key-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 700;
}

.pill-empty {
  background: rgba(244, 63, 94, 0.15);
  color: #fb7185;
  border: 1px solid rgba(244, 63, 94, 0.3);
}

.pill-empty .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #fb7185;
}

.pill-ready {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.pill-ready .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 6px #34d399;
}

.auth-key-input-wrapper {
  width: 100%;
}

.input-with-actions {
  display: flex;
  align-items: center;
  background: #090a0f;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 4px 12px;
  gap: 8px;
  transition: all 0.2s ease;
}

.input-with-actions:focus-within {
  border-color: #a855f7;
  box-shadow: 0 0 16px rgba(168, 85, 247, 0.25);
}

.prefix-icon {
  color: var(--admin-muted, #94a3b8);
  font-size: 17px;
}

.auth-key-input {
  flex: 1;
  background: transparent;
  border: none;
  color: #f8fafc;
  font-family: var(--admin-font-mono);
  font-size: 13px;
  padding: 8px 0;
  outline: none;
  min-width: 0;
}

.btn-icon-action {
  background: transparent;
  border: none;
  color: var(--admin-muted, #94a3b8);
  cursor: pointer;
  padding: 4px;
  font-size: 16px;
  display: grid;
  place-items: center;
  border-radius: 6px;
  transition: color 0.15s ease;
}

.btn-icon-action:hover {
  color: #f8fafc;
}

.btn-clear:hover {
  color: #fb7185;
}

.auth-key-help {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #94a3b8;
}

.auth-key-help i {
  color: #a855f7;
  font-size: 14px;
}

.auth-key-help code {
  background: rgba(255, 255, 255, 0.08);
  padding: 2px 6px;
  border-radius: 4px;
  color: #c084fc;
  font-family: var(--admin-font-mono);
  font-size: 11px;
}

/* Actions Grid */
.sync-actions-grid {
  padding: 16px 24px;
  display: grid;
  gap: 14px;
}

.action-card {
  background: rgba(24, 27, 38, 0.7);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  transition: all 0.2s ease;
}

.action-card:hover {
  background: rgba(30, 34, 48, 0.85);
  border-color: rgba(255, 255, 255, 0.12);
}

.disabled-card {
  opacity: 0.6;
}

.action-all {
  border-color: rgba(168, 85, 247, 0.35);
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(24, 27, 38, 0.8) 100%);
}

.action-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.action-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 20px;
  flex-shrink: 0;
}

.action-icon.purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
.action-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.action-icon.emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }

.action-info h4 { margin: 0 0 3px 0; font-size: 15px; font-weight: 700; color: #f8fafc; }
.action-info p { margin: 0; font-size: 13px; color: var(--admin-muted, #94a3b8); line-height: 1.4; }

.sync-logs-wrapper {
  margin: 0 24px 24px 24px;
  background: #090a0f;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.5);
}

.logs-header {
  padding: 10px 16px;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.terminal-dots {
  display: flex;
  gap: 6px;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.dot.red { background: #f43f5e; }
.dot.yellow { background: #f59e0b; }
.dot.green { background: #10b981; }

.terminal-title {
  font-size: 12px;
  font-family: var(--admin-font-mono);
  color: var(--admin-muted, #94a3b8);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-clear-log {
  background: transparent;
  border: none;
  color: var(--admin-muted, #94a3b8);
  cursor: pointer;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-clear-log:hover { color: #f8fafc; }

.logs-console {
  padding: 16px;
  max-height: 240px;
  overflow-y: auto;
  font-family: var(--admin-font-mono);
  font-size: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.log-placeholder {
  color: #475569;
  font-style: italic;
}

.prompt {
  color: #a855f7;
  font-weight: 700;
}

.log-line {
  display: flex;
  gap: 8px;
  line-height: 1.4;
}

.log-time { color: var(--admin-faint, #64748b); flex-shrink: 0; }
.log-tag { font-weight: 700; font-size: 11px; flex-shrink: 0; }
.tag-info { color: #60a5fa; }
.tag-success { color: #34d399; }
.tag-error { color: #fb7185; }

.log-info .log-msg { color: #cbd5e1; }
.log-success .log-msg { color: #34d399; }
.log-error .log-msg { color: #fb7185; }
</style>

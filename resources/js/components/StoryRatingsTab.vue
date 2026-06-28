<template>
  <div class="story-ratings">
    <div v-if="loading" class="ratings-loading">
      <div v-for="n in 2" :key="n" class="card rating-skeleton">
        <div class="sk sk-line" style="width:40%;"></div>
        <div class="sk sk-line" style="width:80%;"></div>
      </div>
    </div>

    <template v-else>
      <div class="rating-summary card">
        <div class="rating-summary-main">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1">
            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.60l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" />
          </svg>
          <span class="rating-average">{{ summary.average.toFixed(1) }}</span>
          <span class="rating-count">({{ summary.count }} đánh giá)</span>
        </div>
        <p v-if="userRating" class="rating-owned">Bạn đã đánh giá bộ truyện này</p>
      </div>

      <div v-if="auth.isAuthenticated && !userRating" class="card rating-form">
        <p class="rating-form-label">Chọn số sao đánh giá của bạn</p>
        <StarRating v-model="draftRating" />
        <textarea
          v-model="draftContent"
          placeholder="Chia sẻ cảm nhận của bạn (tuỳ chọn)..."
          maxlength="1000"
          rows="3"
          class="rating-textarea"
        ></textarea>
        <div class="rating-form-footer">
          <span class="char-count">{{ draftContent.length }}/1000</span>
          <button
            class="btn btn-primary btn-sm"
            :disabled="!draftRating || submitting"
            @click="submit"
          >
            {{ submitting ? 'Đang gửi...' : 'Gửi đánh giá' }}
          </button>
        </div>
      </div>

      <div v-else-if="!auth.isAuthenticated" class="card rating-login-hint">
        <p>Đăng nhập để đánh giá bộ truyện này.</p>
        <button class="btn btn-outline btn-sm" @click="goAuth">Đăng nhập</button>
      </div>

      <div v-if="!items.length" class="empty-hint">
        Chưa có đánh giá nào. Hãy là người đầu tiên!
      </div>

      <div v-else class="rating-list">
        <div v-for="item in items" :key="item.id" class="card rating-card">
          <div class="rating-card-header">
            <div class="user-avatar">{{ item.user?.initial || 'U' }}</div>
            <div class="rating-card-body">
              <div class="user-name-row">
                <span class="user-name">{{ item.user?.username || 'Người dùng' }}</span>
                <VipBadge v-if="item.user?.is_premium" size="sm" />
                <StarRating :model-value="item.rating" readonly />
              </div>
              <div class="time">{{ formatRelativeTime(item.created_at) }}</div>
              <p v-if="item.content" class="rating-content">{{ item.content }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import ReviewService from '@/services/ReviewService'
import { formatRelativeTime } from '@/utils/helpers'
import StarRating from '@/components/StarRating.vue'
import VipBadge from '@/components/VipBadge.vue'

const props = defineProps({
  seriesId: { type: String, required: true },
})

const emit = defineEmits(['rated', 'count-change'])

const auth = useAuthStore()
const router = useRouter()
const toast = useToastStore()

const loading = ref(true)
const submitting = ref(false)
const draftRating = ref(0)
const draftContent = ref('')
const summary = ref({ average: 0, count: 0 })
const userRating = ref(null)
const items = ref([])

const load = async () => {
  if (!props.seriesId) return
  loading.value = true
  try {
    const data = await ReviewService.getRatings(props.seriesId)
    summary.value = data.summary ?? { average: 0, count: 0 }
    userRating.value = data.user_rating ?? null
    items.value = data.items ?? []
    emit('count-change', summary.value.count)
    emit('rated', summary.value.average)
  } catch {
    toast.error('Không tải được đánh giá')
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  if (!draftRating.value) return
  submitting.value = true
  try {
    await ReviewService.submitRating(props.seriesId, {
      rating: draftRating.value,
      content: draftContent.value.trim() || null,
    })
    toast.success('Đánh giá thành công')
    draftRating.value = 0
    draftContent.value = ''
    await load()
  } catch (error) {
    toast.error(error.message || 'Gửi đánh giá thất bại')
  } finally {
    submitting.value = false
  }
}

const goAuth = () => router.push('/auth')

onMounted(load)
watch(() => props.seriesId, load)
</script>

<style scoped>
.story-ratings {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.rating-summary {
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.rating-summary-main {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rating-summary-main svg {
  width: 28px;
  height: 28px;
}

.rating-average {
  font-size: 24px;
  font-weight: 700;
}

.rating-count {
  font-size: 14px;
  color: var(--text-muted);
}

.rating-owned {
  font-size: 13px;
  color: var(--amber);
  margin: 0;
}

.rating-form,
.rating-login-hint {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: stretch;
}

.rating-form-label {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0;
}

.rating-textarea {
  width: 100%;
  resize: vertical;
  min-height: 72px;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--bg-muted);
  color: var(--text);
  font-family: inherit;
  font-size: 14px;
  line-height: 1.5;
  outline: none;
}

.rating-form-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.char-count {
  font-size: 12px;
  color: var(--text-faint, #71717a);
}

.rating-login-hint {
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
}

.rating-login-hint p {
  margin: 0;
  font-size: 14px;
  color: var(--text-muted);
}

.rating-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.rating-card {
  padding: 16px;
}

.rating-card-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.rating-card-body {
  flex: 1;
  min-width: 0;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(168, 85, 247, 0.12);
  color: #a855f7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 13px;
  flex-shrink: 0;
}

.user-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-weight: 600;
  font-size: 14px;
}

.time {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 4px;
}

.rating-content {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text);
  margin: 10px 0 0;
  white-space: pre-wrap;
}

.empty-hint {
  text-align: center;
  padding: 24px;
  color: var(--text-muted);
  font-size: 14px;
}

.rating-skeleton {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sk {
  background: var(--bg-muted);
  border-radius: 8px;
  height: 12px;
}

.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 16px;
  height: 36px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  font-family: inherit;
}

.btn-primary {
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
  color: #fff;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border-strong, #3f3f46);
  color: var(--text);
}

.btn-sm {
  height: 32px;
  padding: 0 12px;
  font-size: 13px;
}
</style>

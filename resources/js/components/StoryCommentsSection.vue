<template>
  <section class="story-comments">
    <h2 class="comments-heading">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
      </svg>
      Bình luận ({{ total }})
    </h2>

    <div v-if="auth.isAuthenticated" class="card comment-compose">
      <p class="compose-as">Bình luận với tên: <strong>{{ auth.displayName }}</strong></p>
      <textarea
        v-model="draft"
        placeholder="Nhập bình luận của bạn..."
        maxlength="1000"
        rows="4"
        :disabled="submitting"
      ></textarea>
      <div class="compose-footer">
        <span class="char-count">{{ draft.length }}/1000</span>
        <button
          class="btn btn-primary btn-sm"
          :disabled="!draft.trim() || submitting"
          @click="submit()"
        >
          <ButtonSpinner v-if="submitting" variant="light" :size="14" />
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
          </svg>
          Gửi bình luận
        </button>
      </div>
    </div>

    <div v-else class="card comment-login-hint">
      <p>Đăng nhập để bình luận về bộ truyện này.</p>
      <button class="btn btn-outline btn-sm" @click="goAuth">Đăng nhập</button>
    </div>

    <div v-if="loading" class="comments-loading">
      <div v-for="n in 3" :key="n" class="card comment-skeleton">
        <div class="sk sk-line" style="width:30%;"></div>
        <div class="sk sk-line" style="width:90%;"></div>
      </div>
    </div>

    <div v-else-if="!items.length" class="empty-hint">
      Chưa có bình luận nào. Hãy là người đầu tiên!
    </div>

    <div v-else class="comment-list">
      <div v-for="item in items" :key="item.id" class="card comment-card">
        <CommentItem
          :comment="item"
          :deleting-comment-id="deletingCommentId"
          :is-replying="replyingTo === item.id"
          @like="toggleLike"
          @reply="startReply"
          @delete="requestDelete"
          @pin="togglePin"
        />

        <div v-if="replyingTo === item.id" class="reply-compose">
          <textarea
            v-model="replyDraft"
            placeholder="Nhập phản hồi của bạn..."
            maxlength="1000"
            rows="3"
            :disabled="submitting"
          ></textarea>
          <div class="reply-actions">
            <button class="btn btn-outline btn-sm" :disabled="submitting" @click="cancelReply">Huỷ</button>
            <button
              class="btn btn-primary btn-sm"
              :disabled="!replyDraft.trim() || submitting"
              @click="submit(item.id)"
            >
              <ButtonSpinner v-if="submitting" variant="light" :size="14" />
              Gửi phản hồi
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="hasMore" class="load-more">
      <button class="btn btn-outline btn-sm" :disabled="loadingMore" @click="loadMore">
        <ButtonSpinner v-if="loadingMore" variant="muted" :size="14" />
        Xem thêm bình luận
      </button>
    </div>

    <ConfirmDialog
      v-model="showDeleteConfirm"
      :auto-close="false"
      :confirm-loading="deleteConfirmLoading"
      variant="danger"
      title="Xóa bình luận"
      message="Bạn có chắc muốn xóa bình luận này? Hành động này không thể hoàn tác."
      confirm-text="Xóa"
      cancel-text="Huỷ"
      @confirm="confirmDelete"
    />
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, provide, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import ReviewService from '@/services/ReviewService'
import { findNestedComment, optimisticToggleLike } from '@/utils/commentHelpers'
import CommentItem from '@/components/CommentItem.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const props = defineProps({
  seriesId: { type: String, required: true },
})

const auth = useAuthStore()
const router = useRouter()
const toast = useToastStore()

const loading = ref(true)
const loadingMore = ref(false)
const submitting = ref(false)
const draft = ref('')
const replyDraft = ref('')
const replyingTo = ref(null)
const items = ref([])
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)

const showDeleteConfirm = ref(false)
const deleteConfirmLoading = ref(false)
const pendingDelete = ref(null)
const deletingCommentId = ref(null)
const likingCommentIds = reactive(new Set())

const hasMore = computed(() => page.value < lastPage.value)

const sortItems = () => {
  items.value = [...items.value].sort((a, b) => {
    if (a.is_pinned !== b.is_pinned) return b.is_pinned - a.is_pinned
    return new Date(b.created_at) - new Date(a.created_at)
  })
}

const load = async (append = false) => {
  if (!props.seriesId) return
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
    page.value = 1
  }

  try {
    const data = await ReviewService.getComments(props.seriesId, { page: page.value, per_page: 20 })
    const newItems = data.items ?? []
    items.value = append ? [...items.value, ...newItems] : newItems
    lastPage.value = data.pagination?.last_page ?? 1
    total.value = data.pagination?.total_all ?? data.pagination?.total ?? newItems.length
  } catch {
    toast.error('Không tải được bình luận')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  page.value += 1
  await load(true)
}

const submit = async (parentId = null) => {
  const content = (parentId ? replyDraft.value : draft.value).trim()
  if (!content || submitting.value) return

  submitting.value = true
  try {
    const created = await ReviewService.submitComment(props.seriesId, content, parentId)

    if (parentId) {
      const parent = items.value.find((c) => c.id === parentId)
      if (parent) {
        parent.replies = [...(parent.replies || []), created]
      }
      replyDraft.value = ''
      replyingTo.value = null
    } else {
      items.value = [created, ...items.value]
      sortItems()
      draft.value = ''
    }

    total.value += 1
    toast.success(parentId ? 'Gửi phản hồi thành công' : 'Gửi bình luận thành công')
  } catch (error) {
    toast.error(error.message || 'Gửi bình luận thất bại')
  } finally {
    submitting.value = false
  }
}

const startReply = (comment) => {
  if (!auth.isAuthenticated) {
    goAuth()
    return
  }
  if (replyingTo.value === comment.id) {
    cancelReply()
    return
  }
  replyingTo.value = comment.id
  replyDraft.value = ''
}

const cancelReply = () => {
  if (submitting.value) return
  replyingTo.value = null
  replyDraft.value = ''
}

const findComment = (id) => findNestedComment(items.value, id)

const toggleLike = async (comment) => {
  if (!auth.isAuthenticated) {
    goAuth()
    return
  }

  await optimisticToggleLike({
    target: findComment(comment.id),
    id: comment.id,
    pendingSet: likingCommentIds,
    apiCall: () => ReviewService.toggleLike(comment.id),
    onError: (error) => toast.error(error.message || 'Không thể thích bình luận'),
  })
}

const togglePin = async (comment) => {
  try {
    const updated = await ReviewService.pinComment(comment.id, !comment.is_pinned)
    const idx = items.value.findIndex((c) => c.id === comment.id)
    if (idx >= 0) {
      items.value[idx] = updated
      sortItems()
    }
    toast.success(updated.is_pinned ? 'Đã ghim bình luận' : 'Đã bỏ ghim')
  } catch (error) {
    toast.error(error.message || 'Không thể ghim bình luận')
  }
}

const editComment = async (comment, content) => {
  if (!content?.trim()) return false

  try {
    const updated = await ReviewService.updateComment(comment.id, content.trim())
    const target = findComment(comment.id)
    if (target) {
      target.content = updated.content
      target.is_edited = updated.is_edited
      target.updated_at = updated.updated_at
    }
    toast.success('Đã cập nhật bình luận')
    return true
  } catch (error) {
    toast.error(error.message || 'Cập nhật bình luận thất bại')
    return false
  }
}

provide('updateComment', editComment)

const requestDelete = (comment) => {
  pendingDelete.value = comment
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  const comment = pendingDelete.value
  if (!comment || deleteConfirmLoading.value) return

  deleteConfirmLoading.value = true
  deletingCommentId.value = comment.id

  try {
    await ReviewService.deleteComment(comment.id)
    const isTopLevel = items.value.some((c) => c.id === comment.id)

    if (isTopLevel) {
      items.value = items.value.filter((c) => c.id !== comment.id)
    } else {
      for (const item of items.value) {
        if (item.replies?.length) {
          item.replies = item.replies.filter((r) => r.id !== comment.id)
        }
      }
    }

    total.value = Math.max(0, total.value - 1)
    toast.success('Đã xóa bình luận')
    showDeleteConfirm.value = false
    pendingDelete.value = null
  } catch (error) {
    toast.error(error.message || 'Xóa bình luận thất bại')
  } finally {
    deleteConfirmLoading.value = false
    deletingCommentId.value = null
  }
}

const goAuth = () => router.push('/auth')

onMounted(() => load())
watch(() => props.seriesId, () => load())
</script>

<style scoped>
.story-comments {
  margin-top: 32px;
  padding-top: 8px;
  padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
}

@media (max-width: 768px) {
  .story-comments {
    padding-bottom: calc(140px + env(safe-area-inset-bottom, 0px));
  }
}

.comments-heading {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 16px;
}

.comments-heading svg {
  width: 20px;
  height: 20px;
  color: var(--amber, #f59e0b);
}

.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
}

.comment-compose {
  padding: 16px;
  margin-bottom: 16px;
}

.compose-as {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0 0 10px;
}

.compose-as strong {
  color: var(--amber, #f59e0b);
}

.comment-compose textarea,
.reply-compose textarea {
  width: 100%;
  resize: vertical;
  min-height: 96px;
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

.comment-compose textarea:disabled,
.reply-compose textarea:disabled {
  opacity: 0.7;
}

.reply-compose textarea {
  min-height: 72px;
}

.comment-compose textarea:focus,
.reply-compose textarea:focus {
  border-color: rgba(168, 85, 247, 0.5);
}

.compose-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10px;
}

.char-count {
  font-size: 12px;
  color: var(--text-faint, #71717a);
}

.comment-login-hint {
  padding: 16px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.comment-login-hint p {
  margin: 0;
  font-size: 14px;
  color: var(--text-muted);
}

.comment-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.comment-card {
  overflow: visible;
}

.reply-compose {
  padding: 0 16px 16px;
  border-top: 1px solid var(--border);
  margin-top: 0;
  padding-top: 12px;
}

.reply-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.empty-hint {
  text-align: center;
  padding: 24px;
  color: var(--text-muted);
  font-size: 14px;
}

.comment-skeleton {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 12px;
}

.sk {
  background: var(--bg-muted);
  border-radius: 8px;
  height: 12px;
}

.load-more {
  text-align: center;
  margin-top: 16px;
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
  background: linear-gradient(135deg, #d97706, #ea580c);
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

.btn-outline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-sm {
  height: 32px;
  padding: 0 12px;
}
</style>

<template>
  <div :class="['comment-item', { nested: isReply, busy: isDeleting }]">
    <div v-if="isDeleting" class="comment-busy-overlay" aria-hidden="true">
      <span class="comment-spinner"></span>
    </div>

    <div v-if="comment.is_pinned && !isReply" class="pinned-label">
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1">
        <path d="M12 17v5" /><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 7 15.66V17h10v-1.34a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1z" />
      </svg>
      Bình luận đã ghim
    </div>

    <div class="comment-header">
      <div class="user-avatar">{{ comment.user?.initial || 'U' }}</div>
      <div class="comment-meta">
        <div class="user-name-row">
          <span class="user-name">{{ comment.user?.username || 'Người dùng' }}</span>
          <VipBadge v-if="comment.user?.is_premium" size="sm" />
        </div>
        <div class="time">
          {{ formatRelativeTime(comment.created_at) }}
          <span v-if="comment.is_edited" class="edited-tag">· đã chỉnh sửa</span>
        </div>
      </div>
      <div v-if="!editing" class="comment-actions-top">
        <button
          v-if="canEdit"
          class="icon-action"
          title="Chỉnh sửa"
          :disabled="isDeleting"
          @click="startEdit"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 20h9" /><path d="M16.376 3.622a1 1 0 0 1 1.414 0l3.586 3.586a1 1 0 0 1 0 1.414l-9.586 9.586a2 2 0 0 1-.707.464l-3.22 1.073a1 1 0 0 1-1.263-1.263l1.073-3.22a2 2 0 0 1 .464-.707z" />
          </svg>
        </button>
        <button
          v-if="auth.isAdmin && !isReply"
          class="icon-action"
          :title="comment.is_pinned ? 'Bỏ ghim' : 'Ghim'"
          :disabled="isDeleting"
          @click="$emit('pin', comment)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="comment.is_pinned ? '#f59e0b' : 'none'" :stroke="comment.is_pinned ? '#f59e0b' : 'currentColor'" stroke-width="2">
            <path d="M12 17v5" /><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 7 15.66V17h10v-1.34a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1z" />
          </svg>
        </button>
        <button
          v-if="canDelete"
          class="icon-action danger"
          title="Xóa"
          :disabled="isDeleting"
          @click="$emit('delete', comment)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18" /><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" /><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="editing" class="edit-compose">
      <textarea
        v-model="editDraft"
        maxlength="1000"
        rows="3"
        placeholder="Chỉnh sửa bình luận..."
        :disabled="isSaving"
      ></textarea>
      <div class="edit-actions">
        <span class="char-count">{{ editDraft.length }}/1000</span>
        <div class="edit-btns">
          <button class="btn btn-outline btn-sm" :disabled="isSaving" @click="cancelEdit">Huỷ</button>
          <button
            class="btn btn-primary btn-sm"
            :disabled="!editDraft.trim() || isSaving"
            @click="saveEdit"
          >
            {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </div>

    <p v-else class="comment-body">{{ comment.content }}</p>

    <div v-if="!editing" class="comment-footer">
      <button
        :class="['action-btn', 'like', { liked: comment.liked_by_me }]"
        :disabled="isDeleting"
        @click="$emit('like', comment)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="comment.liked_by_me ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
          <path d="M7 10v12" /><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" />
        </svg>
        <span>{{ comment.like_count || 0 }}</span>
      </button>
      <button
        v-if="!isReply"
        class="action-btn reply"
        :disabled="isDeleting"
        @click="$emit('reply', comment)"
      >
        Phản hồi
      </button>
    </div>

    <div v-if="comment.replies?.length" class="replies">
      <CommentItem
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :is-reply="true"
        :deleting-comment-id="deletingCommentId"
        @like="$emit('like', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { formatRelativeTime } from '@/utils/helpers'
import VipBadge from '@/components/VipBadge.vue'
import CommentItem from '@/components/CommentItem.vue'

const props = defineProps({
  comment: { type: Object, required: true },
  isReply: { type: Boolean, default: false },
  deletingCommentId: { type: String, default: null },
})

defineEmits(['like', 'reply', 'delete', 'pin'])

const updateComment = inject('updateComment', null)

const auth = useAuthStore()
const editing = ref(false)
const editDraft = ref('')
const isSaving = ref(false)

const isDeleting = computed(() => props.deletingCommentId === props.comment.id)

const canEdit = computed(() =>
  auth.isAuthenticated && auth.user?.id === props.comment.user?.id
)

const canDelete = computed(() =>
  auth.isAuthenticated && (canEdit.value || auth.isAdmin)
)

const startEdit = () => {
  editDraft.value = props.comment.content
  editing.value = true
}

const cancelEdit = () => {
  if (isSaving.value) return
  editing.value = false
  editDraft.value = ''
}

const saveEdit = async () => {
  const content = editDraft.value.trim()
  if (!content || isSaving.value || !updateComment) return

  isSaving.value = true
  try {
    const ok = await updateComment(props.comment, content)
    if (ok) {
      editing.value = false
      editDraft.value = ''
    }
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
.comment-item {
  position: relative;
  padding: 16px;
}

.comment-item.busy {
  pointer-events: none;
}

.comment-item.nested {
  padding: 12px 0 0 12px;
  margin-left: 12px;
  border-left: 2px solid var(--border, #27272a);
}

.comment-busy-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(9, 9, 11, 0.45);
  border-radius: inherit;
}

.comment-spinner {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.15);
  border-top-color: var(--amber, #f59e0b);
  animation: comment-spin 0.7s linear infinite;
}

@keyframes comment-spin {
  to { transform: rotate(360deg); }
}

.pinned-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #f59e0b;
  margin-bottom: 10px;
}

.comment-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 10px;
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

.comment-meta {
  flex: 1;
  min-width: 0;
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
  color: var(--amber, #f59e0b);
}

.time {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 4px;
}

.edited-tag {
  color: var(--text-faint, #71717a);
}

.comment-actions-top {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.icon-action {
  border: none;
  background: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
}

.icon-action:hover:not(:disabled) {
  background: var(--bg-muted);
  color: var(--text);
}

.icon-action.danger:hover:not(:disabled) {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.08);
}

.icon-action:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.edit-compose textarea {
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
  margin-bottom: 8px;
}

.edit-compose textarea:focus {
  border-color: rgba(168, 85, 247, 0.5);
}

.edit-compose textarea:disabled {
  opacity: 0.7;
}

.edit-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.edit-btns {
  display: flex;
  gap: 8px;
}

.char-count {
  font-size: 12px;
  color: var(--text-faint, #71717a);
}

.comment-body {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text);
  margin: 0 0 12px;
  white-space: pre-wrap;
  word-break: break-word;
}

.comment-footer {
  display: flex;
  align-items: center;
  gap: 16px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: none;
  color: var(--text-muted);
  font-size: 13px;
  cursor: pointer;
  padding: 4px 0;
  font-family: inherit;
}

.action-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.action-btn.like.liked {
  color: #f59e0b;
}

.action-btn.reply:hover:not(:disabled),
.action-btn.like:hover:not(:disabled) {
  color: var(--text);
}

.replies {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 12px;
  height: 32px;
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
}
</style>

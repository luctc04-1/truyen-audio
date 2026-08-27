<template>
  <div :id="'comment-' + comment.id" :class="['community-comment', { nested: isReply }]">
    <div class="comment-header">
      <UserAvatar :user="comment.user" :size="isReply ? 'sm' : 'md'" />
      <div class="comment-meta">
        <UserNameRow :user="comment.user" />
        <div class="time">{{ formatRelativeTime(comment.created_at) }}</div>
      </div>
    </div>

    <!-- Edit mode -->
    <div v-if="editing" class="edit-box">
      <textarea v-model="editContent" maxlength="1000" :disabled="editSaving" rows="3"></textarea>
      <div class="edit-actions">
        <button type="button" class="btn btn-outline btn-sm" :disabled="editSaving" @click="cancelEdit">Huỷ</button>
        <button
          type="button"
          class="btn btn-primary btn-sm"
          :disabled="!editContent.trim() || editSaving"
          @click="saveEdit"
        >
          <ButtonSpinner v-if="editSaving" variant="light" :size="12" />
          <span v-else>Lưu</span>
        </button>
      </div>
    </div>

    <p v-else class="comment-body" v-html="parsedContent"></p>

    <div v-if="!editing" class="comment-footer">
      <button
        type="button"
        :class="['action-btn', 'like', { liked: comment.liked_by_me, popping: likePop }]"
        @click="onLikeClick"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="comment.liked_by_me ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
          <path d="M7 10v12" /><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" />
        </svg>
        <span>{{ comment.like_count || 0 }}</span>
      </button>
      <button
        v-if="canReply"
        type="button"
        :class="['action-btn', 'reply', { active: isReplying }]"
        @click="$emit('reply', comment)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 14 4 9l5-5" /><path d="M4 9h10a4 4 0 0 1 4 4v7" />
        </svg>
        Trả lời
      </button>
      <template v-if="isOwner">
        <button type="button" class="action-btn" @click="showDeleteConfirm">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
          Xóa
        </button>
      </template>
    </div>

    <div v-if="comment.replies?.length" class="replies">
      <CommunityCommentItem
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :is-reply="true"
        :replying-to="replyingTo"
        :depth="depth + 1"
        @like="$emit('like', $event)"
        @reply="$emit('reply', $event)"
        @comment-updated="$emit('comment-updated', $event)"
        @comment-deleted="$emit('comment-deleted', $event)"
      />
    </div>
  </div>

  <ConfirmDialog
    v-model="confirmingDelete"
    :auto-close="false"
    :confirm-loading="deleting"
    variant="danger"
    title="Xóa bình luận"
    message="Bạn có chắc muốn xóa bình luận này? Hành động này không thể hoàn tác."
    confirm-text="Xóa"
    cancel-text="Huỷ"
    @confirm="executeDelete"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatRelativeTime } from '@/utils/helpers'
import { useLikePop } from '@/composables/useLikePop'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import CommunityService from '@/services/CommunityService'
import UserAvatar from '@/components/UserAvatar.vue'
import UserNameRow from '@/components/UserNameRow.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import CommunityCommentItem from '@/components/CommunityCommentItem.vue'

const props = defineProps({
  comment: { type: Object, required: true },
  isReply: { type: Boolean, default: false },
  replyingTo: { type: String, default: null },
  depth: { type: Number, default: 1 },
})

const emit = defineEmits([
  'like',
  'reply',
  'comment-updated',
  'comment-deleted',
])

const auth = useAuthStore()
const toast = useToastStore()
const { likePop, triggerLikePop } = useLikePop()

const isOwner = computed(() => auth.isAuthenticated && auth.user?.id === props.comment.user?.id)
const isReplying = computed(() => props.replyingTo === props.comment.id)
const canReply = computed(() => props.depth < 4)

const parsedContent = computed(() => {
  if (!props.comment.content) return ''
  // Content đã có HTML span từ reply mới (stored as HTML) → dùng trực tiếp
  if (props.comment.content.includes('<span class="mention">')) return props.comment.content
  // Content cũ dạng plain text: convert @word → span
  return props.comment.content.replace(/@(\w+)/g, '<span class="mention">@$1</span>')
})

const onLikeClick = () => {
  triggerLikePop(() => emit('like', props.comment))
}

// ── Menu
const menuOpen = ref(false)
const closeMenu = () => { menuOpen.value = false }

// ── Edit
const editing = ref(false)
const editContent = ref('')
const editSaving = ref(false)

const stripHtmlToPlain = (html) =>
  html
    .replace(/<span class="mention">(@[^<]+)<\/span>/g, '$1')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')

const cancelEdit = () => {
  editing.value = false
  editContent.value = ''
}

const saveEdit = async () => {
  if (!editContent.value.trim() || editSaving.value) return
  editSaving.value = true
  try {
    const updated = await CommunityService.updateComment(props.comment.id, editContent.value.trim())
    emit('comment-updated', { id: props.comment.id, content: updated.content ?? editContent.value.trim() })
    editing.value = false
    toast.success('Đã chỉnh sửa bình luận')
  } catch (error) {
    toast.error(error.message || 'Chỉnh sửa thất bại')
  } finally {
    editSaving.value = false
  }
}

// ── Delete
const confirmingDelete = ref(false)
const deleting = ref(false)

const showDeleteConfirm = () => {
  menuOpen.value = false
  confirmingDelete.value = true
}

const executeDelete = async () => {
  if (deleting.value) return
  deleting.value = true
  try {
    await CommunityService.deleteComment(props.comment.id)
    confirmingDelete.value = false
    emit('comment-deleted', props.comment)
    toast.success('Đã xóa bình luận')
  } catch (error) {
    toast.error(error.message || 'Xóa thất bại')
  } finally {
    deleting.value = false
  }
}

// Directive đóng menu khi click ra ngoài
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutsideHandler = (e) => { if (!el.contains(e.target)) binding.value() }
    document.addEventListener('mousedown', el._clickOutsideHandler)
  },
  unmounted(el) {
    document.removeEventListener('mousedown', el._clickOutsideHandler)
  },
}
</script>

<style scoped>
.community-comment { padding: 12px 0; }

.community-comment.nested {
  padding: 10px 0 0 12px;
  margin-left: 12px;
  border-left: 2px solid var(--border);
}

.comment-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.comment-meta { flex: 1; min-width: 0; }
.time { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

.comment-body {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text);
  margin: 0 0 10px;
  white-space: pre-wrap;
  word-break: break-word;
}

/* ── Comment menu ── */
.comment-menu { position: relative; flex-shrink: 0; }

.menu-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: var(--radius-full);
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.menu-trigger:hover,
.menu-trigger.active { background: var(--bg-muted); color: var(--text); }

.menu-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  z-index: 20;
  min-width: 148px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 9px 12px;
  border: none;
  background: transparent;
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
  text-align: left;
}

.menu-item:hover { background: var(--bg-muted); }
.menu-item.danger { color: var(--red, #ef4444); }
.menu-item.danger:hover { background: rgba(239,68,68,0.08); }

.menu-pop-enter-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.menu-pop-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.menu-pop-enter-from, .menu-pop-leave-to { opacity: 0; transform: translateY(-6px) scale(0.96); }

/* ── Inline edit ── */
.edit-box { margin-bottom: 10px; }

.edit-box textarea {
  width: 100%;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--primary-border);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 14px;
  font-family: inherit;
  line-height: 1.5;
  resize: vertical;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.edit-box textarea:focus { outline: none; }

.edit-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 8px; }

/* ── Comment footer ── */
.comment-footer { display: flex; align-items: center; gap: 16px; }

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
  transition: color 0.15s ease;
}

.action-btn svg { transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.action-btn.like.popping svg { transform: scale(1.25); }
.action-btn.like.liked { color: var(--red); }
.action-btn.reply.active { color: var(--amber); font-weight: 600; }
.action-btn.reply:hover, .action-btn.like:hover { color: var(--text); }
.action-btn.danger { color: var(--red); }
.action-btn.danger:hover { color: var(--red); opacity: 0.8; }

/* ── Replies ── */
.replies { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }

/* ── Mention display ── */
.comment-body :deep(.mention) {
  color: var(--primary);
  font-weight: 600;
  padding: 1px 6px;
}

/* ── Buttons ── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border-radius: var(--radius-sm);
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.15s, background 0.15s;
}

.btn-primary { background: var(--gradient-premium); color: #fff; border: none; height: 34px; padding: 0 14px; font-size: 13px; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); height: 34px; padding: 0 14px; font-size: 13px; }
.btn-sm { height: 30px; padding: 0 12px; font-size: 12px; }

@keyframes targetHighlight {
  0% {
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.85), 0 0 20px rgba(168, 85, 247, 0.5);
    border-color: var(--primary);
    background-color: rgba(168, 85, 247, 0.18);
  }
  70% {
    box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.5), 0 0 12px rgba(168, 85, 247, 0.25);
    border-color: var(--primary);
    background-color: rgba(168, 85, 247, 0.08);
  }
  100% {
    box-shadow: none;
    background-color: transparent;
  }
}

:deep(.highlight-target-comment),
.highlight-target-comment {
  animation: targetHighlight 3.5s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
  border-radius: var(--radius-sm) !important;
}
</style>

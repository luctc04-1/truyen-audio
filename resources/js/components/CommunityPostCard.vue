<template>
  <div class="post-card">
    <div class="post-header">
      <UserAvatar :user="post.user" />
      <div class="post-author-meta">
        <UserNameRow :user="post.user" :tag-label="post.tag_label" />
        <div class="post-time">{{ formatRelativeTime(post.created_at) }}</div>
      </div>

      <div v-if="isOwner" class="post-menu" v-click-outside="closeMenu">
        <button type="button" class="menu-trigger" :class="{ active: menuOpen }" @click.stop="menuOpen = !menuOpen">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
        </button>
        <Transition name="menu-pop">
          <div v-if="menuOpen" class="menu-dropdown">
            <button type="button" class="menu-item" @click="startEdit">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>
              Chỉnh sửa
            </button>
            <button type="button" class="menu-item danger" @click="showDeleteConfirm">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
              Xóa bài viết
            </button>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Edit mode -->
    <div v-if="editing" class="edit-box">
      <textarea v-model="editContent" maxlength="5000" :disabled="editSaving" rows="4"></textarea>
      <div class="edit-actions">
        <button type="button" class="btn btn-outline btn-sm" :disabled="editSaving" @click="cancelEdit">Huỷ</button>
        <button
          type="button"
          class="btn btn-primary btn-sm"
          :disabled="!editContent.trim() || editSaving"
          @click="saveEdit"
        >
          <ButtonSpinner v-if="editSaving" variant="light" :size="13" />
          <span v-else>Lưu</span>
        </button>
      </div>
    </div>

    <template v-else>
      <p class="post-body">{{ post.content }}</p>
      <router-link v-if="post.series" :to="`/story/${post.series.id}`" class="series-attach">
        <img :src="post.series.cover_url || post.series.image || SERIES_FALLBACK_COVER" :alt="post.series.title" class="series-cover" />
        <div class="series-info">
          <span class="series-label">Truyện đính kèm</span>
          <span class="series-title">{{ post.series.title }}</span>
        </div>
      </router-link>
    </template>

    <div class="post-actions">
      <button
        type="button"
        :class="['post-action', 'like', { liked: post.liked_by_me, popping: likePop }]"
        @click="onLikeClick"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="post.liked_by_me ? 'var(--red)' : 'none'" :stroke="post.liked_by_me ? 'var(--red)' : 'currentColor'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        <span>{{ post.like_count }}</span>
      </button>
      <button type="button" class="post-action" @click="$emit('toggle-comments', post)">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
        {{ post.comment_count }}
      </button>
    </div>

    <Transition name="comments-expand">
      <div v-if="expanded" class="post-comments">
        <div v-if="commentsLoading" class="comments-loading">
          <CommunityCommentSkeleton v-for="n in 2" :key="n" />
        </div>

        <template v-else>
          <div v-if="comments.length" class="comment-list">
            <div v-for="comment in comments" :key="comment.id" class="comment-thread">
              <CommunityCommentItem
                :comment="comment"
                :replying-to="replyingTo"
                @like="$emit('comment-like', $event)"
                @reply="$emit('reply', $event)"
                @comment-updated="$emit('comment-updated', $event)"
                @comment-deleted="$emit('comment-deleted', $event)"
              />
            </div>
          </div>
          <div v-else class="comments-empty">Chưa có bình luận</div>
        </template>

        <div v-if="auth.isAuthenticated" class="comment-compose-bottom">
          <Transition name="reply-bar">
            <div v-if="replyingToComment" class="reply-indicator-bar">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 14 4 9l5-5"/><path d="M4 9h10a4 4 0 0 1 4 4v7"/>
              </svg>
              <span>Đang trả lời bình luận</span>
              <button type="button" class="reply-indicator-close" @click="$emit('cancel-reply')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
              </button>
            </div>
          </Transition>
          <div class="compose-row" :class="{ 'is-replying': replyingToComment }">
            <textarea
              ref="commentTextareaEl"
              :value="commentDraft"
              :placeholder="replyingToComment ? 'Nhập phản hồi...' : 'Viết bình luận...'"
              maxlength="1000"
              rows="1"
              :disabled="commentSubmitting"
              @input="$emit('update:commentDraft', $event.target.value)"
            ></textarea>
            <button
              type="button"
              class="send-btn"
              :disabled="!commentDraft?.trim() || commentSubmitting"
              title="Gửi"
              @click="$emit('submit-comment', post)"
            >
              <ButtonSpinner v-if="commentSubmitting" variant="light" :size="16" />
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>
              </svg>
            </button>
          </div>
        </div>
        <p v-else class="comment-login-hint">
          <button type="button" class="link-btn" @click="$emit('go-auth')">Đăng nhập</button> để bình luận
        </p>
      </div>
    </Transition>
  </div>

  <ConfirmDialog
    v-model="confirmingDelete"
    :auto-close="false"
    :confirm-loading="deleting"
    variant="danger"
    title="Xóa bài viết"
    message="Bạn có chắc muốn xóa bài viết này? Hành động này không thể hoàn tác."
    confirm-text="Xóa"
    cancel-text="Huỷ"
    @confirm="executeDelete"
  />
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import { formatRelativeTime, SERIES_FALLBACK_COVER } from '@/utils/helpers'
import { useLikePop } from '@/composables/useLikePop'
import CommunityService from '@/services/CommunityService'
import UserAvatar from '@/components/UserAvatar.vue'
import UserNameRow from '@/components/UserNameRow.vue'
import CommunityCommentItem from '@/components/CommunityCommentItem.vue'
import CommunityCommentSkeleton from '@/components/CommunityCommentSkeleton.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const props = defineProps({
  post: { type: Object, required: true },
  expanded: { type: Boolean, default: false },
  comments: { type: Array, default: () => [] },
  commentsLoading: { type: Boolean, default: false },
  commentDraft: { type: String, default: '' },
  replyingTo: { type: String, default: null },
  replyingToComment: { type: Object, default: null },
  commentSubmitting: { type: Boolean, default: false },
})

const emit = defineEmits([
  'like',
  'toggle-comments',
  'comment-like',
  'reply',
  'cancel-reply',
  'submit-comment',
  'update:commentDraft',
  'go-auth',
  'post-updated',
  'post-deleted',
  'comment-updated',
  'comment-deleted',
])

const auth = useAuthStore()
const toast = useToastStore()
const { likePop, triggerLikePop } = useLikePop()

const isOwner = computed(() => auth.isAuthenticated && auth.user?.id === props.post.user?.id)

// ── Auto-focus textarea khi chuyển sang reply mode
const commentTextareaEl = ref(null)

watch(() => props.replyingToComment, async (val) => {
  if (!val) return
  await nextTick()
  commentTextareaEl.value?.focus()
})

// ── Menu
const menuOpen = ref(false)
const closeMenu = () => { menuOpen.value = false }

// ── Edit
const editing = ref(false)
const editContent = ref('')
const editSaving = ref(false)

const startEdit = () => {
  editContent.value = props.post.content
  editing.value = true
  menuOpen.value = false
}

const cancelEdit = () => {
  editing.value = false
  editContent.value = ''
}

const saveEdit = async () => {
  if (!editContent.value.trim() || editSaving.value) return
  editSaving.value = true
  try {
    const updated = await CommunityService.updatePost(props.post.id, {
      content: editContent.value.trim(),
      tag: props.post.tag,
      series_id: props.post.series?.id ?? null,
    })
    emit('post-updated', updated)
    editing.value = false
    toast.success('Đã chỉnh sửa bài viết')
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
    await CommunityService.deletePost(props.post.id)
    confirmingDelete.value = false
    emit('post-deleted', props.post.id)
    toast.success('Đã xóa bài viết')
  } catch (error) {
    toast.error(error.message || 'Xóa thất bại')
  } finally {
    deleting.value = false
  }
}

const onLikeClick = () => {
  triggerLikePop(() => emit('like', props.post))
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
.post-card {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  transition: background 0.2s;
}

.post-card:last-child { border-bottom: none; }
.post-card:hover { background: rgba(255,255,255,0.02); }

.post-header {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 12px;
}

.post-author-meta { flex: 1; min-width: 0; }
.post-time { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

.post-body {
  font-size: 15px;
  line-height: 1.6;
  color: var(--text);
  margin: 0 0 12px;
  white-space: pre-wrap;
  word-break: break-word;
}

/* ── Menu ── */
.post-menu { position: relative; flex-shrink: 0; }

.menu-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
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
  min-width: 160px;
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
  padding: 10px 14px;
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
.edit-box { margin-bottom: 12px; }

.edit-box textarea {
  width: 100%;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--primary-border);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 15px;
  font-family: inherit;
  line-height: 1.5;
  resize: vertical;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.edit-box textarea:focus { outline: none; }

.edit-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 8px;
}

/* ── Series attach ── */
.series-attach {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  margin-bottom: 12px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg-muted);
  text-decoration: none;
  transition: border-color 0.2s;
}

.series-attach:hover { border-color: var(--primary); }
.series-cover { width: 44px; height: 60px; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
.series-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.series-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
.series-title { font-size: 14px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Post actions ── */
.post-actions {
  display: flex;
  gap: 16px;
  border-top: 1px solid var(--border);
  padding-top: 12px;
}

.post-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  transition: color 0.2s;
  background: none;
  border: none;
  font-family: inherit;
}

.post-action svg { width: 18px; height: 18px; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.post-action.like.popping svg { transform: scale(1.28); }
.post-action:hover { color: var(--text); }
.post-action.like:hover { color: var(--red); }
.post-action.like.liked { color: var(--red); }

/* ── Comments ── */
.post-comments {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
  transform-origin: top center;
}

.comments-expand-enter-active { transition: opacity 0.22s ease, transform 0.22s ease; }
.comments-expand-leave-active { transition: opacity 0.16s ease, transform 0.16s ease; }
.comments-expand-enter-from, .comments-expand-leave-to { opacity: 0; transform: translateY(-8px); }

.comment-list { margin-bottom: 12px; }
.comment-thread + .comment-thread { border-top: 1px solid var(--border); }

.comment-compose-bottom {
  padding-top: 8px;
  border-top: 1px solid var(--border);
}

/* ── Reply indicator bar ── */
.reply-indicator-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  margin-bottom: 8px;
  color: var(--amber, #f59e0b);
  font-size: 13px;
  font-weight: 500;
}

.reply-indicator-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: var(--amber, #f59e0b);
  cursor: pointer;
  flex-shrink: 0;
  opacity: 0.7;
  transition: opacity 0.15s, background 0.15s;
  padding: 0;
}

.reply-indicator-close:hover { opacity: 1; background: rgba(245, 158, 11, 0.12); }

.reply-bar-enter-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.reply-bar-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.reply-bar-enter-from, .reply-bar-leave-to { opacity: 0; transform: translateY(-4px); }

/* ── Compose row ── */
.compose-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
}

.compose-row textarea {
  flex: 1;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 14px;
  resize: none;
  font-family: inherit;
  min-height: 44px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.compose-row textarea:focus { outline: none; border-color: var(--primary-focus); }

.compose-row.is-replying textarea {
  border-color: var(--amber-focus);
  box-shadow: 0 0 0 3px var(--amber-light);
}

.compose-row.is-replying textarea:focus {
  border-color: var(--amber);
  box-shadow: 0 0 0 3px var(--amber-ring);
}

.send-btn {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-md);
  border: none;
  background: var(--gradient-premium);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity 0.2s;
}

.send-btn:hover:not(:disabled) { opacity: 0.9; }
.send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.comments-loading { padding: 4px 0 12px; display: flex; flex-direction: column; gap: 8px; }
.comments-empty { font-size: 13px; color: var(--text-muted); padding: 8px 0 12px; }
.comment-login-hint { font-size: 13px; color: var(--text-muted); margin: 12px 0 0; text-align: center; }

.link-btn {
  background: none;
  border: none;
  color: var(--primary);
  cursor: pointer;
  font-size: inherit;
  padding: 0;
  text-decoration: underline;
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
.btn-outline:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-sm { height: 32px; padding: 0 12px; font-size: 12px; }
</style>

<template>
  <div class="post-card">
    <div class="post-header">
      <UserAvatar :user="post.user" />
      <div class="post-author-meta">
        <UserNameRow :user="post.user" :tag-label="post.tag_label" />
        <div class="post-time">{{ formatRelativeTime(post.created_at) }}</div>
      </div>
    </div>

    <p class="post-body">{{ post.content }}</p>

    <router-link v-if="post.series" :to="`/story/${post.series.id}`" class="series-attach">
      <img
        :src="post.series.cover_url || post.series.image || SERIES_FALLBACK_COVER"
        :alt="post.series.title"
        class="series-cover"
      />
      <div class="series-info">
        <span class="series-label">Truyện đính kèm</span>
        <span class="series-title">{{ post.series.title }}</span>
      </div>
    </router-link>

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
                :is-replying="replyingTo === comment.id"
                @like="$emit('comment-like', $event)"
                @reply="$emit('reply', $event)"
              />

              <Transition name="reply-slide">
                <div v-if="replyingTo === comment.id" class="reply-compose">
                  <textarea
                    ref="replyTextareaEl"
                    :value="replyDraft"
                    placeholder="Nhập phản hồi của bạn..."
                    maxlength="1000"
                    rows="2"
                    :disabled="commentSubmitting"
                    @input="$emit('update:replyDraft', $event.target.value)"
                    @keydown.escape.prevent="$emit('cancel-reply')"
                  ></textarea>
                  <div class="reply-actions">
                    <button type="button" class="btn btn-outline btn-sm" :disabled="commentSubmitting" @click="$emit('cancel-reply')">Huỷ</button>
                    <button
                      type="button"
                      class="btn btn-primary btn-sm"
                      :disabled="!replyDraft?.trim() || commentSubmitting"
                      @click="$emit('submit-reply', comment)"
                    >
                      <ButtonSpinner v-if="commentSubmitting" variant="light" :size="13" />
                      Gửi phản hồi
                    </button>
                  </div>
                </div>
              </Transition>
            </div>
          </div>

          <div v-else class="comments-empty">Chưa có bình luận</div>
        </template>

        <div v-if="auth.isAuthenticated" class="comment-compose-bottom">
          <textarea
            :value="commentDraft"
            placeholder="Viết bình luận..."
            maxlength="1000"
            rows="2"
            :disabled="commentSubmitting"
            @input="$emit('update:commentDraft', $event.target.value)"
          ></textarea>
          <button
            type="button"
            class="send-btn"
            :disabled="!commentDraft?.trim() || commentSubmitting"
            title="Gửi bình luận"
            @click="$emit('submit-comment', post)"
          >
            <ButtonSpinner v-if="commentSubmitting" variant="light" :size="16" />
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>
            </svg>
          </button>
        </div>
        <p v-else class="comment-login-hint">
          <button type="button" class="link-btn" @click="$emit('go-auth')">Đăng nhập</button> để bình luận
        </p>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { formatRelativeTime, SERIES_FALLBACK_COVER } from '@/utils/helpers'
import { useLikePop } from '@/composables/useLikePop'
import UserAvatar from '@/components/UserAvatar.vue'
import UserNameRow from '@/components/UserNameRow.vue'
import CommunityCommentItem from '@/components/CommunityCommentItem.vue'
import CommunityCommentSkeleton from '@/components/CommunityCommentSkeleton.vue'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const props = defineProps({
  post: { type: Object, required: true },
  expanded: { type: Boolean, default: false },
  comments: { type: Array, default: () => [] },
  commentsLoading: { type: Boolean, default: false },
  commentDraft: { type: String, default: '' },
  replyDraft: { type: String, default: '' },
  replyingTo: { type: String, default: null },
  commentSubmitting: { type: Boolean, default: false },
})

const emit = defineEmits([
  'like',
  'toggle-comments',
  'comment-like',
  'reply',
  'cancel-reply',
  'submit-reply',
  'submit-comment',
  'update:commentDraft',
  'update:replyDraft',
  'go-auth',
])

const auth = useAuthStore()
const { likePop, triggerLikePop } = useLikePop()
const replyTextareaEl = ref(null)

const onLikeClick = () => {
  triggerLikePop(() => emit('like', props.post))
}

const focusReplyTextarea = () => {
  const el = replyTextareaEl.value
  const target = Array.isArray(el) ? el.find(Boolean) : el
  target?.focus()
}

watch(() => props.replyingTo, async (id) => {
  if (!id) return
  await nextTick()
  focusReplyTextarea()
})
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

.series-cover {
  width: 44px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

.series-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.series-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
.series-title { font-size: 14px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

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

.post-action svg {
  width: 18px;
  height: 18px;
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.post-action.like.popping svg {
  transform: scale(1.28);
}

.post-action:hover { color: var(--text); }
.post-action.like:hover { color: var(--red); }
.post-action.like.liked { color: var(--red); }

.post-comments {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
  transform-origin: top center;
}

.comments-expand-enter-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.comments-expand-leave-active {
  transition: opacity 0.16s ease, transform 0.16s ease;
}

.comments-expand-enter-from,
.comments-expand-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.reply-slide-enter-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.reply-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.reply-slide-enter-from,
.reply-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.comment-list { margin-bottom: 12px; }
.comment-thread + .comment-thread { border-top: 1px solid var(--border); }

.reply-compose {
  margin: 4px 0 8px 48px;
  overflow: hidden;
}

.reply-compose textarea {
  width: 100%;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg-muted);
  color: var(--text);
  font-size: 14px;
  resize: none;
  font-family: inherit;
  margin-bottom: 8px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.reply-compose textarea:focus {
  outline: none;
  border-color: rgba(168, 85, 247, 0.5);
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.reply-actions { display: flex; gap: 8px; justify-content: flex-end; }

.comment-compose-bottom {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--border);
}

.comment-compose-bottom textarea {
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
}

.comment-compose-bottom textarea:focus {
  outline: none;
  border-color: rgba(168, 85, 247, 0.5);
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

.comments-loading {
  padding: 4px 0 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.comments-empty {
  font-size: 13px;
  color: var(--text-muted);
  padding: 8px 0 12px;
}

.comment-login-hint {
  font-size: 13px;
  color: var(--text-muted);
  margin: 12px 0 0;
  text-align: center;
}

.link-btn {
  background: none;
  border: none;
  color: var(--primary);
  cursor: pointer;
  font-size: inherit;
  padding: 0;
  text-decoration: underline;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border-radius: var(--radius-sm);
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
}

.btn-primary {
  background: var(--gradient-premium);
  color: #fff;
  border: none;
  height: 34px;
  padding: 0 14px;
  font-size: 13px;
}

.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-outline {
  background: transparent;
  border: 1px solid var(--border-strong);
  color: var(--text);
  height: 34px;
  padding: 0 14px;
  font-size: 13px;
}

.btn-outline:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

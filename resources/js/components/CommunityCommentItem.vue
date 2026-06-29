<template>
  <div :class="['community-comment', { nested: isReply }]">
    <div class="comment-header">
      <UserAvatar :user="comment.user" :size="isReply ? 'sm' : 'md'" />
      <div class="comment-meta">
        <UserNameRow :user="comment.user" />
        <div class="time">{{ formatRelativeTime(comment.created_at) }}</div>
      </div>
    </div>

    <p class="comment-body">{{ comment.content }}</p>

    <div class="comment-footer">
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
        v-if="!isReply"
        type="button"
        :class="['action-btn', 'reply', { active: isReplying }]"
        @click="$emit('reply', comment)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 14 4 9l5-5" /><path d="M4 9h10a4 4 0 0 1 4 4v7" />
        </svg>
        Trả lời
      </button>
    </div>

    <div v-if="comment.replies?.length" class="replies">
      <CommunityCommentItem
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :is-reply="true"
        @like="$emit('like', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { formatRelativeTime } from '@/utils/helpers'
import { useLikePop } from '@/composables/useLikePop'
import UserAvatar from '@/components/UserAvatar.vue'
import UserNameRow from '@/components/UserNameRow.vue'
import CommunityCommentItem from '@/components/CommunityCommentItem.vue'

const props = defineProps({
  comment: { type: Object, required: true },
  isReply: { type: Boolean, default: false },
  isReplying: { type: Boolean, default: false },
})

const emit = defineEmits(['like', 'reply'])

const { likePop, triggerLikePop } = useLikePop()

const onLikeClick = () => {
  triggerLikePop(() => emit('like', props.comment))
}
</script>

<style scoped>
.community-comment {
  padding: 12px 0;
}

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

.comment-meta {
  flex: 1;
  min-width: 0;
}

.time {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 4px;
}

.comment-body {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text);
  margin: 0 0 10px;
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
  transition: color 0.15s ease;
}

.action-btn svg {
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.action-btn.like.popping svg {
  transform: scale(1.25);
}

.action-btn.like.liked {
  color: var(--amber, #f59e0b);
}

.action-btn.reply.active {
  color: var(--primary);
  font-weight: 600;
}

.action-btn.reply:hover,
.action-btn.like:hover {
  color: var(--text);
}

.replies {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
</style>

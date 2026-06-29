<template>
  <div class="community-page">
    <div class="container" style="max-width:680px;">

      <div class="page-header">
        <h1 class="page-title">💬 Cộng đồng</h1>
        <p class="page-subtitle">Cùng thảo luận, chia sẻ và giao lưu với mọi người</p>
      </div>

      <!-- Write post -->
      <div v-if="auth.isAuthenticated" :class="['write-box', { 'write-box-picker': showSeriesPicker || selectedSeries }]">
        <div class="write-header">
          <UserAvatar :user="auth.user" size="md" />
          <p class="compose-as">Đăng với tên: <strong>{{ auth.displayName }}</strong></p>
        </div>
        <textarea
          v-model="newPost"
          placeholder="Bạn đang nghĩ gì về truyện hôm nay? ✍️"
          maxlength="5000"
          :disabled="submitting"
        ></textarea>

        <div v-if="showSeriesPicker || selectedSeries" class="series-picker">
          <div class="series-search-wrap">
            <input
              v-model="seriesSearch"
              type="text"
              placeholder="Tìm truyện để gắn..."
              autocomplete="off"
              :disabled="submitting"
            />
            <span v-if="seriesSearching" class="series-search-spinner" aria-hidden="true"></span>
          </div>
          <div v-if="selectedSeries" class="selected-series">
            <img :src="selectedSeries.image" :alt="selectedSeries.title" class="selected-cover" />
            <span class="selected-title">{{ selectedSeries.title }}</span>
            <button type="button" class="clear-series" @click="clearSeries">✕</button>
          </div>
          <template v-else>
            <CommunitySeriesSkeleton v-if="seriesSearching && !displaySeriesResults.length" :count="4" />
            <div v-else-if="displaySeriesResults.length" :class="['series-results', { 'is-loading': seriesSearching }]">
              <button
                v-for="story in displaySeriesResults"
                :key="story.id"
                type="button"
                class="series-option"
                @click="pickSeries(story)"
              >
                <img :src="story.image" :alt="story.title" class="option-cover" loading="lazy" />
                <span class="option-title">{{ story.title }}</span>
              </button>
            </div>
            <div v-else-if="seriesSearchTried && !seriesSearching" class="series-hint">Không tìm thấy truyện phù hợp</div>
          </template>
        </div>

        <div class="write-footer">
          <div class="write-tags">
            <button
              v-for="tag in availableTags"
              :key="tag.id"
              type="button"
              :class="['write-tag', { active: selectedTag === tag.id }]"
              :disabled="submitting"
              @click="selectedTag = tag.id"
            >
              {{ tag.label }}
            </button>
            <button
              type="button"
              :class="['write-tag', 'attach-tag', { active: showSeriesPicker || selectedSeries }]"
              :disabled="submitting"
              title="Gắn truyện"
              @click="toggleSeriesPicker"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
              Gắn truyện
            </button>
          </div>
          <div class="write-actions">
            <button
              class="btn btn-primary btn-sm"
              :disabled="!canSubmit || submitting"
              @click="handlePost"
            >
              <ButtonSpinner v-if="submitting" variant="light" :size="13" />
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
              Đăng
            </button>
          </div>
        </div>
      </div>

      <div v-else class="write-login-hint card">
        <p>Đăng nhập để chia sẻ với cộng đồng.</p>
        <button class="btn btn-outline btn-sm" @click="goAuth">Đăng nhập</button>
      </div>

      <!-- Filter tabs -->
      <div class="post-filter-tabs">
        <button
          v-for="filter in filters"
          :key="filter.id"
          type="button"
          :class="['filter-tab', { active: activeFilter === filter.id }]"
          @click="changeFilter(filter.id)"
        >
          <span v-html="filter.icon"></span>
          {{ filter.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="card posts-loading">
        <CommunityPostSkeleton v-for="n in 4" :key="n" />
      </div>

      <!-- Empty -->
      <div v-else-if="!posts.length" class="empty-hint card">
        Chưa có bài viết nào. Hãy là người đầu tiên chia sẻ!
      </div>

      <!-- Posts -->
      <div v-else class="card" style="overflow:visible;">
        <CommunityPostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          :expanded="expandedPostId === post.id"
          :comments="postComments[post.id] || []"
          :comments-loading="!!commentsLoading[post.id]"
          :comment-draft="commentDrafts[post.id] || ''"
          :reply-draft="replyDraft"
          :replying-to="replyingTo"
          :comment-submitting="!!commentSubmitting[post.id]"
          @like="toggleLike"
          @toggle-comments="toggleComments"
          @comment-like="toggleCommentLike"
          @reply="startReply"
          @cancel-reply="cancelReply"
          @submit-reply="submitReply"
          @submit-comment="submitComment"
          @update:comment-draft="commentDrafts[post.id] = $event"
          @update:reply-draft="replyDraft = $event"
          @go-auth="goAuth"
        />
        <template v-if="loadingMore">
          <CommunityPostSkeleton v-for="n in 2" :key="'more-' + n" />
        </template>
      </div>

      <!-- Load more -->
      <div v-if="hasMore && !loading" style="text-align:center;margin-top:20px;">
        <button class="btn btn-outline" style="width:100%;max-width:280px;" :disabled="loadingMore" @click="loadMore">
          <ButtonSpinner v-if="loadingMore" variant="muted" :size="14" />
          Xem thêm bài viết
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import CommunityService from '@/services/CommunityService'
import StoryService from '@/services/StoryService'
import ButtonSpinner from '@/components/ButtonSpinner.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import CommunityPostCard from '@/components/CommunityPostCard.vue'
import CommunityPostSkeleton from '@/components/CommunityPostSkeleton.vue'
import CommunitySeriesSkeleton from '@/components/CommunitySeriesSkeleton.vue'
import { extractApiPayload, SERIES_FALLBACK_COVER } from '@/utils/helpers'
import { findNestedComment, optimisticToggleLike } from '@/utils/commentHelpers'

const SERIES_SEARCH_DELAY = 400

const normalizeStory = (story) => ({
  ...story,
  image: story?.image || story?.cover_url || SERIES_FALLBACK_COVER,
})

const auth = useAuthStore()
const router = useRouter()
const toast = useToastStore()

const newPost = ref('')
const selectedTag = ref('thaoluan')
const activeFilter = ref('all')
const submitting = ref(false)
const loading = ref(true)
const loadingMore = ref(false)
const posts = ref([])
const page = ref(1)
const lastPage = ref(1)

const showSeriesPicker = ref(false)
const seriesSearch = ref('')
const seriesCatalog = ref([])
const seriesApiResults = ref([])
const selectedSeries = ref(null)
const seriesSearching = ref(false)
const seriesSearchTried = ref(false)

let seriesSearchTimer = null
let seriesSearchAbort = null
let seriesSearchSeq = 0

const mergeStories = (items) => {
  const map = new Map(seriesCatalog.value.map((s) => [s.id, s]))
  for (const raw of items) {
    const story = normalizeStory(raw)
    map.set(story.id, story)
  }
  seriesCatalog.value = Array.from(map.values())
}

const displaySeriesResults = computed(() => {
  const q = seriesSearch.value.trim().toLowerCase()

  if (!q) {
    return seriesCatalog.value.slice(0, 8)
  }

  if (seriesApiResults.value.length) {
    return seriesApiResults.value
  }

  return seriesCatalog.value
    .filter((story) => story.title?.toLowerCase().includes(q))
    .slice(0, 8)
})

const expandedPostId = ref(null)
const postComments = reactive({})
const commentsLoading = reactive({})
const commentDrafts = reactive({})
const commentSubmitting = reactive({})
const replyingTo = ref(null)
const replyDraft = ref('')
const likingPostIds = reactive(new Set())
const likingCommentIds = reactive(new Set())

const TAG_OPTIONS = [
  { id: 'thaoluan', emoji: '🔥', label: 'Thảo luận' },
  { id: 'dexuat', emoji: '🎧', label: 'Đề xuất' },
  { id: 'hoidap', emoji: '❓', label: 'Hỏi đáp' },
  { id: 'baoloi', emoji: '🐞', label: 'Báo lỗi' },
]

const FILTER_ICONS = {
  all: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
  thaoluan: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
  dexuat: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
  hoidap: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>',
  baoloi: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/></svg>',
}

const availableTags = TAG_OPTIONS.map((tag) => ({
  id: tag.id,
  label: `${tag.emoji} ${tag.label}`,
}))

const filters = [
  { id: 'all', label: 'Tất cả', icon: FILTER_ICONS.all },
  ...TAG_OPTIONS.map((tag) => ({
    id: tag.id,
    label: tag.label,
    icon: FILTER_ICONS[tag.id],
  })),
]

const hasMore = computed(() => page.value < lastPage.value)
const canSubmit = computed(() => !!newPost.value.trim())

const goAuth = () => {
  router.push({ name: 'Auth', query: { redirect: '/community' } })
}

const loadPosts = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
    page.value = 1
  }

  try {
    const params = { page: page.value, per_page: 15 }
    if (activeFilter.value !== 'all') params.tag = activeFilter.value

    const data = await CommunityService.getPosts(params)
    const items = data.items ?? []
    posts.value = append ? [...posts.value, ...items] : items
    lastPage.value = data.pagination?.last_page ?? 1
  } catch {
    toast.error('Không tải được bài viết')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  page.value += 1
  await loadPosts(true)
}

const changeFilter = async (filterId) => {
  if (activeFilter.value === filterId) return
  activeFilter.value = filterId
  expandedPostId.value = null
  await loadPosts()
}

const toggleSeriesPicker = async () => {
  showSeriesPicker.value = !showSeriesPicker.value
  if (!showSeriesPicker.value) {
    clearSeries()
    return
  }
  if (!seriesSearch.value.trim()) {
    await loadSeriesSuggestions()
  }
}

const loadSeriesSuggestions = async () => {
  seriesSearching.value = true
  seriesSearchTried.value = true
  try {
    const data = extractApiPayload(await StoryService.getStories({ sort: 'trending', per_page: 20 }))
    const items = (data?.items ?? []).map(normalizeStory)
    mergeStories(items)
    seriesApiResults.value = []
  } catch {
    seriesCatalog.value = []
  } finally {
    seriesSearching.value = false
  }
}

const fetchSeriesSearch = async (query) => {
  const seq = ++seriesSearchSeq

  if (seriesSearchAbort) {
    seriesSearchAbort.abort()
  }

  seriesSearchAbort = new AbortController()
  seriesSearching.value = true
  seriesSearchTried.value = true

  try {
    const response = await fetch(
      `/api/series?${new URLSearchParams({ search: query, per_page: '12' })}`,
      {
        headers: {
          Accept: 'application/json',
          ...(localStorage.getItem('auth_token')
            ? { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
            : {}),
        },
        signal: seriesSearchAbort.signal,
      }
    )

    if (!response.ok) throw new Error('Search failed')

    const payload = extractApiPayload(await response.json())
    if (seq !== seriesSearchSeq) return

    const items = (payload?.items ?? []).map(normalizeStory)
    mergeStories(items)
    seriesApiResults.value = items
  } catch (error) {
    if (error?.name === 'AbortError') return
    if (seq !== seriesSearchSeq) return
    seriesApiResults.value = []
  } finally {
    if (seq === seriesSearchSeq) {
      seriesSearching.value = false
    }
  }
}

const scheduleSeriesSearch = () => {
  clearTimeout(seriesSearchTimer)

  if (selectedSeries.value) return

  const q = seriesSearch.value.trim()

  if (!q) {
    if (seriesSearchAbort) {
      seriesSearchAbort.abort()
      seriesSearchAbort = null
    }
    seriesSearching.value = false
    seriesApiResults.value = []
    seriesSearchTried.value = seriesCatalog.value.length > 0
    return
  }

  seriesSearchTimer = setTimeout(() => {
    fetchSeriesSearch(q)
  }, SERIES_SEARCH_DELAY)
}

watch(seriesSearch, () => {
  if (selectedSeries.value && seriesSearch.value.trim() !== selectedSeries.value.title) {
    selectedSeries.value = null
  }

  if (!showSeriesPicker.value) return

  const q = seriesSearch.value.trim().toLowerCase()
  if (q) {
    const localMatches = seriesCatalog.value.filter((story) =>
      story.title?.toLowerCase().includes(q)
    )
    if (localMatches.length) {
      seriesApiResults.value = localMatches.slice(0, 8)
    }
  }

  scheduleSeriesSearch()
})

const handlePost = async () => {
  if (!canSubmit.value || submitting.value) return
  if (!auth.isAuthenticated) {
    goAuth()
    return
  }

  submitting.value = true
  try {
    const created = await CommunityService.createPost({
      content: newPost.value.trim(),
      tag: selectedTag.value,
      series_id: selectedSeries.value?.id ?? null,
    })
    posts.value = [created, ...posts.value]
    newPost.value = ''
    clearSeries()
    showSeriesPicker.value = false
    toast.success('Đăng bài thành công')
  } catch (error) {
    toast.error(error.message || 'Đăng bài thất bại')
  } finally {
    submitting.value = false
  }
}

const toggleLike = async (post) => {
  if (!auth.isAuthenticated) {
    goAuth()
    return
  }

  await optimisticToggleLike({
    target: post,
    id: post.id,
    pendingSet: likingPostIds,
    apiCall: () => CommunityService.toggleLike(post.id),
    onError: (error) => toast.error(error.message || 'Không thể thích bài viết'),
  })
}

const toggleComments = async (post) => {
  if (expandedPostId.value === post.id) {
    expandedPostId.value = null
    cancelReply()
    return
  }

  expandedPostId.value = post.id
  cancelReply()

  if (!postComments[post.id]) {
    commentsLoading[post.id] = true
    try {
      const data = await CommunityService.getComments(post.id)
      postComments[post.id] = data.items ?? []
    } catch {
      toast.error('Không tải được bình luận')
      postComments[post.id] = []
    } finally {
      commentsLoading[post.id] = false
    }
  }
}

const toggleCommentLike = async (comment) => {
  if (!auth.isAuthenticated) {
    goAuth()
    return
  }

  const postId = expandedPostId.value
  if (!postId) return

  const target = findNestedComment(postComments[postId], comment.id)

  await optimisticToggleLike({
    target,
    id: comment.id,
    pendingSet: likingCommentIds,
    apiCall: () => CommunityService.toggleCommentLike(comment.id),
    onError: (error) => toast.error(error.message || 'Không thể thích bình luận'),
  })
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
  replyingTo.value = null
  replyDraft.value = ''
}

const submitPostComment = async (postId, content, parent = null) => {
  if (!content || commentSubmitting[postId]) return

  commentSubmitting[postId] = true
  try {
    const created = await CommunityService.submitComment(postId, content, parent?.id ?? null)

    if (parent) {
      const target = postComments[postId]?.find((c) => c.id === parent.id)
      if (target) {
        target.replies = [...(target.replies || []), created]
      }
      replyDraft.value = ''
      replyingTo.value = null
    } else {
      if (!postComments[postId]) postComments[postId] = []
      postComments[postId].push(created)
      commentDrafts[postId] = ''
    }

    const post = posts.value.find((p) => p.id === postId)
    if (post) post.comment_count += 1
  } catch (error) {
    toast.error(error.message || (parent ? 'Gửi phản hồi thất bại' : 'Gửi bình luận thất bại'))
  } finally {
    commentSubmitting[postId] = false
  }
}

const submitComment = (post) => {
  submitPostComment(post.id, commentDrafts[post.id]?.trim())
}

const submitReply = (parent) => {
  const postId = expandedPostId.value
  if (!postId) return
  submitPostComment(postId, replyDraft.value.trim(), parent)
}

const pickSeries = (story) => {
  selectedSeries.value = normalizeStory(story)
  seriesSearch.value = story.title
  seriesApiResults.value = []
  seriesSearchTried.value = false
}

const clearSeries = () => {
  selectedSeries.value = null
  seriesSearch.value = ''
  seriesApiResults.value = []
  seriesSearchTried.value = false
  seriesSearching.value = false
  clearTimeout(seriesSearchTimer)
  if (seriesSearchAbort) {
    seriesSearchAbort.abort()
    seriesSearchAbort = null
  }
}

onMounted(() => loadPosts())
</script>

<style scoped>
.community-page { min-height: 100vh; }
.container { max-width: 680px; margin: 0 auto; padding: 32px 16px; }
@media (min-width: 640px) { .container { padding: 32px 24px; } }

.page-header { margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
.page-subtitle { font-size: 14px; color: var(--text-muted); }

.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }

.write-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 16px 0;
}

.compose-as { font-size: 13px; color: var(--text-muted); margin: 0; }
.compose-as strong { color: var(--text); }

.write-login-hint {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 16px; margin-bottom: 32px;
}
.write-login-hint p { font-size: 14px; color: var(--text-muted); margin: 0; }

.write-box { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 32px; }
.write-box-picker { overflow: visible; }
.write-box textarea { width: 100%; min-height: 100px; padding: 16px; background: transparent; border: none; color: var(--text); font-size: 15px; line-height: 1.5; resize: none; font-family: inherit; }
.write-box textarea::placeholder { color: var(--text-faint); }
.write-box textarea:disabled { opacity: 0.6; }
.write-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-top: 1px solid var(--border); background: var(--bg-muted); }
.write-tags { display: flex; align-items: center; gap: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch; flex: 1; min-width: 0; scrollbar-width: none; }
.write-tags::-webkit-scrollbar { display: none; }
.write-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: var(--radius-full); border: 1px solid var(--border); background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 500; white-space: nowrap; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
.write-tag:hover:not(:disabled) { background: var(--bg-card); border-color: var(--primary); color: var(--primary); }
.write-tag.active { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
.write-tag:disabled { opacity: 0.5; cursor: not-allowed; }

.write-actions { display: flex; align-items: center; flex-shrink: 0; }

.series-picker { padding: 0 16px 12px; position: relative; z-index: 5; }
.series-search-wrap { position: relative; }
.series-search-wrap input {
  width: 100%; padding: 10px 40px 10px 12px; border-radius: var(--radius-sm);
  border: 1px solid var(--border); background: var(--bg-muted); color: var(--text); font-size: 14px;
}
.series-search-wrap input:focus {
  outline: none;
  border-color: rgba(168, 85, 247, 0.5);
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.12);
}
.series-search-spinner {
  position: absolute;
  right: 12px;
  top: 50%;
  width: 16px;
  height: 16px;
  margin-top: -8px;
  border-radius: 50%;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  animation: series-spin 0.7s linear infinite;
}
@keyframes series-spin { to { transform: rotate(360deg); } }
.series-hint {
  margin-top: 8px; padding: 10px 12px; font-size: 13px; color: var(--text-muted);
  border: 1px dashed var(--border); border-radius: var(--radius-sm); text-align: center;
}
.selected-series {
  display: flex; align-items: center; gap: 12px;
  margin-top: 8px; padding: 10px 12px; border-radius: var(--radius-sm);
  background: var(--primary-light); border: 1px solid rgba(168, 85, 247, 0.2);
}
.selected-cover { width: 40px; height: 54px; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
.selected-title { flex: 1; min-width: 0; font-size: 14px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.clear-series { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 14px; padding: 4px; }
.clear-series:hover { color: var(--text); }
.series-results {
  margin-top: 8px; border: 1px solid var(--border); border-radius: var(--radius-sm);
  overflow: hidden; max-height: 300px; overflow-y: auto;
  background: var(--bg-card); box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  transition: opacity 0.15s ease;
}
.series-results.is-loading { opacity: 0.72; }
.series-option {
  display: flex; align-items: center; gap: 12px; width: 100%; text-align: left; padding: 10px 12px;
  background: var(--bg-card); border: none; border-bottom: 1px solid var(--border);
  color: var(--text); font-size: 13px; cursor: pointer;
  transition: background 0.15s ease;
}
.option-cover { width: 36px; height: 48px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: var(--bg-muted); }
.option-title { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }
.series-option:last-child { border-bottom: none; }
.series-option:hover { background: var(--bg-muted); }

.post-filter-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 24px; -webkit-overflow-scrolling: touch; border-bottom: 1px solid var(--border); }
.filter-tab { display: flex; align-items: center; gap: 6px; padding: 10px 16px; background: transparent; border: none; color: var(--text-muted); font-size: 14px; font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; }
.filter-tab:hover { color: var(--text); }
.filter-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.filter-tab :deep(svg) { width: 16px; height: 16px; }

.posts-loading { overflow: hidden; }
.empty-hint { padding: 32px 16px; text-align: center; color: var(--text-muted); font-size: 14px; }

.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: var(--radius-sm); font-weight: 500; transition: all 0.2s; cursor: pointer; font-family: inherit; }
.btn-primary { background: var(--gradient-premium); color: #fff; border: none; }
.btn-primary:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-primary:disabled { background: var(--border-strong); opacity: 0.5; cursor: not-allowed; }
.btn-sm { height: 34px; padding: 0 16px; font-size: 13px; }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); height: 40px; padding: 0 20px; font-size: 14px; }
.btn-outline:hover:not(:disabled) { background: var(--bg-muted); }
.btn-outline:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

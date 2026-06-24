<template>
  <div class="community-page">
    <div class="container" style="max-width:680px;">
      
      <div class="page-header">
        <h1 class="page-title">💬 Cộng đồng</h1>
        <p class="page-subtitle">Cùng thảo luận, chia sẻ và giao lưu với mọi người</p>
      </div>

      <!-- App install banner -->
      <div v-if="showInstallBanner" class="install-banner">
        <div class="install-banner-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
        </div>
        <div class="install-banner-text">
          <div class="install-banner-title">Hướng dẫn cài đặt ứng dụng</div>
          <div class="install-banner-sub">Cài Truyện Audio lên điện thoại chỉ với 3 bước</div>
        </div>
        <div class="install-banner-chevron" @click="showInstallBanner = false">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </div>
      </div>

      <!-- Write post -->
      <div class="write-box">
        <textarea v-model="newPost" placeholder="Bạn đang nghĩ gì về truyện hôm nay? ✍️" maxlength="5000"></textarea>
        <div class="write-footer">
          <div class="write-tags">
            <button v-for="tag in availableTags" :key="tag.id" :class="['write-tag', { active: selectedTag === tag.id }]" @click="selectedTag = tag.id">
              {{ tag.label }}
            </button>
          </div>
          <button class="btn btn-primary btn-sm" :disabled="!newPost.trim()" @click="handlePost">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
            Đăng
          </button>
        </div>
      </div>

      <!-- Filter tabs -->
      <div class="post-filter-tabs">
        <button v-for="filter in filters" :key="filter.id" :class="['filter-tab', { active: activeFilter === filter.id }]" @click="activeFilter = filter.id">
          <span v-html="filter.icon"></span>
          {{ filter.label }}
        </button>
      </div>

      <!-- Posts -->
      <div class="card" style="overflow:visible;">
        <div v-for="post in filteredPosts" :key="post.id" class="post-card">
          <div class="post-header">
            <div class="post-author">
              <div class="post-avatar" :style="post.avatarStyle || ''">{{ post.avatar }}</div>
              <div class="post-author-meta">
                <div class="post-author-name-row">
                  <span class="post-author-name">{{ post.author }}</span>
                  <svg v-if="post.verified" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
                  <span class="badge" style="font-size:10px;padding:2px 6px;">{{ post.tag }}</span>
                </div>
                <div class="post-time">{{ post.time }}</div>
              </div>
            </div>
          </div>
          <p class="post-body">{{ post.content }}</p>
          <div class="post-actions">
            <button :class="['post-action', 'like', { liked: post.isLiked }]" @click="toggleLike(post)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="post.isLiked ? 'var(--red)' : 'none'" :stroke="post.isLiked ? 'var(--red)' : 'currentColor'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
              <span>{{ post.likes }}</span>
            </button>
            <button class="post-action">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
              {{ post.comments }}
            </button>
          </div>
        </div>
      </div>

      <!-- Load more -->
      <div style="text-align:center;margin-top:20px;">
        <button class="btn btn-outline" style="width:100%;max-width:280px;">Xem thêm bài viết</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const showInstallBanner = ref(true)
const newPost = ref('')
const selectedTag = ref('thaoluan')
const activeFilter = ref('all')

const availableTags = [
  { id: 'thaoluan', label: '🔥 Thảo luận' },
  { id: 'dexuat', label: '🎧 Đề xuất' },
  { id: 'hoidap', label: '❓ Hỏi đáp' },
  { id: 'baoloi', label: '🐞 Báo lỗi' },
  { id: 'gantruyen', label: '📖 Gắn truyện' },
]

const filters = [
  { id: 'all', label: 'Tất cả', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>' },
  { id: 'thaoluan', label: 'Thảo luận', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>' },
  { id: 'dexuat', label: 'Đề xuất', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>' },
  { id: 'hoidap', label: 'Hỏi đáp', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>' },
  { id: 'baoloi', label: 'Báo lỗi', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/></svg>' },
]

const posts = ref([
  { id: 1, avatar: 'N', author: 'nguyễn thành công', verified: true, tag: '🔥 Thảo luận', tagId: 'thaoluan', time: '1 ngày trước', content: 'sao tôi nạp vip rồi mà chưa sửa thành rứa 😭', likes: 0, comments: 1, isLiked: false },
  { id: 2, avatar: 'T', author: 'Trịnh Khoa', verified: false, tag: '🔥 Thảo luận', tagId: 'thaoluan', time: '3 ngày trước', content: 'Ai có audio bộ "lần này tôi nhường em cho người khác" trên ytb Sumio không ạ', likes: 0, comments: 0, isLiked: false },
  { id: 3, avatar: 'Đ', avatarStyle: 'background:rgba(34,197,94,0.12);color:#22c55e;', author: 'Đỗ Duy Hùng', verified: false, tag: '🎧 Đề xuất', tagId: 'dexuat', time: '6 ngày trước', content: 'admin ơi có nhận viết truyện thuê không ạ 😄', likes: 1, comments: 1, isLiked: false },
  { id: 4, avatar: 'L', avatarStyle: 'background:rgba(236,72,153,0.12);color:#ec4899;', author: 'Lianes', verified: true, tag: '🎧 Đề xuất', tagId: 'dexuat', time: '6 ngày trước', content: 'Ra thêm bộ bách hợp đi ad 🌸', likes: 1, comments: 1, isLiked: false },
  { id: 5, avatar: 'L', avatarStyle: 'background:rgba(236,72,153,0.12);color:#ec4899;', author: 'Lianes', verified: true, tag: '🐞 Báo lỗi', tagId: 'baoloi', time: '6 ngày trước', content: 'Admin ơi tui không gia hạn được, phần hiện mã QR bị lỗi 😢', likes: 0, comments: 0, isLiked: false },
  { id: 6, avatar: 'H', author: 'Hana Lê', verified: false, tag: '❓ Hỏi đáp', tagId: 'hoidap', time: '1 tuần trước', content: 'Cho hỏi các tập VIP có thể nghe offline không ạ?', likes: 3, comments: 2, isLiked: false },
])

const filteredPosts = computed(() => {
  if (activeFilter.value === 'all') return posts.value
  return posts.value.filter(p => p.tagId === activeFilter.value)
})

const toggleLike = (post) => {
  post.isLiked = !post.isLiked
  post.likes += post.isLiked ? 1 : -1
}

const handlePost = () => {
  if (!newPost.value.trim()) return
  const selectedTagObj = availableTags.find(t => t.id === selectedTag.value)
  posts.value.unshift({
    id: Date.now(),
    avatar: 'U',
    avatarStyle: 'background:var(--primary-light);color:var(--primary);',
    author: 'Người dùng',
    verified: false,
    tag: selectedTagObj.label,
    tagId: selectedTagObj.id,
    time: 'Vừa xong',
    content: newPost.value,
    likes: 0,
    comments: 0,
    isLiked: false
  })
  newPost.value = ''
}
</script>

<style scoped>
.community-page { min-height: 100vh; }
.container { max-width: 680px; margin: 0 auto; padding: 32px 16px; }
@media (min-width: 640px) { .container { padding: 32px 24px; } }

.page-header { margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
.page-subtitle { font-size: 14px; color: var(--text-muted); }

/* Install banner */
.install-banner { display: flex; align-items: center; gap: 14px; padding: 16px; border-radius: var(--radius-md); background: linear-gradient(135deg, rgba(168,85,247,0.12), rgba(34,197,94,0.08)); border: 1px solid rgba(168,85,247,0.2); margin-bottom: 24px; cursor: pointer; }
.install-banner-icon { width: 44px; height: 44px; border-radius: 50%; background: var(--gradient-premium); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.install-banner-icon svg { width: 20px; height: 20px; stroke: #fff; }
.install-banner-text { flex: 1; min-width: 0; }
.install-banner-title { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.install-banner-sub { font-size: 12px; color: var(--text-muted); }
.install-banner-chevron { width: 32px; height: 32px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); }
.install-banner-chevron svg { width: 16px; height: 16px; color: var(--text-muted); }

/* Write box */
.write-box { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 32px; }
.write-box textarea { width: 100%; min-height: 100px; padding: 16px; background: transparent; border: none; color: var(--text); font-size: 15px; line-height: 1.5; resize: none; font-family: inherit; }
.write-box textarea::placeholder { color: var(--text-faint); }
.write-footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 16px; border-top: 1px solid var(--border); background: var(--bg-muted); }
.write-tags { display: flex; gap: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
.write-tag { padding: 4px 12px; border-radius: var(--radius-full); border: 1px solid var(--border); background: transparent; color: var(--text-muted); font-size: 12px; font-weight: 500; white-space: nowrap; cursor: pointer; transition: all 0.2s; }
.write-tag:hover { background: var(--bg-card); border-color: var(--primary); color: var(--primary); }
.write-tag.active { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }

/* Filter tabs */
.post-filter-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 24px; -webkit-overflow-scrolling: touch; border-bottom: 1px solid var(--border); }
.filter-tab { display: flex; align-items: center; gap: 6px; padding: 10px 16px; background: transparent; border: none; color: var(--text-muted); font-size: 14px; font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; }
.filter-tab:hover { color: var(--text); }
.filter-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.filter-tab :deep(svg) { width: 16px; height: 16px; }

/* Post card */
.post-card { padding: 16px; border-bottom: 1px solid var(--border); transition: background 0.2s; }
.post-card:last-child { border-bottom: none; }
.post-card:hover { background: rgba(255,255,255,0.02); }
.post-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
.post-author { display: flex; gap: 12px; align-items: center; }
.post-avatar { width: 40px; height: 40px; border-radius: var(--radius-sm); background: var(--border-strong); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; color: var(--text); }
.post-author-name-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 4px; }
.post-author-name { font-size: 15px; font-weight: 600; color: var(--text); }
.post-time { font-size: 12px; color: var(--text-muted); }
.post-body { font-size: 15px; line-height: 1.6; color: var(--text); margin-bottom: 16px; white-space: pre-wrap; word-break: break-word; }
.post-actions { display: flex; gap: 16px; border-top: 1px solid var(--border); padding-top: 12px; }
.post-action { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--text-muted); cursor: pointer; transition: color 0.2s; }
.post-action:hover { color: var(--text); }
.post-action svg { width: 18px; height: 18px; }
.post-action.like:hover { color: var(--red); }
.post-action.like.liked { color: var(--red); }

/* Badges & Buttons */
.badge { display: inline-flex; align-items: center; gap: 4px; border-radius: var(--radius-full); font-weight: 600; background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border); }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: var(--radius-sm); font-weight: 500; transition: all 0.2s; cursor: pointer; font-family: inherit; }
.btn-primary { background: var(--gradient-premium); color: #fff; border: none; }
.btn-primary:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.btn-primary:disabled { background: var(--border-strong); opacity: 0.5; cursor: not-allowed; }
.btn-sm { height: 34px; padding: 0 16px; font-size: 13px; }
.btn-outline { background: transparent; border: 1px solid var(--border-strong); color: var(--text); height: 40px; padding: 0 20px; font-size: 14px; }
.btn-outline:hover { background: var(--bg-muted); }
</style>

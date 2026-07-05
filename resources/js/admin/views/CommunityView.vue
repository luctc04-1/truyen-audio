<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Bài viết cộng đồng</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Cộng đồng</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <ul class="nav nav-tabs nav-tabs-custom mb-3">
      <li class="nav-item">
        <button class="nav-link" :class="{ active: tab === 'posts' }" type="button" @click="tab = 'posts'">Bài viết</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{ active: tab === 'comments' }" type="button" @click="switchComments">Bình luận</button>
      </li>
    </ul>

    <AdminDataTable
      v-if="tab === 'posts'"
      :columns="postColumns"
      :rows="items"
      :loading="loading"
      :error="loadError"
      :pagination="pagination"
      :per-page="perPage"
      empty-text="Không có bài viết"
      empty-icon="ri-group-line"
      @page-change="loadPosts"
      @update:per-page="onPerPageChange"
      @retry="loadPosts()"
    >
      <template #filters>
        <div class="row g-3">
          <div class="col-md-5">
            <input v-model="search" type="search" class="form-control" placeholder="Tìm nội dung, user..." @input="debouncedLoad" />
          </div>
          <div class="col-md-3">
            <select v-model="tag" class="form-select" @change="loadPosts(1)">
              <option value="">Tất cả tag</option>
              <option v-for="(label, key) in tags" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
        </div>
      </template>

      <template #cell-user="{ row }">
        <strong>{{ row.user?.username || 'Ẩn danh' }}</strong>
        <div><span class="badge bg-info-subtle text-info">{{ row.tag_label }}</span></div>
      </template>
      <template #cell-content="{ row }">
        <p class="mb-0 text-truncate" style="max-width: 320px">{{ row.content }}</p>
      </template>
      <template #cell-series="{ row }">{{ row.series?.title || 'Không gắn truyện' }}</template>
      <template #cell-stats="{ row }">
        <small class="text-muted">{{ row.likes_count }} thích · {{ row.comments_count }} BL</small>
      </template>
      <template #cell-actions="{ row }">
        <button class="btn btn-sm btn-soft-danger" type="button" @click="removePost(row)"><i class="ri-delete-bin-line"></i></button>
      </template>
    </AdminDataTable>

    <AdminDataTable
      v-else
      :columns="commentColumns"
      :rows="commentItems"
      :loading="commentsLoading"
      :error="commentsError"
      :pagination="commentsPagination"
      :per-page="commentsPerPage"
      empty-text="Không có bình luận cộng đồng"
      empty-icon="ri-chat-3-line"
      @page-change="loadComments"
      @update:per-page="onCommentsPerPageChange"
      @retry="loadComments()"
    >
      <template #filters>
        <input v-model="commentSearch" type="search" class="form-control" placeholder="Tìm bình luận..." @input="debouncedCommentsLoad" />
      </template>
      <template #cell-user="{ row }"><strong>{{ row.user?.username || 'Ẩn danh' }}</strong></template>
      <template #cell-content="{ row }"><p class="mb-0" style="max-width: 360px">{{ row.content }}</p></template>
      <template #cell-post="{ row }"><small class="text-muted">{{ row.post?.content || '—' }}</small></template>
      <template #cell-actions="{ row }">
        <button class="btn btn-sm btn-soft-danger" type="button" @click="removeComment(row)"><i class="ri-delete-bin-line"></i></button>
      </template>
    </AdminDataTable>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AdminDataTable from '@/admin/components/AdminDataTable.vue'
import AdminService from '@/services/AdminService'
import { debounce, extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const postColumns = [
  { key: 'user', label: 'Người đăng' },
  { key: 'content', label: 'Nội dung' },
  { key: 'series', label: 'Truyện' },
  { key: 'stats', label: 'Tương tác' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const commentColumns = [
  { key: 'user', label: 'User' },
  { key: 'content', label: 'Nội dung' },
  { key: 'post', label: 'Bài viết' },
  { key: 'actions', label: '', thClass: 'text-end', tdClass: 'text-end' },
]

const toast = useToastStore()
const tab = ref('posts')
const loading = ref(true)
const loadError = ref(null)
const items = ref([])
const tags = ref({})
const search = ref('')
const tag = ref('')
const perPage = ref(15)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15, from: 0, to: 0 })

const commentsLoading = ref(false)
const commentsError = ref(null)
const commentItems = ref([])
const commentSearch = ref('')
const commentsPerPage = ref(15)
const commentsPagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15, from: 0, to: 0 })

const loadPosts = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const res = extractApiPayload(await AdminService.getCommunityPosts({
      page, per_page: perPage.value, search: search.value || undefined, tag: tag.value || undefined,
    }))
    items.value = res.items
    pagination.value = res.pagination
    tags.value = res.tags || {}
  } catch (e) {
    loadError.value = e?.message || 'Không tải được bài viết'
    items.value = []
  } finally {
    loading.value = false
  }
}

const loadComments = async (page = 1) => {
  commentsLoading.value = true
  commentsError.value = null
  try {
    const res = extractApiPayload(await AdminService.getCommunityComments({
      page, per_page: commentsPerPage.value, search: commentSearch.value || undefined,
    }))
    commentItems.value = res.items
    commentsPagination.value = res.pagination
  } catch (e) {
    commentsError.value = e?.message || 'Không tải được bình luận'
    commentItems.value = []
  } finally {
    commentsLoading.value = false
  }
}

const debouncedLoad = debounce(() => loadPosts(1), 300)
const debouncedCommentsLoad = debounce(() => loadComments(1), 300)
const onPerPageChange = (value) => { perPage.value = value; loadPosts(1) }
const onCommentsPerPageChange = (value) => { commentsPerPage.value = value; loadComments(1) }

const switchComments = () => {
  tab.value = 'comments'
  if (!commentItems.value.length) loadComments()
}

const removePost = async (post) => {
  if (!confirm('Xóa bài viết này?')) return
  try {
    await AdminService.deleteCommunityPost(post.id)
    items.value = items.value.filter((i) => i.id !== post.id)
    toast.success('Đã xóa bài viết')
  } catch { toast.error('Không thể xóa') }
}

const removeComment = async (comment) => {
  if (!confirm('Xóa bình luận này?')) return
  try {
    await AdminService.deleteCommunityComment(comment.id)
    commentItems.value = commentItems.value.filter((i) => i.id !== comment.id)
    toast.success('Đã xóa bình luận')
  } catch { toast.error('Không thể xóa') }
}

onMounted(() => loadPosts())
</script>

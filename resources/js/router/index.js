import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { setSeoMeta } from '@/utils/seo'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/views/HomePage.vue'),
    meta: { title: 'Trang chủ - Truyện Audio Hay Chọn Lọc Online' },
  },
  {
    path: '/library',
    name: 'Library',
    component: () => import('@/views/LibraryPage.vue'),
    meta: { title: 'Kho truyện - Truyện Audio Hay Chọn Lọc' },
  },
  {
    path: '/story/:id',
    name: 'StoryDetail',
    component: () => import('@/views/StoryDetailPage.vue'),
  },
  {
    path: '/community',
    name: 'Community',
    component: () => import('@/views/CommunityPage.vue'),
    meta: { title: 'Cộng đồng - Truyện Audio Hay' },
  },
  {
    path: '/vip',
    name: 'VIP',
    component: () => import('@/views/VIPPage.vue'),
    meta: { title: 'Nâng cấp VIP - Truyện Audio Hay' },
  },
  {
    path: '/auth',
    name: 'Auth',
    component: () => import('@/views/AuthPage.vue'),
    meta: { title: 'Đăng nhập / Đăng ký - Truyện Audio Hay', hideHeader: true, guestOnly: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/ProfilePage.vue'),
    meta: { title: 'Tài khoản cá nhân - Truyện Audio Hay', requiresAuth: true },
  },
  {
    path: '/history',
    name: 'History',
    component: () => import('@/views/HistoryPage.vue'),
    meta: { title: 'Lịch sử nghe - Truyện Audio Hay', requiresAuth: true },
  },
  {
    path: '/follows',
    name: 'Follows',
    component: () => import('@/views/FollowsPage.vue'),
    meta: { title: 'Truyện đang theo dõi - Truyện Audio Hay', requiresAuth: true },
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('@/views/AdminPage.vue'),
    meta: { title: 'Quản trị hệ thống', layout: 'admin', requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/HomePage.vue'),
    meta: { title: 'Trang chủ - Truyện Audio Hay' },
  },
]

const router = createRouter({
  history: createWebHistory('/'),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.bootstrapped) {
    await auth.bootstrap()
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'Home' }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'Auth', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'Home' }
  }

  return true
})

router.afterEach((to) => {
  if (to.meta.title) {
    setSeoMeta({ title: to.meta.title })
  }
})

export default router


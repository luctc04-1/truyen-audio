import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/views/HomePage.vue'),
  },
  {
    path: '/library',
    name: 'Library',
    component: () => import('@/views/LibraryPage.vue'),
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
  },
  {
    path: '/vip',
    name: 'VIP',
    component: () => import('@/views/VIPPage.vue'),
  },
  {
    path: '/auth',
    name: 'Auth',
    component: () => import('@/views/AuthPage.vue'),
    meta: { hideHeader: true, guestOnly: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/ProfilePage.vue'),
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('@/views/AdminPage.vue'),
    meta: { layout: 'admin', requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/HomePage.vue'),
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

export default router

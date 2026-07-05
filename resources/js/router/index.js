import { createRouter, createWebHistory } from 'vue-router'
import { resolveMiddleware, runMiddleware } from '@/router/guards'

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
    path: '/auth/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/ForgotPasswordPage.vue'),
    meta: { hideHeader: true, guestOnly: true },
  },
  {
    path: '/auth/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/ResetPasswordPage.vue'),
    meta: { hideHeader: true, guestOnly: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/ProfilePage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/history',
    name: 'History',
    component: () => import('@/views/HistoryPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/follows',
    name: 'Follows',
    component: () => import('@/views/FollowsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    component: () => import('@/admin/AdminLayout.vue'),
    meta: { layout: 'admin', requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: () => import('@/admin/views/DashboardView.vue'),
      },
      {
        path: 'series/:id',
        name: 'AdminSeriesDetail',
        component: () => import('@/admin/views/SeriesDetailView.vue'),
      },
      {
        path: 'series',
        name: 'AdminSeries',
        component: () => import('@/admin/views/SeriesView.vue'),
      },
      {
        path: 'episodes',
        name: 'AdminEpisodes',
        component: () => import('@/admin/views/EpisodesView.vue'),
      },
      {
        path: 'categories',
        name: 'AdminCategories',
        component: () => import('@/admin/views/CategoriesView.vue'),
      },
      {
        path: 'users',
        name: 'AdminUsers',
        component: () => import('@/admin/views/UsersView.vue'),
      },
      {
        path: 'orders',
        name: 'AdminOrders',
        component: () => import('@/admin/views/OrdersView.vue'),
      },
      {
        path: 'community',
        name: 'AdminCommunity',
        component: () => import('@/admin/views/CommunityView.vue'),
      },
      {
        path: 'sync',
        name: 'AdminSync',
        component: () => import('@/admin/views/SyncView.vue'),
      },
      {
        path: 'comments',
        name: 'AdminComments',
        component: () => import('@/admin/views/CommentsView.vue'),
      },
      {
        path: 'ratings',
        name: 'AdminRatings',
        component: () => import('@/admin/views/RatingsView.vue'),
      },
      {
        path: 'settings',
        name: 'AdminSettings',
        component: () => import('@/admin/views/SettingsView.vue'),
      },
    ],
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

router.beforeEach(async (to, from) => {
  const chain = resolveMiddleware(to)
  return runMiddleware(to, from, chain)
})

export default router

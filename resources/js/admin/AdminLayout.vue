<template>
  <div
    id="layout-wrapper"
    class="admin-theme"
    data-layout="vertical"
    data-topbar="light"
    data-sidebar="dark"
    data-sidebar-size="lg"
  >
    <AdminTopbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

    <AdminSidebar
      :primary-items="primaryItems"
      :secondary-items="secondaryItems"
      :menu-groups="menuGroups"
      :open="sidebarOpen"
      @close="sidebarOpen = false"
    />

    <div class="main-content">
      <div class="page-content">
        <div class="container-fluid">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminTopbar from '@/admin/components/AdminTopbar.vue'

const sidebarOpen = ref(false)

const ADMIN_HTML_ATTRS = {
  'data-layout': 'vertical',
  'data-topbar': 'light',
  'data-sidebar': 'dark',
  'data-sidebar-size': 'lg',
  'data-sidebar-image': 'none',
  'data-preloader': 'disable',
  'data-layout-style': 'default',
}

let previousHtmlAttrs = {}

onMounted(() => {
  document.body.classList.add('admin-route')
  previousHtmlAttrs = {}
  Object.entries(ADMIN_HTML_ATTRS).forEach(([key, value]) => {
    previousHtmlAttrs[key] = document.documentElement.getAttribute(key)
    document.documentElement.setAttribute(key, value)
  })
  previousHtmlAttrs['data-theme'] = document.documentElement.getAttribute('data-theme')
  document.documentElement.setAttribute('data-bs-theme', 'light')
})

onUnmounted(() => {
  document.body.classList.remove('admin-route')
  document.documentElement.removeAttribute('data-bs-theme')
  Object.entries(previousHtmlAttrs).forEach(([key, value]) => {
    if (value === null) {
      document.documentElement.removeAttribute(key)
    } else {
      document.documentElement.setAttribute(key, value)
    }
  })
})

const primaryItems = [
  { id: 'dashboard', label: 'Tổng quan', icon: 'ri-dashboard-2-line', to: '/admin' },
]

const secondaryItems = [
  { id: 'ratings', label: 'Đánh giá', icon: 'ri-star-line', to: '/admin/ratings' },
  { id: 'sync', label: 'Đồng bộ & Jobs', icon: 'ri-cloud-line', to: '/admin/sync' },
  { id: 'settings', label: 'Cài đặt', icon: 'ri-settings-3-line', to: '/admin/settings' },
]

const menuGroups = [
  {
    id: 'stories',
    label: 'Truyện audio',
    icon: 'ri-book-open-line',
    children: [
      { id: 'series', label: 'Truyện', to: '/admin/series' },
      { id: 'episodes', label: 'Tập truyện', to: '/admin/episodes' },
      { id: 'hot', label: 'Truyện hot', to: '/admin/series?is_hot=1' },
    ],
  },
  {
    id: 'social',
    label: 'Tương tác',
    icon: 'ri-group-line',
    children: [
      { id: 'community', label: 'Cộng đồng', to: '/admin/community' },
      { id: 'comments', label: 'Bình luận', to: '/admin/comments' },
    ],
  },
  {
    id: 'members',
    label: 'Thành viên',
    icon: 'ri-user-3-line',
    children: [
      { id: 'users', label: 'Người dùng', to: '/admin/users' },
      { id: 'orders', label: 'Gói VIP', to: '/admin/orders' },
    ],
  },
]
</script>

<style>
@import '../../css/admin/admin.css';
</style>

<template>
  <header id="page-topbar">
    <div class="layout-width">
      <div class="navbar-header">
        <div class="d-flex">
          <button
            type="button"
            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
            @click="$emit('toggle-sidebar')"
          >
            <span class="hamburger-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </button>

          <form class="app-search d-none d-md-block ms-3" @submit.prevent="submitSearch">
            <div class="position-relative">
              <input v-model="searchQuery" type="text" class="form-control" placeholder="Tìm truyện, user, đơn..." />
              <span class="mdi mdi-magnify search-widget-icon"></span>
            </div>
          </form>
        </div>

        <div class="d-flex align-items-center">
          <router-link to="/" class="btn btn-soft-primary btn-sm me-2">
            <i class="ri-external-link-line me-1"></i>
            Xem site
          </router-link>

          <div class="dropdown ms-sm-3 header-item topbar-user" :class="{ show: userMenuOpen }">
            <button type="button" class="btn" @click="userMenuOpen = !userMenuOpen">
              <span class="d-flex align-items-center">
                <img
                  class="rounded-circle header-profile-user"
                  :src="auth.user?.avatar_url || '/theme/admin/assets/images/users/avatar-1.jpg'"
                  alt="Admin"
                />
                <span class="text-start ms-xl-2">
                  <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth.displayName }}</span>
                  <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Quản trị viên</span>
                </span>
              </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end" :class="{ show: userMenuOpen }">
              <h6 class="dropdown-header">Xin chào {{ auth.displayName }}!</h6>
              <router-link class="dropdown-item" to="/profile" @click="userMenuOpen = false">
                <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                <span class="align-middle">Hồ sơ</span>
              </router-link>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item" type="button" @click="handleLogout">
                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                <span class="align-middle">Đăng xuất</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

defineEmits(['toggle-sidebar'])

const auth = useAuthStore()
const router = useRouter()
const userMenuOpen = ref(false)
const searchQuery = ref('')

const submitSearch = () => {
  const q = searchQuery.value.trim()
  if (!q) return
  router.push({ path: '/admin/series', query: { search: q } })
}

const handleLogout = async () => {
  userMenuOpen.value = false
  await auth.runLogoutFlow(router, '/auth')
}
</script>

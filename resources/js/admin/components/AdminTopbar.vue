<template>
  <header class="admin-topbar">
    <button class="icon-btn menu-btn" type="button" title="Menu" @click="$emit('toggle-sidebar')">
      <i class="ri-menu-2-line"></i>
    </button>

    <div class="topbar-search">
      <i class="ri-search-line"></i>
      <input
        :value="search"
        type="search"
        placeholder="Tìm truyện, tập, user, đơn hàng..."
        @input="$emit('update:search', $event.target.value)"
      />
    </div>

    <div class="topbar-actions">
      <button class="icon-btn" type="button" title="Thông báo">
        <i class="ri-notification-3-line"></i>
        <span></span>
      </button>
      <button class="primary-btn" type="button">
        <i class="ri-add-line"></i>
        Thêm truyện
      </button>
      <div class="admin-user">
        <img src="/theme/admin/assets/images/users/avatar-1.jpg" alt="Admin" />
        <div>
          <strong>Admin</strong>
          <small>Quản trị viên</small>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps({
  search: {
    type: String,
    default: ''
  }
})

defineEmits(['toggle-sidebar', 'update:search'])
</script>

<template>
  <aside class="admin-sidebar" :class="{ open }">
    <div class="brand">
      <span class="brand-mark">TA</span>
      <div>
        <strong>Truyen Audio</strong>
        <small>Admin console</small>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a
        v-for="item in menuItems"
        :key="item.id"
        href="#"
        :class="{ active: activeSection === item.id }"
        @click.prevent="$emit('change-section', item.id)"
      >
        <i :class="item.icon"></i>
        <span>{{ item.label }}</span>
        <b v-if="item.count">{{ item.count }}</b>
      </a>
    </nav>

    <div class="sidebar-card">
      <span class="pulse"></span>
      <div>
        <strong>Crawler đang chạy</strong>
        <p>12 nguồn chờ xử lý</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  activeSection: {
    type: String,
    required: true
  },
  menuItems: {
    type: Array,
    required: true
  },
  open: {
    type: Boolean,
    default: false
  }
})

defineEmits(['change-section'])
</script>

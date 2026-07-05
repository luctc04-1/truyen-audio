<template>
  <div class="auth-page" :class="{ 'auth-page-busy': busy }">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">T</div>
        <h1 class="auth-title">{{ title }}</h1>
        <p class="auth-subtitle">{{ subtitle }}</p>
      </div>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
  busy: {
    type: Boolean,
    default: false,
  },
})
</script>

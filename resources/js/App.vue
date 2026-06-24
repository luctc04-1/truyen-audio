<template>
  <div class="app">
    <AppHeader v-if="!isAdminRoute && !hideHeader" />
    <main :class="['main', { 'main-admin': isAdminRoute, 'main-with-player': showMiniPlayer }]">
      <router-view v-slot="{ Component }">
        <keep-alive :include="['HomePage', 'LibraryPage']">
          <component :is="Component" />
        </keep-alive>
      </router-view>
    </main>
    <AudioPlayer v-if="showMiniPlayer" />
    <NowPlaying />
    <AuthOverlay />
    <AppToast />
    
    <!-- SOCIAL FLOAT -->
    <div v-if="!isAdminRoute" :class="['social-float', { 'social-float-raised': showMiniPlayer, 'social-float-auth': hideHeader }]">
      <a href="#" class="social-btn fb" title="Facebook">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
      </a>
      <a href="#" class="social-btn tt" title="TikTok">
        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
      </a>
      <a href="#" class="social-btn yt" title="YouTube">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
      </a>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import AppHeader from './components/AppHeader.vue'
import AudioPlayer from './components/AudioPlayer.vue'
import NowPlaying from './components/NowPlaying.vue'
import AuthOverlay from './components/AuthOverlay.vue'
import AppToast from './components/AppToast.vue'
import { useAudioStore } from '@/stores/audioStore'

const $route = useRoute()
const audio = useAudioStore()
const isAdminRoute = computed(() => $route.meta.layout === 'admin')
const hideHeader = computed(() => !!$route.meta.hideHeader)
const showMiniPlayer = computed(() => !isAdminRoute.value && !!audio.currentEpisode)
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

:root {
  --bg: #09090b;
  --bg-card: #111113;
  --bg-muted: #18181b;
  --border: #27272a;
  --border-strong: #3f3f46;
  --text: #fafafa;
  --text-muted: #a1a1aa;
  --text-faint: #71717a;
  --primary: #a855f7;
  --primary-hover: #9333ea;
  --primary-light: rgba(168, 85, 247, 0.12);
  --primary-light2: rgba(168, 85, 247, 0.06);
  --success: #22c55e;
  --success-border: #16a34a;
  --amber: #f59e0b;
  --red: #ef4444;
  --gradient-premium: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.5);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.5);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-full: 9999px;
  --nav-height: 56px;
  --player-height: 80px;
  --container: 1200px;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

a {
  color: inherit;
  text-decoration: none;
}

button {
  font-family: inherit;
  cursor: pointer;
  border: none;
  background: none;
  outline: none;
}

img {
  max-width: 100%;
  display: block;
}

ul {
  list-style: none;
}

textarea,
input {
  font-family: inherit;
  outline: none;
}

::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}

::-webkit-scrollbar-track {
  background: var(--bg);
}

::-webkit-scrollbar-thumb {
  background: var(--border-strong);
  border-radius: 99px;
}

/* Horizontal scroll — hide scrollbar, keep swipe/drag */
.genre-scroll,
.story-scroll,
.write-tags,
.post-filter-tabs,
.scroll-x {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.genre-scroll::-webkit-scrollbar,
.story-scroll::-webkit-scrollbar,
.write-tags::-webkit-scrollbar,
.post-filter-tabs::-webkit-scrollbar,
.scroll-x::-webkit-scrollbar {
  display: none;
}

.app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main {
  flex: 1;
  padding-bottom: 24px;
}

.main-with-player {
  padding-bottom: calc(var(--player-height) + 16px);
}

.main-admin {
  padding-bottom: 0;
}

/* ===== Social Float ===== */
.social-float {
  position: fixed;
  bottom: 16px;
  right: 16px;
  z-index: 80;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: bottom 0.25s ease;
}
.social-float-raised {
  bottom: calc(var(--player-height) + 16px);
}
.social-float-auth {
  bottom: 16px;
}
.social-btn {
  width: 40px; height: 40px;
  border-radius: var(--radius-full);
  background: var(--bg-card);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-muted);
  transition: all 0.2s;
  box-shadow: var(--shadow-sm);
}
.social-btn:hover { transform: scale(1.1); }
.social-btn.fb:hover { background: #1877f2; color: #fff; border-color: #1877f2; }
.social-btn.tt:hover { background: #010101; color: #fff; border-color: #010101; }
.social-btn.yt:hover { background: #ff0000; color: #fff; border-color: #ff0000; }
.social-btn svg { width: 18px; height: 18px; }
</style>

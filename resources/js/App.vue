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
    <WebPushPrompt />

    <!-- SOCIAL FLOAT -->
    <Teleport to="body">
      <div v-if="!isAdminRoute" :class="['social-float', { 'social-float-raised': showMiniPlayer, 'social-float-auth': hideHeader }]">
        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-btn fb" title="Facebook" aria-label="Facebook">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        </a>
        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="social-btn tt" title="TikTok" aria-label="TikTok">
          <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
        </a>
        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="social-btn yt" title="YouTube" aria-label="YouTube">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
        </a>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AudioPlayer from '@/components/layout/AudioPlayer.vue'
import NowPlaying from '@/components/layout/NowPlaying.vue'
import AuthOverlay from '@/components/feedback/AuthOverlay.vue'
import AppToast from '@/components/feedback/AppToast.vue'
import WebPushPrompt from '@/components/feedback/WebPushPrompt.vue'
import { useAudioStore } from '@/stores/audioStore'
import { applyThemeToDocument } from '@/utils/theme'

const $route = useRoute()
const audio = useAudioStore()
const isAdminRoute = computed(() => $route.meta.layout === 'admin')
const hideHeader = computed(() => !!$route.meta.hideHeader)
const showMiniPlayer = computed(() => !isAdminRoute.value && !!audio.currentEpisode)

applyThemeToDocument(localStorage.getItem('theme') || 'dark')
</script>

<style>
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
  right: calc(12px + env(safe-area-inset-right, 0px));
  bottom: calc(12px + env(safe-area-inset-bottom, 0px));
  z-index: 120;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: bottom 0.25s ease;
  pointer-events: none;
}

.social-float-raised {
  bottom: calc(var(--player-height) + 12px + env(safe-area-inset-bottom, 0px));
}

.social-float-auth {
  bottom: calc(12px + env(safe-area-inset-bottom, 0px));
}

.social-btn {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background: var(--bg-card);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: transform 0.2s, background 0.2s, color 0.2s, border-color 0.2s;
  box-shadow: var(--shadow-md);
  pointer-events: auto;
  flex-shrink: 0;
}

.social-btn:hover { transform: scale(1.08); }
.social-btn.fb:hover { background: #1877f2; color: #fff; border-color: #1877f2; }
.social-btn.tt:hover { background: #010101; color: #fff; border-color: #010101; }
.social-btn.yt:hover { background: #ff0000; color: #fff; border-color: #ff0000; }
.social-btn svg { width: 18px; height: 18px; }

@media (max-width: 768px) {
  .social-btn {
    width: 38px;
    height: 38px;
  }
}
</style>

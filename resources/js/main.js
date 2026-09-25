import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/authStore'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// 1. Vue Global Error Handler: Bắt runtime errors từ mọi component
app.config.errorHandler = (err, instance, info) => {
  if (import.meta.env.DEV) {
    console.error('[Vue Global Error]:', err, `\nLifecycle/Info: ${info}`, instance)
  }
}

// 2. Performance Tracking: Kích hoạt đo lường hiệu năng component trên DevTools khi DEV
if (import.meta.env.DEV) {
  app.config.performance = true
}

// 3. Khởi tạo an toàn (Async Bootstrap)
// Giữ nguyên logic: chờ song song auth.bootstrap() và router.isReady() trước khi mount
async function bootstrapApp() {
  const auth = useAuthStore()

  try {
    await Promise.all([
      auth.bootstrap().catch((err) => {
        console.warn('[Auth Bootstrap Warn]:', err)
      }),
      router.isReady(),
    ])
  } catch (error) {
    console.error('[App Bootstrap Error]:', error)
  } finally {
    const mountEl = document.querySelector('#app')
    if (mountEl) {
      app.mount(mountEl)
    } else {
      console.error('[App Mount Error]: Root element #app not found in DOM.')
    }
  }
}

bootstrapApp()


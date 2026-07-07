import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/authStore'
import { applyShellEarly, isAdminPath } from '@/utils/shellEarly'
import { applyThemeToDocument } from '@/utils/theme'
import '@/css/auth-fields.css'
import '@/css/auth-page.css'

applyShellEarly()

if (!isAdminPath()) {
  applyThemeToDocument(localStorage.getItem('theme') || 'dark')
}

const app = createApp(App)
app.use(createPinia())
app.use(router)

useAuthStore().bootstrap().then(() => app.mount('#app'))

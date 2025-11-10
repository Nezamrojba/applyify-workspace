import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/tailwind.css'
import { i18n, applyDir } from './i18n'
import { useAuthStore } from '@/stores/auth'
import { setAuthStore } from '@/services/api'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(i18n)
app.mount('#app')

app.config.globalProperties.$setLocale = (l: 'en'|'ar') => {
  ;(i18n.global.locale as any).value = l
  localStorage.setItem('locale', l)
  applyDir(l)
}

const auth = useAuthStore()
setAuthStore(auth)
if (localStorage.getItem('token')) {
  auth.me().catch(() => {
    auth.clearAuth()
  })
}

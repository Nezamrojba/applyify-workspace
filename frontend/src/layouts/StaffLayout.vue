<template>
  <div class="min-h-[70vh] grid grid-cols-1 md:grid-cols-[240px_minmax(0,1fr)] gap-0">
    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-14 left-0 right-0 z-40 bg-white border-b border-black/5 px-4 py-2">
      <button @click="mobileMenuOpen = !mobileMenuOpen" class="flex items-center gap-2 text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
          <path d="M3 12h18M3 6h18M3 18h18"/>
        </svg>
        {{ t('staff.layout.menu') }}
      </button>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div v-if="mobileMenuOpen" class="md:hidden fixed inset-0 z-50 bg-black/50" @click="mobileMenuOpen = false"></div>
    
    <!-- Sidebar (Mobile: Fixed overlay, Desktop: Static) -->
    <aside :class="['md:block border-r border-black/5 bg-white', mobileMenuOpen ? 'fixed top-14 left-0 h-[calc(100vh-56px)] w-64 z-50' : 'hidden md:block']">
      <div class="h-[calc(100vh-56px)] overflow-y-auto">
        <div class="px-4 py-4 border-b border-black/5">
          <div class="text-sm font-medium truncate">{{ auth.user?.name || '—' }}</div>
          <span class="mt-1 inline-flex items-center text-xs px-2 py-0.5 rounded bg-primary/5 text-primary">{{ roleLabel }}</span>
        </div>
        <SideNav :items="items" :hideBrand="true" @navigate="mobileMenuOpen = false" />
      </div>
    </aside>
    
    <!-- Main Content -->
    <div>
      <div class="p-3 sm:p-4 lg:p-6 w-full max-w-7xl mx-auto pt-20 md:pt-4">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import SideNav from '@/components/layout/SideNav.vue'
import { useSidebar } from '@/composables/useSidebar'
import { useAuthStore } from '@/stores/auth'
import { useModuleStatus } from '@/composables/useModuleStatus'
import { useI18n } from 'vue-i18n'

const { items: sidebarItems } = useSidebar('/staff')
const auth = useAuthStore()
const { isModuleEnabled, loadModuleSettings } = useModuleStatus()
const mobileMenuOpen = ref(false)
const { t } = useI18n()

onMounted(() => {
  if (auth.user?.role === 'staff') {
    loadModuleSettings(true).catch(() => {})
  }
})

const items = computed(() => {
  return sidebarItems.value.filter((item: any) => {
    // Remove commission module completely from staff account
    if (item.to === '/staff/commission') return false
    if ((item.to === '/staff/assignments' || item.to?.startsWith('/staff/assignments')) && !isModuleEnabled('applications')) return false
    if ((item.to === '/staff/faqs') && !isModuleEnabled('faqs')) return false
    return true
  })
})

const initials = computed(() => {
  const n = (auth.user?.name || 'NA').trim()
  const parts = n.split(/\s+/)
  const s = (parts[0]?.[0] || '') + (parts[1]?.[0] || '')
  return s.slice(0,2).toUpperCase() || 'NA'
})
function hashColor(str: string){
  let h = 0; for (let i=0;i<str.length;i++) h = (h<<5)-h + str.charCodeAt(i)
  const c = (h >>> 0).toString(16).padStart(6, '0').slice(0,6)
  return `#${c}`
}
const avatarColor = computed(() => hashColor(auth.user?.name || 'NA'))
const roleLabel = computed(() => {
  const r = auth.user?.role || ''
  if (r === 'super_admin') return t('common.superAdmin')
  if (r === 'staff') return t('common.staff')
  if (r === 'student') return t('common.student')
  return r
})

async function logout(){
  try { await auth.logout() } catch {}
  location.href = '/'
}
</script>

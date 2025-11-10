<template>
  <div class="relative" ref="menuRef">
    <button 
      @click.stop="open=!open" 
      class="relative h-9 w-9 grid place-items-center rounded-full border border-black/10 bg-white text-muted hover:bg-black/5" 
      aria-label="Notifications"
    >
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 10-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
    </svg>
      <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-danger text-white text-xs flex items-center justify-center font-medium">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
    <transition name="fade">
      <div v-if="open" class="absolute right-0 mt-2 w-80 rounded-md bg-white border border-black/10 shadow-lg z-[60] max-h-[500px] overflow-hidden flex flex-col">
        <div class="p-3 border-b border-black/5 flex items-center justify-between">
          <div class="font-medium text-sm">Notifications</div>
          <button 
            v-if="unreadCount > 0" 
            @click="markAllAsRead" 
            class="text-xs text-primary hover:underline"
            :disabled="markingAll"
          >
            Mark all as read
          </button>
        </div>
        <div class="overflow-y-auto flex-1">
          <div v-if="loading" class="p-4 text-center text-sm text-muted">Loading...</div>
          <div v-else-if="notifications.length === 0" class="p-4 text-center text-sm text-muted">No notifications</div>
          <div v-else class="divide-y divide-black/5">
            <button
              v-for="notif in notifications"
              :key="notif.id"
              @click="handleNotificationClick(notif)"
              :class="['w-full text-left p-3 hover:bg-black/5 transition-colors', !notif.read_at ? 'bg-primary/5' : '']"
            >
              <div class="text-sm font-medium">{{ notif.data.message }}</div>
              <div class="text-xs text-muted mt-1">{{ formatDate(notif.created_at) }}</div>
  </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useModuleStatus } from '@/composables/useModuleStatus'
import { api } from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const { isModuleEnabled, loadModuleSettings } = useModuleStatus()

const open = ref(false)
const menuRef = ref<HTMLElement | null>(null)
const notifications = ref<any[]>([])
const unreadCount = ref(0)
const loading = ref(false)
const markingAll = ref(false)
let pollInterval: number | null = null
const has404Error = ref(false) // Track if we got a 404 to stop retrying

const notificationsEnabled = computed(() => {
  // If we've hit a 404, treat as disabled to prevent any further attempts
  if (has404Error.value) {
    return false
  }
  // Also check if module settings are loaded and notifications module exists
  return isModuleEnabled('notifications')
})

// Store a flag that persists across component instances
const NOTIFICATIONS_DISABLED_KEY = 'notifications_api_disabled'

async function loadNotifications() {
  // Check localStorage first
  if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
    has404Error.value = true
    notifications.value = []
    loading.value = false
    stopPolling()
    return
  }
  
  if (!auth.token || !auth.user || !notificationsEnabled.value || has404Error.value) {
    notifications.value = []
    loading.value = false
    return
  }
  try {
    loading.value = true
    const response = await api.notifications.unread() as any
    notifications.value = response.data || response || []
    // Don't call loadCount here if we already have a 404 error
    if (!has404Error.value) {
      await loadCount()
    }
  } catch (e: any) {
    // Check for 404 in multiple ways
    const is404 = e?.status === 404 || 
                  e?.response?.status === 404 || 
                  e?.message?.includes('404') ||
                  e?.message?.includes('Not Found') ||
                  (typeof e === 'object' && 'status' in e && e.status === 404)
    
    if (is404) {
      // Persist the 404 state
      has404Error.value = true
      localStorage.setItem(NOTIFICATIONS_DISABLED_KEY, 'true')
      stopPolling()
      notifications.value = []
      unreadCount.value = 0
    } else if (e?.status === 500 || e?.response?.status === 500) {
      console.warn('Notifications not available', e)
      notifications.value = []
    } else {
      console.error('Failed to load notifications', e)
    }
  } finally {
    loading.value = false
  }
}

async function loadCount() {
  // Check localStorage first - if we've previously detected a 404, don't make any requests
  if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
    has404Error.value = true
    unreadCount.value = 0
    stopPolling()
    return
  }
  
  // Always check has404Error first - if it's true, don't make any requests
  if (!auth.token || !auth.user || !notificationsEnabled.value || has404Error.value) {
    unreadCount.value = 0
    return
  }
  try {
    const response = await api.notifications.count() as any
    unreadCount.value = response.count || 0
    has404Error.value = false // Reset error flag on success
    // Remove the disabled flag on success
    localStorage.removeItem(NOTIFICATIONS_DISABLED_KEY)
  } catch (e: any) {
    // Check for 404 in multiple ways - status code, status in error object, or message
    const is404 = e?.status === 404 || 
                  e?.response?.status === 404 || 
                  e?.message?.includes('404') ||
                  e?.message?.includes('Not Found') ||
                  (typeof e === 'object' && 'status' in e && e.status === 404)
    
    if (is404) {
      // Persist the 404 state to localStorage to prevent future requests
      has404Error.value = true
      localStorage.setItem(NOTIFICATIONS_DISABLED_KEY, 'true')
      stopPolling()
      unreadCount.value = 0
      // Don't throw - we've handled it
      return
    } else if (e?.status === 500 || e?.response?.status === 500) {
      // Don't log 500 errors to console to reduce noise
      unreadCount.value = 0
    } else {
      // Only log non-404/500 errors
      console.error('Failed to load notification count', e)
    }
  }
}

async function markAllAsRead() {
  if (markingAll.value || unreadCount.value === 0 || has404Error.value || !notificationsEnabled.value) return
  markingAll.value = true
  try {
    await api.notifications.markAllAsRead()
    await loadNotifications()
  } catch (e: any) {
    if (e?.status === 404) {
      has404Error.value = true
      stopPolling()
    }
    console.error('Failed to mark all as read', e)
  } finally {
    markingAll.value = false
  }
}

async function handleNotificationClick(notif: any) {
  if (!notif.read_at && !has404Error.value && notificationsEnabled.value) {
    try {
      await api.notifications.markAsRead(notif.id)
    } catch (e: any) {
      if (e?.status === 404) {
        has404Error.value = true
        stopPolling()
      }
      console.error('Failed to mark notification as read', e)
    }
  }
  
  if (notif.data?.application_id) {
    if (auth.user?.role === 'staff') {
      router.push(`/staff/assignments/${notif.data.application_id}`)
    } else if (auth.user?.role === 'student') {
      router.push(`/dashboard/applications/${notif.data.application_id}`)
    }
    open.value = false
  }
}

function handleClickOutside(e: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
    open.value = false
  }
}

function formatDate(date: string): string {
  const d = new Date(date)
  const now = new Date()
  const diffMs = now.getTime() - d.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return d.toLocaleDateString()
}

function startPolling() {
  // Check localStorage first
  if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
    has404Error.value = true
    return
  }
  
  if (pollInterval || !auth.token || !notificationsEnabled.value || has404Error.value) {
    return
  }
  
  // Initial load - these will set has404Error if they fail
  loadCount().catch(() => {
    // Error already handled in loadCount
  })
  loadNotifications().catch(() => {
    // Error already handled in loadNotifications
  })
  
  pollInterval = window.setInterval(() => {
    // Check localStorage on every interval
    if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
      has404Error.value = true
      stopPolling()
      return
    }
    
    // Check all conditions before making any requests
    if (!auth.token || !notificationsEnabled.value || has404Error.value) {
      stopPolling()
      return
    }
    
    // Only make requests if we haven't encountered a 404
    if (!has404Error.value && localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) !== 'true') {
      loadCount().catch(() => {
        // Error already handled in loadCount
      })
      if (open.value) {
        loadNotifications().catch(() => {
          // Error already handled in loadNotifications
        })
      }
    }
  }, 30000) // Increased from 10s to 30s to reduce server load
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

function refreshNotifications() {
  if (!has404Error.value && notificationsEnabled.value) {
    loadCount()
    if (open.value) {
      loadNotifications()
    }
  }
}

window.addEventListener('notification-refresh', refreshNotifications)

watch(() => open.value, (newVal) => {
  if (newVal && !has404Error.value && notificationsEnabled.value) {
    loadNotifications()
  }
})

watch(() => auth.token, async (newToken) => {
  if (!newToken) {
    stopPolling()
    notifications.value = []
    unreadCount.value = 0
    has404Error.value = false
    // Clear the localStorage flag when user logs out
    localStorage.removeItem(NOTIFICATIONS_DISABLED_KEY)
    return
  }
  
  // Check localStorage first - if we've previously detected a 404, don't start polling
  if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
    has404Error.value = true
    return
  }
  
  // Load module settings first before starting polling
  await loadModuleSettings(true)
  
  // Only start polling if module is enabled and we haven't encountered a 404
  if (notificationsEnabled.value && !has404Error.value && localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) !== 'true') {
    // Try to load count once - if it fails with 404, we'll stop
    try {
      await loadCount()
      // Only start polling if loadCount succeeded (no 404)
      if (!has404Error.value && localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) !== 'true') {
        startPolling()
      }
    } catch (e: any) {
      // Error already handled in loadCount
    }
  }
}, { immediate: false })

watch(notificationsEnabled, (enabled) => {
  if (!enabled) {
    stopPolling()
    notifications.value = []
    unreadCount.value = 0
  } else if (auth.token && !has404Error.value) {
    // Only start if we haven't encountered a 404
    startPolling()
  }
})

onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  
  // Check localStorage first - if we've previously detected a 404, don't do anything
  if (localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) === 'true') {
    has404Error.value = true
    return
  }
  
  // Load module settings first to check if notifications are enabled
  if (auth.token) {
    await loadModuleSettings(true)
    // Only start if module is enabled and we haven't encountered a 404
    if (notificationsEnabled.value && !has404Error.value) {
      try {
        // Try to load count first - if it fails with 404, we won't start polling
        await loadCount()
        // Only start polling if loadCount succeeded (no 404 detected)
        if (!has404Error.value && localStorage.getItem(NOTIFICATIONS_DISABLED_KEY) !== 'true') {
          startPolling()
        }
      } catch (e: any) {
        // Error already handled in loadCount
      }
    }
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('notification-refresh', refreshNotifications)
  stopPolling()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>

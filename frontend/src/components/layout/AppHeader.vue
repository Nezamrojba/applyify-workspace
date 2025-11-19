<template>
  <header ref="headerRef" class="border-b border-black/5 sticky top-0 backdrop-blur bg-white/80 z-50">
    <div class="w-full max-w-9xl mx-auto h-14 flex items-center justify-between px-4 lg:px-6">
      <!-- Logo -->
      <router-link to="/" class="font-semibold text-sm sm:text-base">{{ $t('app.title') }}</router-link>

      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center gap-3">
        <LocaleSwitcher />
        <router-link
          v-for="item in desktopNavItems"
          :key="item.to"
          :to="item.to"
          :class="['text-sm transition-colors', item.isPrimary ? 'text-primary' : 'text-muted hover:text-primary']"
        >
          {{ item.label }}
        </router-link>
        <div v-if="isAuthed" class="flex items-center gap-2">
          <NotificationsButton v-if="showNotifications" />
          <AccountMenu />
        </div>
        <Button v-else size="sm" @click="ui.openAuth()">{{ $t('auth.loginTitle') }}</Button>
      </nav>

      <!-- Mobile Menu Button -->
      <div class="flex md:hidden items-center gap-2 relative z-[60]">
        <LocaleSwitcher />
        <button
          ref="menuButtonRef"
          @click.stop="toggleMobileMenu"
          type="button"
          class="p-2 rounded-lg hover:bg-black/5 transition-colors"
          :aria-label="mobileMenuOpen ? $t('header.closeMenu') : $t('header.openMenu')"
          :aria-expanded="mobileMenuOpen"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="h-5 w-5"
          >
            <path v-if="!mobileMenuOpen" d="M3 12h18M3 6h18M3 18h18" />
            <path v-else d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <teleport to="body">
      <transition name="fade">
        <div
          v-if="mobileMenuOpen"
          class="fixed bg-black/50 z-[100] md:hidden"
          style="top: 3.5rem; left: 0; right: 0; bottom: 0;"
          @click.stop="closeMobileMenu"
        />
      </transition>

      <!-- Mobile Menu -->
      <transition :name="isRtl ? 'slide-left' : 'slide-right'">
        <nav
          v-if="mobileMenuOpen"
          ref="mobileMenuRef"
          :dir="isRtl ? 'rtl' : 'ltr'"
          :class="[
            'fixed top-14 bottom-0 w-64 bg-white border shadow-xl z-[110] overflow-y-auto md:hidden',
            isRtl ? 'left-0 border-r border-black/10' : 'right-0 border-l border-black/10'
          ]"
          @click.stop
        >
          <div class="flex flex-col h-full">
            <!-- Authenticated User Section -->
            <template v-if="isAuthed">
              <!-- User Profile Header -->
              <div class="p-4 border-b border-black/10 bg-surface/50">
                <div class="flex items-center gap-3 mb-2">
                  <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <span class="text-primary font-semibold text-sm uppercase">
                      {{ auth.user?.name?.charAt(0) || 'U' }}{{ auth.user?.name?.split(' ')[1]?.charAt(0) || '' }}
                    </span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-text truncate">{{ auth.user?.name || '—' }}</div>
                    <div class="text-xs text-muted mt-0.5">{{ userRoleLabel }}</div>
                  </div>
                </div>
              </div>

              <!-- Navigation Links -->
              <div class="flex-1 overflow-y-auto p-2">
                <nav class="space-y-1">
                  <router-link
                    v-for="item in mobileNavItems"
                    :key="item.to"
                    :to="item.to"
                    :class="[
                      'block px-3 py-2.5 text-sm font-medium rounded-md transition-colors',
                      item.isPrimary ? 'text-primary bg-primary/5 font-semibold' : 'text-text hover:text-primary hover:bg-black/5'
                    ]"
                    @click="closeMobileMenu"
                  >
                    {{ item.label }}
                  </router-link>
                </nav>
              </div>

              <!-- Bottom Actions -->
              <div class="p-4 border-t border-black/10 space-y-2 bg-surface/30">
                <!-- Notifications -->
                <div v-if="showNotifications" class="pb-2">
                  <NotificationsButton />
                </div>

                <!-- Account Actions -->
                <button
                  @click.stop="handleMobileProfile"
                  class="w-full text-left px-3 py-2.5 text-sm font-medium text-text hover:text-primary hover:bg-black/5 rounded-md transition-colors"
                >
                  {{ $t('header.profile') }}
                </button>
                <button
                  @click.stop="handleMobileLogout"
                  class="w-full text-left px-3 py-2.5 text-sm font-medium text-danger hover:bg-danger/5 rounded-md transition-colors"
                >
                  {{ $t('header.logout') }}
                </button>
              </div>
            </template>

            <!-- Not Authenticated Section -->
            <template v-else>
              <div class="flex flex-col h-full">
                <!-- Navigation Links -->
                <div class="flex-1 overflow-y-auto p-4">
                  <nav class="space-y-1">
                    <router-link
                      v-for="item in mobileNavItems"
                      :key="item.to"
                      :to="item.to"
                      :class="[
                        'block px-3 py-2.5 text-sm font-medium rounded-md transition-colors',
                        item.isPrimary ? 'text-primary bg-primary/5' : 'text-muted hover:text-primary hover:bg-black/5'
                      ]"
                      @click="closeMobileMenu"
                    >
                      {{ item.label }}
                    </router-link>
                  </nav>
                </div>

                <!-- Login Section -->
                <div class="p-4 border-t border-black/10 bg-surface/30">
                  <Button class="w-full" size="sm" @click.stop="handleMobileLogin">
                    {{ $t('auth.loginTitle') }}
                  </Button>
                </div>
              </div>
            </template>
          </div>
        </nav>
      </transition>
    </teleport>
  </header>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter, useRoute } from 'vue-router'
import LocaleSwitcher from '@/components/LocaleSwitcher.vue'
import Button from '@/components/ui/Button.vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import NotificationsButton from '@/components/layout/NotificationsButton.vue'
import AccountMenu from '@/components/layout/AccountMenu.vue'
import { useModuleStatus } from '@/composables/useModuleStatus'

// Composables
const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()
const route = useRoute()
const { isModuleEnabled } = useModuleStatus()
const { t, locale } = useI18n()

// Refs
const headerRef = ref<HTMLElement | null>(null)
const menuButtonRef = ref<HTMLElement | null>(null)
const mobileMenuRef = ref<HTMLElement | null>(null)

// State
const mobileMenuOpen = ref(false)

// Computed: Authentication & Authorization
const isAuthed = computed(() => !!auth.token)
const isAdmin = computed(() => !!auth.token && auth.user?.role === 'super_admin')
const isStaff = computed(() => !!auth.token && auth.user?.role === 'staff')
const isStudent = computed(() => !!auth.token && auth.user?.role === 'student')
const isRtl = computed(() => locale.value === 'ar')
const showNotifications = computed(() => isModuleEnabled('notifications'))

// Computed: Navigation Items
const desktopNavItems = computed(() => {
  const items = [
    { to: '/faqs', label: t('header.faqs'), isPrimary: false },
    { to: '/ask-advisor', label: t('header.ask'), isPrimary: false }
  ]

  if (isAdmin.value) {
    items.push({ to: '/admin', label: t('header.admin'), isPrimary: true })
  } else if (isStaff.value) {
    items.push({ to: '/staff', label: t('header.myAccount'), isPrimary: true })
  } else if (isStudent.value) {
    items.push({ to: '/dashboard', label: t('header.myAccount'), isPrimary: true })
  }

  return items
})

const mobileNavItems = computed(() => {
  return desktopNavItems.value
})

// Computed: User Role Label
const userRoleLabel = computed(() => {
  if (isAdmin.value) return t('header.roleAdmin')
  if (isStaff.value) return t('header.roleStaff')
  if (isStudent.value) return t('header.roleStudent')
  return ''
})

// Methods: Mobile Menu
function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
  updateBodyScroll()
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
  updateBodyScroll()
}

function updateBodyScroll() {
  if (mobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

function handleMobileLogin() {
  closeMobileMenu()
  ui.openAuth()
}

// Methods: Authenticated Actions
function handleMobileProfile() {
  closeMobileMenu()
  if (isAdmin.value) {
    router.push('/admin')
  } else if (isStaff.value) {
    router.push('/staff')
  } else if (isStudent.value) {
    router.push('/dashboard')
  }
}

async function handleMobileLogout() {
  closeMobileMenu()
  try {
    await auth.logout()
  } catch {
    // Ignore errors
  }
  router.push('/')
}

// Lifecycle: Handle Outside Clicks & Escape Key
function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  
  // Check if click is outside menu and button
  const isInsideMenu = mobileMenuRef.value?.contains(target)
  const isInsideButton = menuButtonRef.value?.contains(target)
  const isInsideHeader = headerRef.value?.contains(target)
  
  if (mobileMenuOpen.value && !isInsideMenu && !isInsideButton && !isInsideHeader) {
    closeMobileMenu()
  }
}

function handleEscape(e: KeyboardEvent) {
  if (e.key === 'Escape' && mobileMenuOpen.value) {
    closeMobileMenu()
  }
}

// Watch: Close menu on route change
watch(() => route.path, () => {
  if (mobileMenuOpen.value) {
    closeMobileMenu()
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Fade transition for overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide transition for menu (RTL: left, LTR: right) */
.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.2s ease;
}

.slide-right-enter-from {
  transform: translateX(100%);
}

.slide-right-leave-to {
  transform: translateX(100%);
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.2s ease;
}

.slide-left-enter-from {
  transform: translateX(-100%);
}

.slide-left-leave-to {
  transform: translateX(-100%);
}
</style>

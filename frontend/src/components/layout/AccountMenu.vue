<template>
  <div class="relative" ref="menuRef">
    <button @click.stop="open=!open" :style="{ backgroundColor: color }" class="h-9 w-9 rounded-full grid place-items-center text-white text-sm uppercase" aria-haspopup="menu" :aria-expanded="open">
      {{ initials }}
    </button>
    <transition name="fade">
      <div
        v-if="open"
        :dir="isRtl ? 'rtl' : 'ltr'"
        :class="[
          'absolute mt-2 w-44 rounded-md bg-white border border-black/10 shadow-lg p-1 z-[60]',
          isRtl ? 'left-0' : 'right-0'
        ]"
      >
        <router-link 
          :to="profileLink" 
          @click="open=false" 
          :class="[
            'block px-3 py-2 text-sm hover:bg-black/5 rounded transition-colors',
            isRtl ? 'text-right' : 'text-left'
          ]"
        >
          {{ $t('header.profile') }}
        </router-link>
        <button 
          :class="[
            'block w-full px-3 py-2 text-sm hover:bg-black/5 rounded transition-colors',
            isRtl ? 'text-right' : 'text-left'
          ]" 
          @click="logout"
        >
          {{ $t('header.logout') }}
        </button>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAvatar } from '@/composables/useAvatar'
import { useI18n } from 'vue-i18n'

const auth = useAuthStore()
const { locale, t } = useI18n()
const open = ref(false)
const menuRef = ref<HTMLElement | null>(null)
const nameRef = computed(() => auth.user?.name || 'NA')
const { initials, color } = useAvatar(nameRef)
const profileLink = computed(() => {
  if (auth.user?.role === 'super_admin') return '/admin'
  if (auth.user?.role === 'staff') return '/staff'
  return '/dashboard'
})

const isRtl = computed(() => locale.value === 'ar')

function handleClickOutside(e: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function logout(){ 
  open.value = false
  try { await auth.logout() } catch {}; location.href = '/' 
}
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


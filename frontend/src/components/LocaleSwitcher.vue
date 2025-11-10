<template>
  <div class="locale-switcher relative">
    <button @click="toggle" class="flex items-center gap-1 text-sm font-medium">
      {{ currentLabel }}
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </button>
    <div v-if="open" class="absolute right-0 mt-2 w-28 rounded-lg border border-black/5 bg-white shadow-lg z-50">
      <button
        v-for="option in options"
        :key="option.value"
        class="w-full text-left px-3 py-2 text-sm hover:bg-black/5"
        @click="select(option.value)"
      >
        {{ option.label }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { applyDir } from '@/i18n'

const open = ref(false)
const { locale } = useI18n()

const options = [
  { value: 'en', label: 'English' },
  { value: 'ar', label: 'عربي' }
]

const currentLabel = computed(() => options.find(o => o.value === locale.value)?.label || 'English')

function select(value: string) {
  locale.value = value as 'en' | 'ar'
  localStorage.setItem('locale', value)
  applyDir(value)
  open.value = false
}

function toggle() {
  open.value = !open.value
}

function handleClickOutside(event: MouseEvent) {
  const target = event.target as HTMLElement
  if (!target.closest('.locale-switcher')) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

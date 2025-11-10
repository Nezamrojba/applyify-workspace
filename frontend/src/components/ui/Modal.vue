<template>
  <transition name="fade">
    <div v-if="open" class="fixed inset-0 z-50 grid place-items-center">
      <!-- Backdrop click disabled - modals cannot be closed by clicking outside -->
      <div class="absolute inset-0 bg-black/40"></div>
      <transition name="pop">
        <div v-if="open" :class="['relative bg-white rounded-xl shadow-xl w-[90vw] p-6 flex flex-col max-h-[90vh]', sizeClasses]">
          <button
            @click="$emit('close')"
            class="absolute top-4 text-muted hover:text-text transition-colors z-10"
            :class="isRtl ? 'left-4' : 'right-4'"
            aria-label="Close modal"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          
          <div v-if="$slots.header" class="mb-4 pb-4 border-b border-black/5" :class="isRtl ? 'pl-8' : 'pr-8'">
            <slot name="header" />
          </div>
          <div class="flex-1 overflow-y-auto">
            <slot />
          </div>
          <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-black/5">
            <slot name="footer" />
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{ open: boolean; size?: 'sm' | 'md' | 'lg' | 'xl' }>()
defineEmits(['close'])

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl'
  }
  return sizes[props.size || 'md']
})

const { locale } = useI18n()
const isRtl = computed(() => locale.value === 'ar')
</script>

<style scoped>
.fade-enter-active,.fade-leave-active{ transition: opacity .15s ease }
.fade-enter-from,.fade-leave-to{ opacity: 0 }
.pop-enter-active,.pop-leave-active{ transition: transform .16s ease, opacity .16s ease }
.pop-enter-from,.pop-leave-to{ transform: scale(.98); opacity: 0 }
</style>

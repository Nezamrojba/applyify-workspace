<template>
  <button :class="classes" :disabled="disabled" @click="handleClick">
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ variant?: 'primary'|'ghost'|'danger'|'success'; size?: 'sm'|'md'|'lg'; disabled?: boolean }>()
const emit = defineEmits(['click'])

const classes = computed(() => {
  const v = props.variant || 'primary'
  const s = props.size || 'md'
  const base = 'inline-flex items-center justify-center rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed shadow-sm'
  const pv = v === 'primary' ? 'bg-primary text-white hover:bg-primary/90 shadow-md shadow-black/10' : 
           v === 'danger' ? 'bg-danger text-white hover:bg-danger/90 shadow-md shadow-black/10' : 
           v === 'success' ? 'bg-success text-white hover:bg-success/90 shadow-md shadow-black/10' :
           'bg-white text-text hover:bg-white/90 border border-black/10 shadow-sm'
  const ps = s === 'sm' ? 'h-8 px-3 text-sm' : s === 'lg' ? 'h-12 px-5 text-base' : 'h-10 px-4 text-sm'
  return `${base} ${pv} ${ps}`
})

function handleClick(event: MouseEvent) {
  if (props.disabled) {
    event.preventDefault()
    event.stopPropagation()
    return
  }
  emit('click', event)
}
</script>

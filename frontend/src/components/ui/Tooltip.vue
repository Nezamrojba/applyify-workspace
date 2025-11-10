<template>
  <div class="relative inline-block" @mouseenter="open=true" @mouseleave="open=false" @focusin="open=true" @focusout="open=false">
    <button type="button" class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-black/5 text-muted hover:bg-black/10 focus:outline-none focus:ring-2 focus:ring-primary/30" :aria-describedby="id">
      <span class="sr-only">Help</span>
      i
    </button>
    <div v-if="open" :id="id" role="tooltip" class="absolute z-10 mt-2 w-56 rounded-lg bg-black text-white text-xs p-3 shadow-lg md:right-0 md:translate-x-0" style="left:50%;transform:translateX(-50%);">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
const open = ref(false)
const id = `tip-${Math.random().toString(36).slice(2)}`
</script>


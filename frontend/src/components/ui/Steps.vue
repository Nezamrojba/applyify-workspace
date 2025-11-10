<template>
  <ol class="flex flex-wrap items-center gap-3">
    <li v-for="(s, i) in steps" :key="i" class="flex items-center gap-2">
      <span :class="dotClass(i)">{{ i + 1 }}</span>
      <span class="text-sm" :class="i === current ? 'text-primary' : 'text-muted'">{{ s }}</span>
      <span v-if="i < steps.length - 1" class="w-6 h-px bg-white/10 mx-1"></span>
    </li>
  </ol>
</template>

<script setup lang="ts">
const props = defineProps<{ steps: string[]; current: number }>()
function dotClass(i: number) {
  const base = 'w-6 h-6 rounded-full text-xs grid place-items-center'
  return i === props.current ? `${base} bg-primary text-white` : `${base} bg-white/10 text-text`
}
</script>


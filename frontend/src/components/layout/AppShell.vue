<template>
  <div class="min-h-[70vh] grid grid-cols-1 md:grid-cols-[240px,1fr] gap-0">
    <div class="hidden md:block border-r border-black/5 bg-white">
      <div class="h-[calc(100vh-56px)] overflow-y-auto">
        <SideNav :items="items" />
      </div>
    </div>
    <div>
      <div class="sticky top-14 z-10 bg-white/80 backdrop-blur border-b border-black/5">
        <div class="w-full max-w-7xl mx-auto h-12 flex items-center justify-between gap-3 px-4 lg:px-6">
          <div class="text-sm font-medium truncate">
            <slot name="header">{{ title }}</slot>
          </div>
          <div class="hidden lg:block" />
        </div>
      </div>
      <div class="p-4 lg:p-6 w-full max-w-7xl mx-auto">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import SideNav from '@/components/layout/SideNav.vue'
defineProps<{ items: { to: string; label: string; badge?: string|number }[]; title?: string }>()
</script>

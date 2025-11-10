<template>
  <div class="h-full flex flex-col">
    <div v-if="!hideBrand" class="px-4 py-4 border-b border-black/5">
      <router-link to="/" class="text-sm font-semibold">{{$t('app.title')}}</router-link>
    </div>
    <div class="flex-1 overflow-y-auto">
      <nav class="px-3 py-3 space-y-1">
        <template v-for="it in items" :key="it.label + (it.to || '')">
          <template v-if="it.children && it.children.length">
            <button class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-md text-muted hover:bg-black/5"
                    @click="toggle(it.label)">
              <span class="truncate">{{ it.label }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" :class="isOpen(it.label) ? 'h-4 w-4 rotate-90 transition-transform' : 'h-4 w-4 transition-transform'">
                <path fill-rule="evenodd" d="M6.292 4.94a1 1 0 011.415 0l4.243 4.243a1 1 0 010 1.415L7.707 14.84a1 1 0 01-1.414-1.415L9.121 10 5.293 6.354a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div v-if="isOpen(it.label)" class="pl-3 space-y-1">
              <router-link v-for="ch in it.children" :key="ch.to" :to="ch.to!"
                           class="group flex items-center px-3 py-2 text-sm rounded-md"
                           :class="route.path===ch.to ? 'bg-primary/5 text-primary' : 'text-muted hover:bg-black/5'"
                           @click="$emit('navigate')">
                <span class="truncate">{{ ch.label }}</span>
                <span v-if="ch.badge" class="ml-auto text-xs px-2 py-0.5 rounded bg-primary/10 text-primary">{{ ch.badge }}</span>
              </router-link>
            </div>
          </template>
          <router-link v-else-if="it.to" :to="it.to" class="group flex items-center px-3 py-2 text-sm rounded-md"
                       :class="route.path===it.to ? 'bg-primary/5 text-primary' : 'text-muted hover:bg-black/5'"
                       @click="$emit('navigate')">
            <span class="truncate">{{ it.label }}</span>
            <span v-if="it.badge" class="ml-auto text-xs px-2 py-0.5 rounded bg-primary/10 text-primary">{{ it.badge }}</span>
          </router-link>
          <span v-else class="block px-3 py-2 text-sm text-muted">{{ it.label }}</span>
        </template>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRoute } from 'vue-router'
type NavItem = { to?: string; label: string; badge?: string|number; children?: NavItem[] }
const props = defineProps<{ items: NavItem[]; hideBrand?: boolean }>()
defineEmits(['navigate'])
const route = useRoute()
const open = reactive<Record<string,boolean>>({})
function isOpen(label: string){ return !!open[label] }
function toggle(label: string){ open[label] = !open[label] }
</script>

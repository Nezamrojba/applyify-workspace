<template>
  <div
    v-if="pageCount > 1"
    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-3"
  >
    <div class="text-xs text-muted">
      Showing
      <span class="font-medium text-text">
        {{ startItem }} - {{ endItem }}
      </span>
      of
      <span class="font-medium text-text">{{ total }}</span>
    </div>
    <div class="flex items-center gap-1">
      <button
        class="px-2 h-8 rounded border border-black/10 bg-white text-xs disabled:opacity-50"
        :disabled="page <= 1"
        @click="setPage(page - 1)"
      >
        Prev
      </button>
      <button
        v-for="n in visiblePages"
        :key="n"
        class="px-3 h-8 rounded border text-xs transition-colors"
        :class="n === page ? 'bg-primary text-white border-primary' : 'border-black/10 bg-white hover:bg-primary/10'"
        @click="setPage(n)"
      >
        {{ n }}
      </button>
      <button
        class="px-2 h-8 rounded border border-black/10 bg-white text-xs disabled:opacity-50"
        :disabled="page >= pageCount"
        @click="setPage(page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  page: number
  perPage: number
  total: number
  maxButtons?: number
}>()

const emit = defineEmits<{ (e: 'update:page', value: number): void }>()

const pageCount = computed(() => {
  return Math.max(1, Math.ceil((props.total || 0) / props.perPage))
})

const page = computed(() => Math.min(Math.max(1, props.page || 1), pageCount.value))

const startItem = computed(() => ((page.value - 1) * props.perPage) + 1)
const endItem = computed(() => Math.min(page.value * props.perPage, props.total))

const visiblePages = computed(() => {
  const maxButtons = props.maxButtons ?? 5
  const totalPages = pageCount.value
  if (totalPages <= maxButtons) {
    return Array.from({ length: totalPages }, (_, i) => i + 1)
  }
  const half = Math.floor(maxButtons / 2)
  let start = Math.max(1, page.value - half)
  let end = Math.min(totalPages, start + maxButtons - 1)
  if (end - start + 1 < maxButtons) {
    start = Math.max(1, end - maxButtons + 1)
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

function setPage(newPage: number) {
  const normalized = Math.min(Math.max(1, newPage), pageCount.value)
  emit('update:page', normalized)
}
</script>


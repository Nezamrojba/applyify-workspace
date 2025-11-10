<template>
  <div class="fixed top-16 right-4 z-50 space-y-2 w-[92vw] sm:w-96">
    <transition-group name="toast-fade" tag="div">
      <div v-for="t in toasts" :key="t.id" :class="toastClass(t.type)" class="rounded-xl shadow-lg overflow-hidden backdrop-blur-sm">
        <div class="flex items-start gap-3 p-4">
          <div :class="iconClass(t.type)" class="mt-0.5 flex-shrink-0">
            <svg v-if="t.type==='success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-2.943a.75.75 0 10-1.22-.914l-3.236 4.32-1.69-1.69a.75.75 0 10-1.06 1.061l2.25 2.25a.75.75 0 001.14-.094l3.816-4.933z" clip-rule="evenodd"/></svg>
            <svg v-else-if="t.type==='error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM9.53 8.47a.75.75 0 011.06 0L12 9.88l1.41-1.41a.75.75 0 111.06 1.06L13.06 10.94l1.41 1.41a.75.75 0 11-1.06 1.06L12 12l-1.47 1.41a.75.75 0 11-1.06-1.06l1.41-1.41-1.41-1.41a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
            <svg v-else-if="t.type==='warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M10.788 3.21a1.35 1.35 0 012.424 0l8.387 18.366A1.35 1.35 0 0120.387 24H3.613a1.35 1.35 0 01-1.212-1.924L10.788 3.21zM12 9a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0112 9zm0 8.25a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM11.25 7.5a.75.75 0 011.5 0v6a.75.75 0 01-1.5 0v-6zm.75 9a1.125 1.125 0 100 2.25A1.125 1.125 0 0012 16.5z" clip-rule="evenodd"/></svg>
          </div>
          <div class="flex-1 text-sm font-medium" :class="textClass(t.type)">{{ t.message }}</div>
          <button :class="closeClass(t.type)" class="h-6 w-6 grid place-items-center rounded-full hover:bg-black/5 transition-colors" @click="close(t.id)">×</button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useToastStore } from '@/stores/toast'
const toast = useToastStore()
const { items: toasts } = storeToRefs(toast)
function close(id: number){ toast.remove(id) }
function toastClass(type: string){
  if (type === 'success') return 'bg-green-50/95 border-l-4 border-green-500 ring-1 ring-green-200/50'
  if (type === 'error') return 'bg-red-50/95 border-l-4 border-red-500 ring-1 ring-red-200/50'
  if (type === 'warning') return 'bg-yellow-50/95 border-l-4 border-yellow-500 ring-1 ring-yellow-200/50'
  return 'bg-blue-50/95 border-l-4 border-blue-500 ring-1 ring-blue-200/50'
}
function iconClass(type: string){
  if (type === 'success') return 'text-green-600'
  if (type === 'error') return 'text-red-600'
  if (type === 'warning') return 'text-yellow-600'
  return 'text-blue-600'
}
function textClass(type: string){
  if (type === 'success') return 'text-green-900'
  if (type === 'error') return 'text-red-900'
  if (type === 'warning') return 'text-yellow-900'
  return 'text-blue-900'
}
function closeClass(type: string){
  if (type === 'success') return 'text-green-600 hover:text-green-700'
  if (type === 'error') return 'text-red-600 hover:text-red-700'
  if (type === 'warning') return 'text-yellow-600 hover:text-yellow-700'
  return 'text-blue-600 hover:text-blue-700'
}
</script>

<style scoped>
.toast-fade-enter-active,.toast-fade-leave-active{ transition: all .18s ease }
.toast-fade-enter-from{ opacity: 0; transform: translateY(-6px) }
.toast-fade-leave-to{ opacity: 0; transform: translateY(-6px) }
</style>

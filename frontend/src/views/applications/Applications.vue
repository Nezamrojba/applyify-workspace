<template>
  <div class="container py-8 space-y-6">
    <!-- ApplicationCreateBar removed - applications must be created through the payment flow via course catalog -->
    <div class="p-4 rounded-lg bg-info/5 border border-info/20">
      <div class="flex items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-info mt-0.5 flex-shrink-0">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
        </svg>
        <div class="flex-1">
          <div class="text-sm font-medium text-info">New Application</div>
          <div class="text-xs text-muted mt-1">To create a new application, please browse courses and use the "Apply" button. Payment receipt upload is required.</div>
        </div>
      </div>
    </div>
    <div class="grid gap-3">
      <ApplicationCard v-for="a in apps.items" :key="a.id" :app="a" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useApplicationStore } from '@/stores/applications'
import ApplicationCard from '@/components/applications/ApplicationCard.vue'

const apps = useApplicationStore()

onMounted(() => apps.fetch())
</script>

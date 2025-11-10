<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-muted">
          <th class="py-2 px-2">Name</th>
          <th class="py-2 px-2">Country</th>
          <th class="py-2 px-2">Status</th>
          <th class="py-2 px-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in universities" :key="u.id" class="border-t border-black/5">
          <td class="py-2 px-2 font-medium">{{ u.name }}</td>
          <td class="py-2 px-2 text-muted">{{ u.country?.name || '-' }}</td>
          <td class="py-2 px-2">
            <span
              :class="(u.is_active ?? true) ? 'text-green-600' : 'text-red-600'"
              class="inline-flex items-center text-xs px-2 py-0.5 rounded"
              :style="{ backgroundColor: (u.is_active ?? true) ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }"
            >
              {{ (u.is_active ?? true) ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="py-2 px-2">
            <div class="flex items-center gap-3">
              <button class="text-xs text-primary hover:underline" @click="$emit('edit', u.id)">Edit</button>
              <button class="text-xs text-danger hover:underline" @click="$emit('delete', u.id)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
type University = { id: number; name: string; country_id?: number; country?: { id: number; name: string }; is_active?: boolean }
defineProps<{ universities: University[] }>()
defineEmits(['edit','delete'])
</script>

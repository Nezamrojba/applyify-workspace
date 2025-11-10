<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-muted">
          <th class="py-2 px-2">Name</th>
          <th class="py-2 px-2">Code</th>
          <th class="py-2 px-2">Level</th>
          <th class="py-2 px-2">University</th>
          <th class="py-2 px-2">Acceptance %</th>
          <th class="py-2 px-2">Duration</th>
          <th class="py-2 px-2">Status</th>
          <th class="py-2 px-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in courses" :key="c.id" class="border-t border-black/5">
          <td class="py-2 px-2 font-medium">{{ c.name }}</td>
          <td class="py-2 px-2 text-muted">{{ c.code || '-' }}</td>
          <td class="py-2 px-2 text-muted">{{ c.level || '-' }}</td>
          <td class="py-2 px-2 text-muted">{{ c.university?.name || '-' }}</td>
          <td class="py-2 px-2 text-muted">{{ c.acceptance_percent ? `${c.acceptance_percent}%` : '-' }}</td>
          <td class="py-2 px-2 text-muted">{{ c.duration_months ? `${c.duration_months} months` : '-' }}</td>
          <td class="py-2 px-2">
            <span :class="c.is_active ? 'text-green-600' : 'text-red-600'" class="inline-flex items-center text-xs px-2 py-0.5 rounded" :style="{ backgroundColor: c.is_active ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }">
              {{ c.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="py-2 px-2">
            <div class="flex items-center gap-3">
              <button class="text-xs text-primary hover:underline" @click="$emit('edit', c.id)">Edit</button>
              <button class="text-xs text-danger hover:underline" @click="$emit('delete', c.id)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
type Course = { 
  id: number
  name: string
  code?: string
  level?: string
  university_id: number
  university?: { id: number; name: string }
  acceptance_percent?: number
  duration_months?: number
  is_active?: boolean
}
defineProps<{ courses: Course[] }>()
defineEmits(['edit','delete'])
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-muted">
          <th class="py-2 px-2">Name</th>
          <th class="py-2 px-2">Email</th>
          <th class="py-2 px-2">Role</th>
          <th class="py-2 px-2">Status</th>
          <th class="py-2 px-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in users" :key="u.id" class="border-t border-black/5">
          <td class="py-2 px-2">{{ u.name }}</td>
          <td class="py-2 px-2 text-muted">{{ u.email }}</td>
          <td class="py-2 px-2">
            <span class="inline-flex items-center text-xs px-2 py-0.5 rounded bg-primary/5 text-primary">{{ u.role }}</span>
          </td>
          <td class="py-2 px-2">
            <span :class="u.is_active ? 'text-green-600' : 'text-red-600'" class="inline-flex items-center text-xs px-2 py-0.5 rounded" :style="{ backgroundColor: u.is_active ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }">
              {{ u.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="py-2 px-2">
            <div class="flex items-center gap-3">
              <template v-if="u.role !== 'super_admin'">
                <button class="text-xs text-primary hover:underline" @click="$emit('edit', u.id)">Edit</button>
                <RoleAssignCell :value="roleAssign[u.id] || ''" @update:value="v => roleAssign[u.id]=v" @assign="r => $emit('assign', u.id, r)" />
                <button class="text-xs text-danger hover:underline" @click="$emit('delete', u.id)">Delete</button>
              </template>
              <template v-else>
                <button class="text-xs text-primary hover:underline" @click="$emit('edit', u.id)">Edit</button>
                <span class="text-xs text-muted">Locked</span>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import RoleAssignCell from '@/components/admin/users/RoleAssignCell.vue'
defineProps<{ users: { id:number; name:string; email:string; role:string; is_active?:boolean }[]; roleAssign: Record<number,string|''> }>()
defineEmits(['assign','delete','edit'])
</script>

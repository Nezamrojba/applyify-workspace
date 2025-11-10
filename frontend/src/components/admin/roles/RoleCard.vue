<template>
  <div class="p-3 rounded-lg border border-black/5">
    <div class="flex items-center justify-between gap-2">
      <input v-if="editing" v-model="editName" @keyup.enter="saveEdit" @keyup.esc="cancelEdit" class="flex-1 px-2 py-1 text-sm border border-black/20 rounded bg-white font-medium" />
      <div v-else class="font-medium">{{ role.name }}</div>
      <div v-if="editing" class="flex items-center gap-1">
        <Button size="sm" @click="saveEdit">Save</Button>
        <Button size="sm" variant="ghost" @click="cancelEdit">Cancel</Button>
      </div>
      <button v-else-if="role.name !== 'super_admin'" class="text-xs text-primary hover:underline" @click="startEdit">Edit</button>
    </div>
    <div class="mt-3 stack">
      <div class="text-sm text-muted">Attach permissions</div>
      <div class="flex flex-wrap gap-2">
        <label v-for="p in permissions" :key="p.name" class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" :value="p.name" v-model="model" />
          <span>{{ p.name }}</span>
        </label>
      </div>
      <div class="flex items-center justify-between">
        <button class="text-xs text-danger hover:underline" @click="$emit('delete', role.name)">Delete</button>
        <Button size="sm" @click="$emit('sync', role.name, model)">Save</Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from '@/components/ui/Button.vue'
import { useToast } from '@/composables/useToast'
import { api } from '@/services/api'

const props = defineProps<{ role: { name: string }; permissions: { name: string }[]; selected: string[] }>()
const emit = defineEmits(['sync','update:selected','delete','updated'])
const model = ref<string[]>(props.selected || [])
const editing = ref(false)
const editName = ref('')
const toast = useToast()

watch(model, v => { /* sync to parent if needed */ })

function startEdit(){
  editing.value = true
  editName.value = props.role.name
}

function cancelEdit(){
  editing.value = false
  editName.value = ''
}

async function saveEdit(){
  if (!editName.value || editName.value === props.role.name) {
    cancelEdit()
    return
  }
  try {
    await api.admin.roles.update(props.role.name, editName.value)
    toast.success('Role updated')
    emit('updated')
    cancelEdit()
  } catch(e:any) {
    toast.error(e?.message || 'Failed to update role')
  }
}
</script>

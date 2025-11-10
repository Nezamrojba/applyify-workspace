<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">Permissions</h3>
      <div class="flex items-center gap-2">
        <Input v-model="name" placeholder="New permission (e.g., users.manage)" />
        <Button size="sm" @click="create">Add</Button>
      </div>
    </div>

    <Card>
      <template #header><div class="text-sm text-muted">All permissions</div></template>
      <ul class="text-sm grid md:grid-cols-2 gap-2">
        <li v-for="p in items" :key="p.name" class="px-3 py-2 rounded border border-black/5 bg-white flex items-center justify-between gap-2">
          <input v-if="editing[p.name]" v-model="editNames[p.name]" @keyup.enter="saveEdit(p.name)" @keyup.esc="cancelEdit(p.name)" class="flex-1 px-2 py-1 text-sm border border-black/20 rounded bg-white" />
          <span v-else class="flex-1">{{ p.name }}</span>
          <div class="flex items-center gap-2">
            <template v-if="editing[p.name]">
              <Button size="sm" @click="saveEdit(p.name)">Save</Button>
              <Button size="sm" variant="ghost" @click="cancelEdit(p.name)">Cancel</Button>
            </template>
            <template v-else>
              <button class="text-xs text-primary hover:underline" @click="startEdit(p.name)">Edit</button>
              <button class="text-xs text-danger hover:underline" @click="requestDelete(p.name)">Delete</button>
            </template>
          </div>
        </li>
      </ul>
    </Card>

    <ConfirmModal
      :open="confirmOpen"
      title="Delete permission"
      message="This action cannot be undone."
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="removePermission"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import { api } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const items = ref<{ name:string }[]>([])
const name = ref('')
const confirmOpen = ref(false)
const permToDelete = ref('')
const editing = ref<Record<string,boolean>>({})
const editNames = ref<Record<string,string>>({})

async function load(){ items.value = await api.admin.permissions.list() as any }
onMounted(load)

async function create(){
  if (!name.value) return
  const toast = useToast()
  try{ await api.admin.permissions.create(name.value); toast.success('Permission created') }catch(e:any){ toast.error(e?.message || 'Failed to create permission') }
  name.value = ''
  await load()
}

function startEdit(n: string){
  editing.value[n] = true
  editNames.value[n] = n
}

function cancelEdit(n: string){
  editing.value[n] = false
  delete editNames.value[n]
}

async function saveEdit(oldName: string){
  const newName = editNames.value[oldName]
  if (!newName || newName === oldName) {
    cancelEdit(oldName)
    return
  }
  const toast = useToast()
  try {
    await api.admin.permissions.update(oldName, newName)
    toast.success('Permission updated')
    cancelEdit(oldName)
    await load()
  } catch(e:any) {
    toast.error(e?.message || 'Failed to update permission')
  }
}

function requestDelete(n: string){ permToDelete.value = n; confirmOpen.value = true }

async function removePermission(){
  if (!permToDelete.value) { confirmOpen.value = false; return }
  const toast = useToast()
  try{ await api.admin.permissions.delete(permToDelete.value); toast.success('Permission deleted') }catch(e:any){ toast.error(e?.message || 'Failed to delete permission') }
  confirmOpen.value = false
  permToDelete.value = ''
  await load()
}
</script>

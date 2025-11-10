<template>
  <div class="stack">
    <div class="flex items-center justify-between">
      <h3 class="text-xl font-semibold">Roles</h3>
      <div class="flex items-center gap-2">
        <Input v-model="newRole" placeholder="New role name" />
        <Button size="sm" @click="createRole">Add</Button>
      </div>
    </div>

    <Card>
      <template #header><div class="text-sm text-muted">All roles</div></template>
      <div class="grid md:grid-cols-2 gap-4">
        <RoleCard v-for="r in roles" :key="r.name" :role="r" :permissions="permissions" :selected="selected[r.name] || []" @sync="onSync" @delete="requestDelete" @updated="load" />
      </div>
    </Card>
    <ConfirmModal
      :open="confirmOpen"
      title="Delete role"
      :message="'This action cannot be undone.'"
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="removeRole"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import RoleCard from '@/components/admin/roles/RoleCard.vue'
import { api } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const roles = ref<{ name: string }[]>([])
const permissions = ref<{ name: string }[]>([])
const selected = ref<Record<string,string[]>>({})
const newRole = ref('')
const confirmOpen = ref(false)
const roleToDelete = ref<string>('')

async function load(){
  roles.value = await api.admin.roles.list() as any
  permissions.value = await api.admin.permissions.list() as any
  // initialize selections empty by default
  roles.value.forEach(r => { if (!selected.value[r.name]) selected.value[r.name] = [] })
}
onMounted(load)

async function createRole(){
  if (!newRole.value) return
  const toast = useToast()
  try{ await api.admin.roles.create(newRole.value); toast.success('Role created') }catch(e:any){ toast.error(e?.message || 'Failed to create role') }
  newRole.value=''
  await load()
}
async function onSync(roleName: string, list: string[]){
  selected.value[roleName] = list
  const toast = useToast()
  try{ await api.admin.roles.syncPermissions(roleName, list || []); toast.success('Permissions saved') }catch(e:any){ toast.error(e?.message || 'Failed to save permissions') }
}
function requestDelete(name: string){
  if (name === 'super_admin') return
  roleToDelete.value = name
  confirmOpen.value = true
}
async function removeRole(){
  if (!roleToDelete.value) { confirmOpen.value = false; return }
  const toast = useToast()
  try{ await api.admin.roles.delete(roleToDelete.value); toast.success('Role deleted') }catch(e:any){ toast.error(e?.message || 'Failed to delete role') }
  confirmOpen.value = false
  roleToDelete.value = ''
  await load()
}
</script>

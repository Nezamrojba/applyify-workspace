<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">Users</h3>
      <Button size="sm" @click="openCreate=true">New User</Button>
    </div>

    <Card>
      <template #header>
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="text-sm text-muted">User list</div>
          <ListFilters v-model="filters" :schema="filterSchema" @change="load" />
        </div>
      </template>
      <UsersTable :users="paginatedUsers" :role-assign="roleAssign" @assign="assign" @delete="requestDelete" @edit="requestEdit" />
      <Pagination
        :page="page"
        :per-page="perPage"
        :total="filteredUsers.length"
        @update:page="page = $event"
      />
    </Card>

    <UserCreateModal :open="openCreate" :errors="createErrors" :submitting="creating" @close="openCreate=false" @submit="createFrom" />
    <UserEditModal :open="openEdit" :user="editingUser" :errors="editErrors" :submitting="editing" @close="openEdit=false" @submit="updateFrom" />
    <ConfirmModal
      :open="confirmOpen"
      title="Delete user"
      :message="'This action cannot be undone.'"
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="removeUser"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import UsersTable from '@/components/admin/users/UsersTable.vue'
import UserCreateModal from '@/components/admin/users/UserCreateModal.vue'
import UserEditModal from '@/components/admin/users/UserEditModal.vue'
import ListFilters from '@/components/filters/ListFilters.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import Pagination from '@/components/ui/Pagination.vue'

type User = { id:number; name:string; email:string; role:string; is_active?:boolean; whatsapp?:string; passport_no?:string }
type UserForEdit = { id:number; name:string; email:string; role:string; is_active:boolean; whatsapp?:string; passport_no?:string }
type FilterDef = { key: string; type: 'search'|'select'; placeholder?: string; options?: { value: string; label: string }[] }
const users = ref<User[]>([])
const filters = ref<{ q: string; role: string }>({ q: '', role: '' })
const filterSchema: FilterDef[] = [
  { key: 'q', type: 'search', placeholder: 'Search name or email' },
  { key: 'role', type: 'select', placeholder: 'All roles', options: [
    { value: 'super_admin', label: 'Super Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'student', label: 'Student' }
  ] }
]
const roleAssign = ref<Record<number,string|''>>({})
const openCreate = ref(false)
const openEdit = ref(false)
const editing = ref(false)
const editErrors = ref<Record<string, any>>({})
const editingUser = ref<UserForEdit|undefined>(undefined)
const creating = ref(false)
const createErrors = ref<Record<string, any>>({})
const form = ref<{ name:string; email:string; password:string; whatsapp?:string; role?:string }>({ name:'', email:'', password:'', whatsapp:'', role:'' })
const confirmOpen = ref(false)
const confirmUserId = ref<number|null>(null)
const page = ref(1)
const perPage = 10

const filteredUsers = computed(() => {
  let list = users.value
  if (filters.value.role) {
    list = list.filter(u => u.role === filters.value.role)
  }
  if (filters.value.q) {
    const needle = filters.value.q.toLowerCase()
    list = list.filter(u => (u.name || '').toLowerCase().includes(needle) || (u.email || '').toLowerCase().includes(needle))
  }
  return list
})

const paginatedUsers = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

watch(openCreate, (isOpen) => {
  if (!isOpen) {
    createErrors.value = {}
  }
})

watch(openEdit, (isOpen) => {
  if (!isOpen) {
    editErrors.value = {}
    editingUser.value = undefined
  }
})

watch(filteredUsers, () => {
  page.value = 1
})

async function load(){
  const q: any = {}
  if (filters.value.role) q.role = filters.value.role
  if (filters.value.q) q.q = filters.value.q
  const r = await api.admin.users.list(q) as any
  users.value = (r.data || r || []) as User[]
  page.value = 1
}
onMounted(load)

async function create(){
  const toast = useToast()
  try{
    await api.admin.users.create(form.value)
    toast.success('User created')
  }catch(e:any){ toast.error(e?.message || 'Failed to create user') }
  openCreate.value = false
  form.value = { name:'', email:'', password:'', whatsapp:'', role:'' }
  await load()
}
async function createFrom(payload: any){
  const toast = useToast()
  creating.value = true
  createErrors.value = {}
  try{
    await api.admin.users.create(payload)
    toast.success('User created')
    openCreate.value = false
    await load()
  }catch(e:any){
    createErrors.value = e || { message: 'Failed to create user' }
  }finally{
    creating.value = false
  }
}
function requestDelete(id: number){
  confirmUserId.value = id
  confirmOpen.value = true
}

async function requestEdit(id: number){
  editErrors.value = {}
  const target = users.value.find(u => u.id === id)
  if (!target) return
  try {
    const user = await api.admin.users.show(id) as any
    editingUser.value = { ...user, is_active: user.is_active ?? true } as UserForEdit
    openEdit.value = true
  } catch(e:any) {
    useToast().error(e?.message || 'Failed to load user')
  }
}

async function updateFrom(payload: any){
  if (!editingUser.value) return
  const toast = useToast()
  editing.value = true
  editErrors.value = {}
  try{
    const data: any = { ...payload }
    if (!data.password) delete data.password
    await api.admin.users.update(editingUser.value.id, data)
    toast.success('User updated')
    openEdit.value = false
    editingUser.value = undefined
    await load()
  }catch(e:any){
    editErrors.value = e || { message: 'Failed to update user' }
  }finally{
    editing.value = false
  }
}

async function removeUser(){
  const id = confirmUserId.value
  if (id == null) { confirmOpen.value = false; return }
  const target = users.value.find(u => u.id === id)
  if (!target) return
  if (target.role === 'super_admin') return
  const toast = useToast()
  try{ await api.admin.users.delete(id); toast.success('User deleted') }catch(e:any){ toast.error(e?.message || 'Failed to delete user') }
  confirmOpen.value = false
  confirmUserId.value = null
  await load()
}
async function assign(userId: number){
  const role = roleAssign.value[userId]
  if (!role) return
  const target = users.value.find(u => u.id === userId)
  if (target?.role === 'super_admin') return
  const toast = useToast()
  try{ await api.admin.users.roles.assign(userId, role); toast.success('Role assigned') }catch(e:any){ toast.error(e?.message || 'Failed to assign role') }
  roleAssign.value[userId] = ''
  await load()
}
</script>

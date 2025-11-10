<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">Universities</h3>
      <Button size="sm" @click="openCreate=true">New University</Button>
    </div>

    <Card>
      <template #header>
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="text-sm text-muted">University list</div>
          <ListFilters v-model="filters" :schema="filterSchema" @change="load" />
        </div>
      </template>
      <UniversitiesTable :universities="paginatedUniversities" @edit="requestEdit" @delete="requestDelete" />
      <Pagination
        :page="page"
        :per-page="perPage"
        :total="filteredUniversities.length"
        @update:page="page = $event"
      />
    </Card>

    <UniversityCreateModal :open="openCreate" :errors="createErrors" :submitting="creating" @close="openCreate=false" @submit="createFrom" />
    <UniversityEditModal :open="openEdit" :university="editingUniversity" :errors="editErrors" :submitting="editing" @close="openEdit=false" @submit="updateFrom" />
    <ConfirmModal
      :open="confirmOpen"
      title="Delete university"
      :message="'This action cannot be undone.'"
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="removeUniversity"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import UniversitiesTable from '@/components/admin/universities/UniversitiesTable.vue'
import UniversityCreateModal from '@/components/admin/universities/UniversityCreateModal.vue'
import UniversityEditModal from '@/components/admin/universities/UniversityEditModal.vue'
import ListFilters from '@/components/filters/ListFilters.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import Pagination from '@/components/ui/Pagination.vue'

type University = { id:number; name:string; country_id?:number; country?:{ id:number; name:string }; is_active?:boolean }
type FilterDef = { key: string; type: 'search'|'select'; placeholder?: string; options?: { value: string; label: string }[] }

const universities = ref<University[]>([])
const filters = ref<{ q: string }>({ q: '' })
const filterSchema: FilterDef[] = [
  { key: 'q', type: 'search', placeholder: 'Search name' }
]
const openCreate = ref(false)
const openEdit = ref(false)
const editing = ref(false)
const editErrors = ref<Record<string, any>>({})
const editingUniversity = ref<University|undefined>(undefined)
const creating = ref(false)
const createErrors = ref<Record<string, any>>({})
const confirmOpen = ref(false)
const confirmUniversityId = ref<number|null>(null)
const page = ref(1)
const perPage = 10

const filteredUniversities = computed(() => {
  let list = universities.value
  if (filters.value.q) {
    const needle = filters.value.q.toLowerCase()
    list = list.filter(u => (u.name || '').toLowerCase().includes(needle))
  }
  return list
})

const paginatedUniversities = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredUniversities.value.slice(start, start + perPage)
})

watch(openCreate, (isOpen) => {
  if (!isOpen) {
    createErrors.value = {}
  }
})

watch(openEdit, (isOpen) => {
  if (!isOpen) {
    editErrors.value = {}
    editingUniversity.value = undefined
  }
})

watch(filteredUniversities, () => {
  page.value = 1
})

async function load(){
  const toast = useToast()
  try {
    const r = await api.admin.universities.list() as any
    let list = (r.data || r || []) as University[]
    universities.value = list
    page.value = 1
  } catch(e:any) {
    toast.error(e?.message || 'Failed to load universities')
  }
}
onMounted(load)

async function createFrom(payload: any){
  const toast = useToast()
  creating.value = true
  createErrors.value = {}
  try{
    const data: any = { ...payload }
    if (data.acceptance_percent === '') delete data.acceptance_percent
    await api.admin.universities.create(data)
    toast.success('University created')
    openCreate.value = false
    await load()
  }catch(e:any){
    createErrors.value = e || { message: 'Failed to create university' }
  }finally{
    creating.value = false
  }
}

async function requestEdit(id: number){
  const toast = useToast()
  try {
    editErrors.value = {}
    const uni = await api.admin.universities.show(id) as any
    editingUniversity.value = uni
    openEdit.value = true
  } catch(e:any) {
    toast.error(e?.message || 'Failed to load university')
  }
}

async function updateFrom(payload: any){
  if (!editingUniversity.value) return
  const toast = useToast()
  editing.value = true
  editErrors.value = {}
  try{
    const data: any = { ...payload }
    if (data.acceptance_percent === '') delete data.acceptance_percent
    await api.admin.universities.update(editingUniversity.value.id, data)
    toast.success('University updated')
    openEdit.value = false
    editingUniversity.value = undefined
    await load()
  }catch(e:any){
    editErrors.value = e || { message: 'Failed to update university' }
  }finally{
    editing.value = false
  }
}

function requestDelete(id: number){
  confirmUniversityId.value = id
  confirmOpen.value = true
}

async function removeUniversity(){
  const id = confirmUniversityId.value
  if (id == null) { confirmOpen.value = false; return }
  const toast = useToast()
  try{ 
    await api.admin.universities.delete(id)
    toast.success('University deleted') 
  }catch(e:any){ 
    toast.error(e?.message || 'Failed to delete university') 
  }
  confirmOpen.value = false
  confirmUniversityId.value = null
  await load()
}
</script>


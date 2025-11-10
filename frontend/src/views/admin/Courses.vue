<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">Courses</h3>
      <Button size="sm" @click="$router.push('/admin/courses/new')">New Course</Button>
    </div>

    <Card>
      <template #header>
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="text-sm text-muted">Course list</div>
          <ListFilters v-model="filters" :schema="filterSchema" @change="load" />
        </div>
      </template>
      <CoursesTable :courses="paginatedCourses" @edit="requestEdit" @delete="requestDelete" />
      <Pagination
        :page="page"
        :per-page="perPage"
        :total="filteredCourses.length"
        @update:page="page = $event"
      />
    </Card>

    <ConfirmModal
      :open="confirmOpen"
      title="Delete course"
      :message="'This action cannot be undone.'"
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="removeCourse"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import CoursesTable from '@/components/admin/courses/CoursesTable.vue'
import ListFilters from '@/components/filters/ListFilters.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import Pagination from '@/components/ui/Pagination.vue'

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
type FilterDef = { key: string; type: 'search'|'select'; placeholder?: string; options?: { value: string; label: string }[] }

const courses = ref<Course[]>([])
const universities = ref<{ id: number; name: string }[]>([])
const filters = ref<{ q: string; university_id: string; min_accept: string; max_accept: string }>({ q: '', university_id: '', min_accept: '', max_accept: '' })
const filterSchema: FilterDef[] = [
  { key: 'q', type: 'search', placeholder: 'Search name' },
  { key: 'university_id', type: 'select', placeholder: 'All universities', options: [] },
  { key: 'min_accept', type: 'select', placeholder: 'Min acceptance %', options: [
    { value: '', label: 'Any %' },
    { value: '50', label: '50%+' },
    { value: '60', label: '60%+' },
    { value: '70', label: '70%+' },
    { value: '80', label: '80%+' },
    { value: '90', label: '90%+' }
  ] },
  { key: 'max_accept', type: 'select', placeholder: 'Max acceptance %', options: [
    { value: '', label: 'Any %' },
    { value: '60', label: '≤60%' },
    { value: '70', label: '≤70%' },
    { value: '80', label: '≤80%' },
    { value: '90', label: '≤90%' },
    { value: '100', label: '≤100%' }
  ] }
]
const confirmOpen = ref(false)
const confirmCourseId = ref<number|null>(null)
const page = ref(1)
const perPage = 10

const filteredCourses = computed(() => {
  let list = courses.value
  if (filters.value.q) {
    const needle = filters.value.q.toLowerCase()
    list = list.filter(c => (c.name || '').toLowerCase().includes(needle) || (c.code || '').toLowerCase().includes(needle))
  }
  return list
})

const paginatedCourses = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredCourses.value.slice(start, start + perPage)
})

watch(filteredCourses, () => {
  page.value = 1
})
const router = useRouter()

async function loadUniversities(){
  try {
    const r = await api.admin.universities.list() as any
    universities.value = (r.data || r || [])
    filterSchema[1].options = [
      { value: '', label: 'All universities' },
      ...universities.value.map(u => ({ value: String(u.id), label: u.name }))
    ]
  } catch(e) {
    console.error('Failed to load universities', e)
  }
}

async function load(){
  const toast = useToast()
  try {
    const universityId = filters.value.university_id ? Number(filters.value.university_id) : undefined
    const minAccept = filters.value.min_accept ? Number(filters.value.min_accept) : undefined
    const maxAccept = filters.value.max_accept ? Number(filters.value.max_accept) : undefined
    const r = await api.admin.courses.list(universityId, minAccept, maxAccept) as any
    courses.value = (r.data || r || []) as Course[]
    page.value = 1
  } catch(e:any) {
    toast.error(e?.message || 'Failed to load courses')
  }
}

onMounted(async () => {
  await loadUniversities()
  await load()
})

function requestEdit(id: number){
  router.push(`/admin/courses/${id}/edit`)
}

function requestDelete(id: number){
  confirmCourseId.value = id
  confirmOpen.value = true
}

async function removeCourse(){
  const id = confirmCourseId.value
  if (id == null) { confirmOpen.value = false; return }
  const toast = useToast()
  try{ 
    await api.admin.courses.delete(id)
    toast.success('Course deleted') 
  }catch(e:any){ 
    toast.error(e?.message || 'Failed to delete course') 
  }
  confirmOpen.value = false
  confirmCourseId.value = null
  await load()
}
</script>


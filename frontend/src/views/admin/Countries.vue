<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">Countries</h3>
      <Button size="sm" @click="openCreate=true">New Country</Button>
    </div>

    <Card>
      <template #header>
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="text-sm text-muted">Country list</div>
          <ListFilters v-model="filters" :schema="filterSchema" @change="load" />
        </div>
      </template>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-black/5">
              <th class="text-left p-3 text-sm font-medium text-muted">ID</th>
              <th class="text-left p-3 text-sm font-medium text-muted">Name</th>
              <th class="text-left p-3 text-sm font-medium text-muted">Code</th>
              <th class="text-left p-3 text-sm font-medium text-muted">Status</th>
              <th class="text-right p-3 text-sm font-medium text-muted">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" class="border-b border-black/5">
              <td colspan="5" class="p-8 text-center text-sm text-muted">Loading countries...</td>
            </tr>
            <tr v-else-if="filteredCountries.length === 0" class="border-b border-black/5">
              <td colspan="5" class="p-8 text-center text-sm text-muted">No countries found</td>
            </tr>
            <tr v-for="country in paginatedCountries" :key="country.id" class="border-b border-black/5 hover:bg-black/2 transition-colors">
              <td class="p-3 text-sm">{{ country.id }}</td>
              <td class="p-3 text-sm font-medium">{{ country.name }}</td>
              <td class="p-3 text-sm text-muted">{{ country.code || '—' }}</td>
              <td class="p-3">
                <span :class="['px-2 py-1 rounded text-xs font-medium', (country.is_active ?? true) ? 'bg-success/10 text-success' : 'bg-muted/10 text-muted']">
                  {{ (country.is_active ?? true) ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="p-3">
                <div class="flex items-center justify-end gap-2">
                  <button @click="requestEdit(country)" class="text-primary hover:text-primary/80 text-sm">Edit</button>
                  <button @click="requestDelete(country.id)" class="text-danger hover:text-danger/80 text-sm">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination
        :page="page"
        :per-page="perPage"
        :total="filteredCountries.length"
        @update:page="page = $event"
      />
    </Card>

    <Modal :open="openCreate" @close="openCreate=false">
      <template #header>Create Country</template>
      <div class="space-y-4">
        <Input v-model="createForm.name" label="Name" placeholder="Country name" :error="createErrors?.errors?.name?.[0]" />
        <Input v-model="createForm.code" label="Code" placeholder="Country code (e.g., MY)" :error="createErrors?.errors?.code?.[0]" />
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="createForm.is_active" class="rounded" />
          <span class="text-sm">Active</span>
        </label>
        <p v-if="createErrors?.message" class="text-xs text-danger">{{ createErrors.message }}</p>
        <div class="flex justify-end gap-2">
          <Button variant="ghost" @click="openCreate=false">Cancel</Button>
          <Button :disabled="creating" @click="createCountry">Create</Button>
        </div>
      </div>
    </Modal>

    <Modal :open="openEdit" @close="openEdit=false">
      <template #header>Edit Country</template>
      <div class="space-y-4">
        <Input v-model="editForm.name" label="Name" placeholder="Country name" :error="editErrors?.errors?.name?.[0]" />
        <Input v-model="editForm.code" label="Code" placeholder="Country code (e.g., MY)" :error="editErrors?.errors?.code?.[0]" />
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="editForm.is_active" class="rounded" />
          <span class="text-sm">Active</span>
        </label>
        <p v-if="editErrors?.message" class="text-xs text-danger">{{ editErrors.message }}</p>
        <div class="flex justify-end gap-2">
          <Button variant="ghost" @click="openEdit=false">Cancel</Button>
          <Button :disabled="editing" @click="updateCountry">Save</Button>
        </div>
      </div>
    </Modal>

    <ConfirmModal
      :open="confirmOpen"
      title="Delete Country"
      message="This action cannot be undone."
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="deleteCountry"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ListFilters from '@/components/filters/ListFilters.vue'
import { api } from '@/services/api'

type Country = { id: number; name: string; code?: string; is_active?: boolean }
type FilterDef = { key: string; type: 'search'|'select'; placeholder?: string; options?: { value: string; label: string }[] }

const countries = ref<Country[]>([])
const loading = ref(false)
const filters = ref<{ q: string }>({ q: '' })
const filterSchema: FilterDef[] = [
  { key: 'q', type: 'search', placeholder: 'Search name' }
]

const openCreate = ref(false)
const openEdit = ref(false)
const editing = ref(false)
const editErrors = ref<Record<string, any>>({})
const editingCountryId = ref<number | null>(null)
const creating = ref(false)
const createErrors = ref<Record<string, any>>({})
const confirmOpen = ref(false)
const confirmCountryId = ref<number | null>(null)

const createForm = ref({ name: '', code: '', is_active: true })
const editForm = ref({ name: '', code: '', is_active: true })
const page = ref(1)
const perPage = 10

const filteredCountries = computed(() => {
  let list = countries.value
  if (filters.value.q) {
    const needle = filters.value.q.toLowerCase()
    list = list.filter(c => (c.name || '').toLowerCase().includes(needle) || (c.code || '').toLowerCase().includes(needle))
  }
  return list
})

const paginatedCountries = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredCountries.value.slice(start, start + perPage)
})

watch(filteredCountries, () => {
  page.value = 1
})

async function load() {
  const toast = useToast()
  loading.value = true
  try {
    const r = await api.admin.countries.list() as any
    countries.value = (r.data || r || []) as Country[]
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load countries')
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function createCountry() {
  const toast = useToast()
  creating.value = true
  createErrors.value = {}
  try {
    const payload: any = { name: createForm.value.name }
    if (createForm.value.code) payload.code = createForm.value.code
    if (createForm.value.is_active !== undefined) payload.is_active = createForm.value.is_active
    
    await api.admin.countries.create(payload)
    toast.success('Country created')
    openCreate.value = false
    createForm.value = { name: '', code: '', is_active: true }
    await load()
  } catch (e: any) {
    createErrors.value = e || { message: 'Failed to create country' }
  } finally {
    creating.value = false
  }
}

async function requestEdit(country: Country) {
  const toast = useToast()
  try {
    const data = await api.admin.countries.show(country.id) as any
    editingCountryId.value = country.id
    editForm.value = {
      name: data.name || '',
      code: data.code || '',
      is_active: data.is_active !== undefined ? data.is_active : true
    }
    openEdit.value = true
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load country')
  }
}

async function updateCountry() {
  const toast = useToast()
  if (!editingCountryId.value) return
  editing.value = true
  editErrors.value = {}
  try {
    const payload: any = { name: editForm.value.name }
    if (editForm.value.code !== undefined) payload.code = editForm.value.code
    if (editForm.value.is_active !== undefined) payload.is_active = editForm.value.is_active
    
    await api.admin.countries.update(editingCountryId.value, payload)
    toast.success('Country updated')
    openEdit.value = false
    editingCountryId.value = null
    await load()
  } catch (e: any) {
    editErrors.value = e || { message: 'Failed to update country' }
  } finally {
    editing.value = false
  }
}

function requestDelete(id: number) {
  confirmCountryId.value = id
  confirmOpen.value = true
}

async function deleteCountry() {
  const toast = useToast()
  if (!confirmCountryId.value) return
  try {
    await api.admin.countries.delete(confirmCountryId.value)
    toast.success('Country deleted')
    confirmOpen.value = false
    confirmCountryId.value = null
    await load()
  } catch (e: any) {
    toast.error(e?.message || 'Failed to delete country')
  }
}
</script>




<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h4 class="text-lg font-semibold">Edit University</h4>
      <Input v-model="form.name" placeholder="University name (fallback)" :error="fieldError('name')" />
      <div>
        <label class="text-xs text-muted mb-1 block">Name (English) *</label>
        <Input v-model="form.i18n.name.en" placeholder="University name in English" :error="fieldError('i18n.name.en')" />
      </div>
      <div>
        <label class="text-xs text-muted mb-1 block">Name (Arabic)</label>
        <Input v-model="form.i18n.name.ar" placeholder="اسم الجامعة بالعربية" :error="fieldError('i18n.name.ar')" />
      </div>
      <div>
        <label class="text-xs text-muted mb-1 block">Location (English)</label>
        <Input v-model="form.i18n.location.en" placeholder="Location in English" :error="fieldError('i18n.location.en')" />
      </div>
      <div>
        <label class="text-xs text-muted mb-1 block">Location (Arabic)</label>
        <Input v-model="form.i18n.location.ar" placeholder="الموقع بالعربية" :error="fieldError('i18n.location.ar')" />
      </div>
      <div>
        <label class="text-xs text-muted mb-1 block">Country</label>
        <select v-model.number="form.country_id" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full" :class="fieldError('country_id') ? 'border-danger/60' : ''">
          <option :value="0">Select country</option>
          <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="fieldError('country_id')" class="mt-1 text-xs text-danger">{{ fieldError('country_id') }}</p>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.is_active" class="rounded border-black/20" />
        <span class="text-sm">Active</span>
      </label>
      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
      <div class="flex justify-end gap-2">
        <!-- Cancel button removed - modals cannot be closed via close button -->
        <Button :disabled="submitting" @click="handleSubmit">Save</Button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'

  const props = defineProps<{ 
    open: boolean
    university?: { id: number; name: string; country_id?: number; country?: { id: number; name: string }; is_active?: boolean }
    errors?: Record<string, any>
    submitting?: boolean 
  }>()

const emit = defineEmits(['close','submit'])

const countries = ref<{ id: number; name: string }[]>([])

const initialForm = () => ({
  name: '',
  country_id: 0,
  is_active: true,
  i18n: { name: { en: '', ar: '' }, location: { en: '', ar: '' } }
})

const form = ref(initialForm())

async function loadCountries(){
  try {
    const r = await api.admin.countries.list() as any
    countries.value = (r || [])
  } catch(e) {
    console.error('Failed to load countries', e)
  }
}

watch(() => props.university, (u) => {
  if (u) {
    form.value = {
      name: u.name || '',
      country_id: u.country_id || u.country?.id || 0,
      is_active: u.is_active ?? true,
      i18n: {
        name: {
          en: u.i18n?.name?.en || u.name || '',
          ar: u.i18n?.name?.ar || ''
        },
        location: {
          en: u.i18n?.location?.en || '',
          ar: u.i18n?.location?.ar || ''
        }
      }
    }
  } else {
    form.value = initialForm()
  }
}, { immediate: true })

onMounted(loadCountries)

async function handleSubmit(){
  const data: any = JSON.parse(JSON.stringify(form.value))
  if (data.country_id === 0) {
    delete data.country_id
  } else {
    data.country_id = Number(data.country_id)
  }
  const hasEnglishName = (data.i18n?.name?.en || '').trim().length > 0
  const hasArabicName = (data.i18n?.name?.ar || '').trim().length > 0
  if (!hasEnglishName && !hasArabicName) {
    data.name = data.name || 'University'
    delete data.i18n?.name
  } else if (data.i18n?.name) {
    if (!hasEnglishName) delete data.i18n.name.en
    if (!hasArabicName) delete data.i18n.name.ar
  }

  const hasLocationEn = (data.i18n?.location?.en || '').trim().length > 0
  const hasLocationAr = (data.i18n?.location?.ar || '').trim().length > 0
  if (!hasLocationEn && !hasLocationAr) {
    if (data.i18n) delete data.i18n.location
  } else if (data.i18n?.location) {
    if (!hasLocationEn) delete data.i18n.location.en
    if (!hasLocationAr) delete data.i18n.location.ar
  }

  if (data.i18n) {
    if (data.i18n.name && Object.keys(data.i18n.name).length === 0) delete data.i18n.name
    if (data.i18n.location && Object.keys(data.i18n.location).length === 0) delete data.i18n.location
    if (Object.keys(data.i18n).length === 0) delete data.i18n
  }

  emit('submit', data)
}

const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')

function fieldError(key: string){
  const e = (props.errors as any)?.errors?.[key] || (props.errors as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

watch(() => props.open, (isOpen) => {
  if (!isOpen) {
    form.value = initialForm()
  }
})
</script>

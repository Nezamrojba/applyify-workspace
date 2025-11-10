<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack max-h-[90vh] overflow-y-auto">
      <h4 class="text-lg font-semibold">Edit Course</h4>
      
      <div>
        <label class="text-xs text-muted mb-1 block">University</label>
        <select v-model.number="form.university_id" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full" :class="fieldError('university_id') ? 'border-danger/60' : ''" disabled>
          <option :value="form.university_id">{{ selectedUniversityName }}</option>
        </select>
      </div>

      <Input v-model="form.name" placeholder="Course name *" :error="fieldError('name')" />
      <Input v-model="form.code" placeholder="Course code" :error="fieldError('code')" />
      <Input v-model="form.level" placeholder="Level (e.g., Bachelor, Master)" :error="fieldError('level')" />
      <Input v-model.number="form.acceptance_percent" type="number" min="0" max="100" placeholder="Acceptance percentage (0-100)" :error="fieldError('acceptance_percent')" />
      <Input v-model.number="form.duration_months" type="number" min="1" max="120" placeholder="Duration (months)" :error="fieldError('duration_months')" />
      <Input v-model.number="form.total_years" type="number" min="1" max="10" placeholder="Total years" :error="fieldError('total_years')" />
      
      <div>
        <label class="text-xs text-muted mb-1 block">Details</label>
        <textarea v-model="form.details" rows="3" class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm" placeholder="Course description" :class="fieldError('details') ? 'border-danger/60' : ''"></textarea>
        <p v-if="fieldError('details')" class="mt-1 text-xs text-danger">{{ fieldError('details') }}</p>
      </div>

      <Input v-model.number="form.total_tuition_fees" type="number" min="0" step="0.01" placeholder="Total tuition fees" :error="fieldError('total_tuition_fees')" />
      <Input v-model.number="form.procedure_fees" type="number" min="0" step="0.01" placeholder="Procedure fees" :error="fieldError('procedure_fees')" />
      <Input v-model="form.payment_method" placeholder="Payment method" :error="fieldError('payment_method')" />
      
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.allow_installments" class="rounded border-black/20" />
        <span class="text-sm">Allow installments</span>
      </label>

      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.is_active" class="rounded border-black/20" />
        <span class="text-sm">Active</span>
      </label>

      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
      <div class="flex justify-end gap-2">
        <!-- Cancel button removed - modals cannot be closed via close button -->
        <Button :disabled="submitting" @click="$emit('submit', form)">Save</Button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{ 
  open: boolean
  course?: { 
    id: number
    university_id: number
    university?: { id: number; name: string }
    name: string
    code?: string
    level?: string
    acceptance_percent?: number
    duration_months?: number
    total_years?: number
    details?: string
    total_tuition_fees?: number
    procedure_fees?: number
    payment_method?: string
    allow_installments?: boolean
    is_active?: boolean
  }
  errors?: Record<string, any>
  submitting?: boolean 
}>()

defineEmits(['close','submit'])

const form = ref<{ 
  university_id: number
  name: string
  code: string
  level: string
  acceptance_percent: number | ''
  duration_months: number | ''
  total_years: number | ''
  details: string
  total_tuition_fees: number | ''
  procedure_fees: number | ''
  payment_method: string
  allow_installments: boolean
  is_active: boolean
}>({
  university_id: 0,
  name: '',
  code: '',
  level: '',
  duration_months: '',
  total_years: '',
  details: '',
  total_tuition_fees: '',
  procedure_fees: '',
  payment_method: '',
  allow_installments: false,
  is_active: true
})

watch(() => props.course, (c) => {
  if (c) {
    form.value = {
      university_id: c.university_id || 0,
      name: c.name || '',
      code: c.code || '',
      level: c.level || '',
      acceptance_percent: c.acceptance_percent ?? '',
      duration_months: c.duration_months ?? '',
      total_years: c.total_years ?? '',
      details: c.details || '',
      total_tuition_fees: c.total_tuition_fees ?? '',
      procedure_fees: c.procedure_fees ?? '',
      payment_method: c.payment_method || '',
      allow_installments: c.allow_installments ?? false,
      is_active: c.is_active ?? true
    }
  }
}, { immediate: true })

const selectedUniversityName = computed(() => props.course?.university?.name || 'Unknown')

const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')

function fieldError(key: string){
  const e = (props.errors as any)?.errors?.[key] || (props.errors as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}
</script>

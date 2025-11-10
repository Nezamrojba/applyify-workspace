<template>
  <div class="stack max-w-4xl">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-xl font-semibold">Edit Course</h3>
      <div class="flex items-center gap-2">
        <Button variant="ghost" size="sm" @click="$router.push(`/admin/courses/${route.params.id}/fee-structures`)">Manage Fee Structures</Button>
        <Button variant="ghost" @click="$router.push('/admin/courses')">Cancel</Button>
      </div>
    </div>

    <Card v-if="loading">
      <div class="text-center py-8 text-muted">Loading...</div>
    </Card>

    <Card v-else>
      <div class="stack">
        <div>
          <label class="text-sm font-medium mb-2 block">University</label>
          <select v-model.number="form.university_id" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full" disabled>
            <option :value="form.university_id">{{ selectedUniversityName }}</option>
          </select>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium mb-2 block">Course Name *</label>
            <Input v-model="form.name" placeholder="Course name" :error="fieldError('name')" />
          </div>
          <div>
            <label class="text-sm font-medium mb-2 block">Course Code</label>
            <Input v-model="form.code" placeholder="Course code" :error="fieldError('code')" />
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium mb-2 block">Level</label>
            <Input v-model="form.level" placeholder="Level (e.g., Bachelor, Master)" :error="fieldError('level')" />
          </div>
          <div>
            <label class="text-sm font-medium mb-2 block">Acceptance %</label>
            <Input v-model.number="form.acceptance_percent" type="number" min="0" max="100" placeholder="Acceptance percentage (0-100)" :error="fieldError('acceptance_percent')" />
          </div>
        </div>

        <div>
          <label class="text-sm font-medium mb-2 block">Duration (months)</label>
          <Input v-model.number="form.duration_months" type="number" min="1" max="120" placeholder="Duration in months" :error="fieldError('duration_months')" />
        </div>

        <div>
          <label class="text-sm font-medium mb-2 block">Total Years</label>
          <Input v-model.number="form.total_years" type="number" min="1" max="10" placeholder="Total years" :error="fieldError('total_years')" />
        </div>

        <div>
          <label class="text-sm font-medium mb-2 block">Course Details</label>
          <textarea v-model="form.details" rows="5" class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm" placeholder="Course description and details" :class="fieldError('details') ? 'border-danger/60' : ''"></textarea>
          <p v-if="fieldError('details')" class="mt-1 text-xs text-danger">{{ fieldError('details') }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium mb-2 block">Total Tuition Fees</label>
            <Input v-model.number="form.total_tuition_fees" type="number" min="0" step="0.01" placeholder="Total tuition fees" :error="fieldError('total_tuition_fees')" />
          </div>
          <div>
            <label class="text-sm font-medium mb-2 block">Procedure Fees</label>
            <Input v-model.number="form.procedure_fees" type="number" min="0" step="0.01" placeholder="Procedure fees" :error="fieldError('procedure_fees')" />
          </div>
        </div>

        <div>
          <label class="text-sm font-medium mb-2 block">Payment Method</label>
          <Input v-model="form.payment_method" placeholder="Payment method" :error="fieldError('payment_method')" />
        </div>

        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.allow_installments" class="rounded border-black/20" />
            <span class="text-sm">Allow installments</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_active" class="rounded border-black/20" />
            <span class="text-sm">Active</span>
          </label>
        </div>

        <p v-if="generalError" class="text-sm text-danger">{{ generalError }}</p>

        <div class="flex justify-end gap-2 pt-4 border-t border-black/5">
          <Button variant="ghost" @click="$router.push('/admin/courses')" :disabled="submitting">Cancel</Button>
          <Button :disabled="submitting" @click="handleSubmit">Save Changes</Button>
        </div>
      </div>
    </Card>

    <Card v-if="!loading && courseId" class="mt-6">
      <template #header>
        <div class="flex items-center justify-between">
          <div class="text-lg font-semibold">Fee Structures</div>
          <Button size="sm" @click="openNewFeeStructureModal">Add Fee Structure</Button>
        </div>
      </template>
      <FeeStructureManager ref="feeStructureManager" :course-id="courseId" @edit="handleFeeStructureEdit" />
    </Card>

    <FeeStructureModal 
      v-if="courseId"
      :open="openFeeStructureModal" 
      :course-id="courseId"
      :errors="feeStructureErrors"
      :submitting="feeStructureSubmitting"
      :fee-structure="editingFeeStructure"
      @close="closeFeeStructureModal"
      @save="handleFeeStructureSave"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import FeeStructureManager from '@/components/admin/courses/FeeStructureManager.vue'
import FeeStructureModal from '@/components/admin/courses/FeeStructureModal.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(true)
const submitting = ref(false)
const errors = ref<Record<string, any>>({})

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

const selectedUniversityName = ref('')
const courseId = computed(() => Number(route.params.id) || null)
const openFeeStructureModal = ref(false)
const feeStructureManager = ref<any>(null)
const feeStructureErrors = ref<Record<string, any>>({})
const feeStructureSubmitting = ref(false)
const editingFeeStructure = ref<any>(null)

async function loadCourse(){
  const id = Number(route.params.id)
  if (!id) {
    toast.error('Invalid course ID')
    router.push('/admin/courses')
    return
  }
  
  loading.value = true
  try {
    const course = await api.admin.courses.show(id) as any
    form.value = {
      university_id: course.university_id || 0,
      name: course.name || '',
      code: course.code || '',
      level: course.level || '',
      acceptance_percent: course.acceptance_percent ?? '',
      duration_months: course.duration_months ?? '',
      total_years: course.total_years ?? '',
      details: course.details || '',
      total_tuition_fees: course.total_tuition_fees ?? '',
      procedure_fees: course.procedure_fees ?? '',
      payment_method: course.payment_method || '',
      allow_installments: course.allow_installments ?? false,
      is_active: course.is_active ?? true
    }
    selectedUniversityName.value = course.university?.name || 'Unknown'
  } catch(e:any) {
    toast.error(e?.message || 'Failed to load course')
    router.push('/admin/courses')
  } finally {
    loading.value = false
  }
}

async function handleSubmit(){
  submitting.value = true
  errors.value = {}
  try{
    const id = Number(route.params.id)
    const data: any = { ...form.value }
    if (data.acceptance_percent === '') delete data.acceptance_percent
    if (data.duration_months === '') delete data.duration_months
    if (data.total_years === '') delete data.total_years
    if (data.total_tuition_fees === '') delete data.total_tuition_fees
    if (data.procedure_fees === '') delete data.procedure_fees
    await api.admin.courses.update(id, data)
    toast.success('Course updated')
    router.push('/admin/courses')
  }catch(e:any){
    errors.value = e || { message: 'Failed to update course' }
    toast.error(errors.value.message || 'Failed to update course')
  }finally{
    submitting.value = false
  }
}

onMounted(loadCourse)

const generalError = computed(() => (errors.value as any)?.message || (errors.value as any)?.error || '')

function fieldError(key: string){
  const e = (errors.value as any)?.errors?.[key] || (errors.value as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

function openNewFeeStructureModal() {
  editingFeeStructure.value = null
  feeStructureErrors.value = {}
  openFeeStructureModal.value = true
}

function handleFeeStructureEdit(fs: any) {
  editingFeeStructure.value = { ...fs }
  openFeeStructureModal.value = true
}

function closeFeeStructureModal() {
  openFeeStructureModal.value = false
}

watch(openFeeStructureModal, (isOpen) => {
  if (!isOpen) {
    editingFeeStructure.value = null
    feeStructureErrors.value = {}
  }
})

async function handleFeeStructureSave(payload: any) {
  if (!courseId.value) return
  feeStructureSubmitting.value = true
  feeStructureErrors.value = {}
  try {
    if (editingFeeStructure.value?.id) {
      await api.admin.feeStructures.update(courseId.value, editingFeeStructure.value.id, payload)
      toast.success('Fee structure updated')
    } else {
      await api.admin.feeStructures.create(courseId.value, payload)
      toast.success('Fee structure created')
    }
    closeFeeStructureModal()
    editingFeeStructure.value = null
    await feeStructureManager.value?.reload?.()
  } catch (e: any) {
    feeStructureErrors.value = e || { message: 'Failed to save fee structure' }
    toast.error(feeStructureErrors.value.message || 'Failed to save fee structure')
  } finally {
    feeStructureSubmitting.value = false
  }
}
</script>

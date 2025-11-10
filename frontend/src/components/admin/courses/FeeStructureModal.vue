<template>
  <Modal :open="open" size="xl" @close="$emit('close')">
    <template #header>
      {{ feeStructure?.id ? 'Edit Fee Structure' : 'Create Fee Structure' }}
    </template>
    
    <div class="space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto pr-2">
      <div class="grid md:grid-cols-2 gap-4">
        <Input
          v-model="form.program_code"
          label="Program Code"
          placeholder="e.g., KP/JPS (R3/0731/6/0001)10/28"
          :error="getFieldError('program_code')"
        />
        <Input
          v-model="form.intake"
          label="Intake"
          placeholder="e.g., OCT/NOV 2024"
          :error="getFieldError('intake')"
        />
      </div>

      <Input
        v-model.number="form.tuition_fee_per_credit_hour"
        type="number"
        step="0.01"
        min="0"
        label="Tuition Fee Per Credit Hour (RM) *"
        placeholder="e.g., 690"
        :error="getFieldError('tuition_fee_per_credit_hour') || (submitAttempted && (!form.tuition_fee_per_credit_hour || form.tuition_fee_per_credit_hour <= 0) ? 'Tuition fee per credit hour is required and must be greater than 0' : undefined)"
      />

      <div>
        <label class="text-sm font-medium mb-2 block">Semesters</label>
        <div class="space-y-3">
          <div v-for="(sem, idx) in form.semesters" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3 bg-white">
            <div class="flex items-center justify-between">
              <span class="font-medium text-sm">Semester {{ idx + 1 }}</span>
              <button @click="removeSemester(idx)" class="text-danger text-sm hover:underline">Remove</button>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <Input v-model="sem.semester_type" label="Type" placeholder="Sem / S Sem" />
              <Input v-model="sem.academic_period" label="Academic Period" placeholder="e.g., Oct-24" />
              <Input v-model.number="sem.credit_hours" type="number" label="Credit Hours" placeholder="15" />
              <Input v-model.number="sem.tuition_fee" type="number" step="0.01" label="Tuition Fee (RM)" />
              <Input v-model.number="sem.library_fee" type="number" step="0.01" label="Library Fee (RM)" />
              <Input v-model.number="sem.student_club_fee" type="number" step="0.01" label="Student Club Fee (RM)" />
              <Input v-model.number="sem.ikad_fee" type="number" step="0.01" label="IKAD Fee (RM)" />
              <Input v-model.number="sem.visa_processing_fee" type="number" step="0.01" label="Visa Processing Fee (RM)" />
              <Input v-model.number="sem.medical_insurance_fee" type="number" step="0.01" label="Medical Insurance Fee (RM)" />
              <Input v-model.number="sem.medical_examination_fee" type="number" step="0.01" label="Medical Examination Fee (RM)" />
            </div>
            <div class="border-t border-dashed border-black/10 pt-3 mt-3 space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Additional Fees</label>
                <button type="button" class="text-xs text-primary hover:underline" @click="addCustomFee(idx)">+ Add Fee</button>
              </div>
              <p v-if="!sem.custom_fees || sem.custom_fees.length === 0" class="text-xs text-muted bg-muted/10 rounded-lg px-3 py-2">
                No additional fees added for this semester.
              </p>
              <div v-else class="space-y-3">
                <div
                  v-for="(customFee, feeIdx) in sem.custom_fees"
                  :key="feeIdx"
                  class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,160px)_auto] items-end"
                >
                  <div class="sm:col-span-1">
                    <Input
                      v-model="customFee.name"
                      label="Fee Name"
                      placeholder="e.g., Application Fee"
                    />
                  </div>
                  <div>
                    <Input
                      v-model.number="customFee.amount"
                      type="number"
                      step="0.01"
                      min="0"
                      label="Amount (RM)"
                      placeholder="e.g., 500"
                    />
                  </div>
                  <div class="flex items-center sm:justify-end">
                    <button
                      type="button"
                      @click="removeCustomFee(idx, feeIdx)"
                      class="inline-flex items-center justify-center h-9 px-3 rounded-lg text-danger text-sm bg-danger/10 hover:bg-danger/20 transition-colors"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <Button variant="ghost" size="sm" @click="addSemester">+ Add Semester</Button>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium mb-2 block">One-Time Fees</label>
        <div class="space-y-3">
          <div v-for="(fee, idx) in form.one_time_fees" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-medium text-sm">Fee {{ idx + 1 }}</span>
              <button @click="removeOneTimeFee(idx)" class="text-danger text-sm hover:underline">Remove</button>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <Input
                v-model="fee.name"
                label="Fee Name"
                placeholder="e.g., International Student Fee"
              />
              <Input
                v-model.number="fee.amount"
                type="number"
                step="0.01"
                min="0"
                label="Amount (RM)"
                placeholder="e.g., 8000"
              />
              <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="fee.refundable" class="rounded" />
                  <span class="text-sm">Refundable</span>
                </label>
              </div>
            </div>
          </div>
          <Button variant="ghost" size="sm" @click="addOneTimeFee">+ Add One-Time Fee</Button>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium mb-2 block">Discounts</label>
        <div class="space-y-3">
          <div v-for="(discount, idx) in form.discounts" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-medium text-sm">Discount {{ idx + 1 }}</span>
              <button @click="removeDiscount(idx)" class="text-danger text-sm hover:underline">Remove</button>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <Input
                v-model="discount.description"
                label="Description"
                placeholder="e.g., 30% on Tuition Fee for War-Torn Countries"
              />
              <Input
                v-model.number="discount.percentage"
                type="number"
                step="0.01"
                min="0"
                max="100"
                label="Percentage (%)"
                placeholder="30"
              />
            </div>
          </div>
          <Button variant="ghost" size="sm" @click="addDiscount">+ Add Discount</Button>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium mb-2 block">Payment Methods</label>
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-muted mb-1 block">English</label>
            <textarea
              v-model="form.payment_methods.en"
              rows="6"
              class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
              placeholder="Describe payment methods in English..."
            ></textarea>
          </div>
          <div>
            <label class="text-xs text-muted mb-1 block">Arabic</label>
            <textarea
              v-model="form.payment_methods.ar"
              rows="6"
              class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
              placeholder="Describe payment methods in Arabic..."
            ></textarea>
          </div>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium mb-2 block">Policies & Notes</label>
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-muted mb-1 block">English Notes</label>
            <div class="space-y-2">
              <div v-for="(note, idx) in form.policies.en" :key="idx" class="flex gap-2 items-start">
                <Input v-model="form.policies.en[idx]" :placeholder="`Note ${idx + 1}`" class="flex-1" />
                <button @click="removePolicyNote('en', idx)" class="text-danger text-sm px-2 py-2">×</button>
              </div>
              <Button variant="ghost" size="sm" @click="addPolicyNote('en')">+ Add Note</Button>
            </div>
          </div>
          <div>
            <label class="text-xs text-muted mb-1 block">Arabic Notes</label>
            <div class="space-y-2">
              <div v-for="(note, idx) in form.policies.ar" :key="idx" class="flex gap-2 items-start">
                <Input v-model="form.policies.ar[idx]" :placeholder="`Note ${idx + 1}`" class="flex-1" />
                <button @click="removePolicyNote('ar', idx)" class="text-danger text-sm px-2 py-2">×</button>
              </div>
              <Button variant="ghost" size="sm" @click="addPolicyNote('ar')">+ Add Note</Button>
            </div>
          </div>
        </div>
      </div>

      <div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="rounded" />
          <span class="text-sm font-medium">Active (only one fee structure can be active per course)</span>
        </label>
      </div>

      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
    </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <!-- Cancel button removed - modals cannot be closed via close button -->
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-black/10 bg-primary text-white hover:bg-primary/90 h-10 px-4 text-sm"
            :disabled="submitting"
            @click="handleSubmit"
          >
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{
  open: boolean
  feeStructure?: any
  courseId: number
  errors?: Record<string, any>
  submitting?: boolean
}>()

const emit = defineEmits(['close', 'save'])

const submitAttempted = ref(false)


const form = ref({
  program_code: '',
  intake: '',
  tuition_fee_per_credit_hour: undefined as number | undefined,
  semesters: [] as any[],
  one_time_fees: [] as Array<{ name: string; amount: number; refundable: boolean }>,
  discounts: [] as Array<{ description: string; percentage: number }>,
  payment_methods: {
    en: '',
    ar: ''
  },
  policies: {
    en: [] as string[],
    ar: [] as string[]
  },
  i18n: {} as any,
  is_active: true
})

watch(() => props.feeStructure, (fs) => {
  if (fs) {
    const oneTimeFees = fs.one_time_fees
    let oneTimeFeesArray: Array<{ name: string; amount: number; refundable: boolean }> = []
    
    if (Array.isArray(oneTimeFees)) {
      oneTimeFeesArray = oneTimeFees
    } else if (oneTimeFees && typeof oneTimeFees === 'object') {
      if (oneTimeFees.international_student_fee) {
        oneTimeFeesArray.push({ name: 'International Student Fee', amount: Number(oneTimeFees.international_student_fee) || 0, refundable: false })
      }
      if (oneTimeFees.caution_fee) {
        oneTimeFeesArray.push({ name: 'Caution Fee', amount: Number(oneTimeFees.caution_fee) || 0, refundable: true })
      }
      Object.keys(oneTimeFees).forEach(key => {
        if (!['international_student_fee', 'caution_fee'].includes(key)) {
          oneTimeFeesArray.push({ 
            name: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            amount: Number(oneTimeFees[key]) || 0,
            refundable: false
          })
        }
      })
    }
    
    const discounts = fs.discounts
    let discountsArray: Array<{ description: string; percentage: number }> = []
    
    if (Array.isArray(discounts)) {
      discountsArray = discounts
    } else if (discounts && typeof discounts === 'object') {
      if (discounts.description || discounts.percentage) {
        discountsArray.push({
          description: discounts.description || '',
          percentage: Number(discounts.percentage) || 0
        })
      }
    }
    
    form.value = {
      program_code: fs.program_code || '',
      intake: fs.intake || '',
      tuition_fee_per_credit_hour: Number(fs.tuition_fee_per_credit_hour) || 0,
      semesters: Array.isArray(fs.semesters) ? fs.semesters.map((s: any) => ({
        ...s,
        custom_fees: s.custom_fees || []
      })) : [],
      one_time_fees: oneTimeFeesArray.length > 0 ? oneTimeFeesArray : [],
      discounts: discountsArray.length > 0 ? discountsArray : [],
      payment_methods: fs.payment_methods || { en: '', ar: '' },
      policies: fs.policies || { en: [], ar: [] },
      i18n: fs.i18n || {},
      is_active: fs.is_active !== undefined ? fs.is_active : true
    }
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.open, (isOpen) => {
  if (isOpen && props.feeStructure) {
    // Modal is opening with fee structure data - form will be populated by feeStructure watcher
    submitAttempted.value = false
  } else if (!isOpen) {
    resetForm()
    submitAttempted.value = false
  } else if (isOpen && !props.feeStructure) {
    // Modal is opening for new fee structure - reset form
    resetForm()
    submitAttempted.value = false
  }
})

function resetForm() {
  form.value = {
    program_code: '',
    intake: '',
    tuition_fee_per_credit_hour: undefined as number | undefined,
    semesters: [],
    one_time_fees: [],
    discounts: [],
    payment_methods: { en: '', ar: '' },
    policies: { en: [], ar: [] },
    i18n: {},
    is_active: true
  }
}

function addSemester() {
  form.value.semesters.push({
    semester_type: '',
    academic_period: '',
    credit_hours: 0,
    tuition_fee: 0,
    library_fee: 0,
    student_club_fee: 0,
    ikad_fee: 0,
    visa_processing_fee: 0,
    medical_insurance_fee: 0,
    medical_examination_fee: 0,
    custom_fees: []
  })
}

function removeSemester(idx: number) {
  form.value.semesters.splice(idx, 1)
}

function addCustomFee(semesterIdx: number) {
  if (!form.value.semesters[semesterIdx].custom_fees) {
    form.value.semesters[semesterIdx].custom_fees = []
  }
  form.value.semesters[semesterIdx].custom_fees.push({
    name: '',
    amount: 0
  })
}

function removeCustomFee(semesterIdx: number, feeIdx: number) {
  if (form.value.semesters[semesterIdx].custom_fees) {
    form.value.semesters[semesterIdx].custom_fees.splice(feeIdx, 1)
  }
}

function addOneTimeFee() {
  form.value.one_time_fees.push({
    name: '',
    amount: 0,
    refundable: false
  })
}

function removeOneTimeFee(idx: number) {
  form.value.one_time_fees.splice(idx, 1)
}

function addDiscount() {
  form.value.discounts.push({
    description: '',
    percentage: 0
  })
}

function removeDiscount(idx: number) {
  form.value.discounts.splice(idx, 1)
}

function addPolicyNote(lang: 'en' | 'ar') {
  form.value.policies[lang].push('')
}

function removePolicyNote(lang: 'en' | 'ar', idx: number) {
  form.value.policies[lang].splice(idx, 1)
}

function handleSubmit() {
  submitAttempted.value = true
  
  // Validate required field
  if (!form.value.tuition_fee_per_credit_hour || form.value.tuition_fee_per_credit_hour <= 0) {
    // Show error for missing tuition fee
    const errorMsg = 'Tuition fee per credit hour is required and must be greater than 0'
    emit('save', { error: errorMsg })
    return
  }

  const rawForm = JSON.parse(JSON.stringify(form.value))

  const cleanupCustomFees = (list: Array<{ name?: string | number; amount?: string | number }>) => {
    if (!Array.isArray(list)) return []
    return list
      .map((fee) => ({
        name: (fee.name || '').toString().trim(),
          amount: fee.amount !== undefined && fee.amount !== null && `${fee.amount}`.trim() !== ''
          ? Number(fee.amount)
          : null
      }))
      .filter((fee) => fee.name.length > 0 && fee.amount !== null)
  }

  const semesters = Array.isArray(rawForm.semesters)
    ? rawForm.semesters.map((sem: any) => {
        const customFees = cleanupCustomFees(sem.custom_fees || [])
        return {
          ...sem,
          custom_fees: customFees.length > 0 ? customFees : undefined
        }
      })
    : []

  const oneTimeFeeItems: Array<{ name?: string | number; amount?: string | number; refundable?: boolean }> =
    Array.isArray(rawForm.one_time_fees) ? rawForm.one_time_fees : []
  const oneTimeFees = oneTimeFeeItems
    .map((fee) => ({
      name: (fee.name || '').toString().trim(),
      amount: fee.amount !== undefined && fee.amount !== null && `${fee.amount}`.trim() !== ''
        ? Number(fee.amount)
        : null,
      refundable: Boolean(fee.refundable)
    }))
    .filter((fee) => fee.name.length > 0 && fee.amount !== null)

  const discountItems: Array<{ description?: string | number; percentage?: string | number }> =
    Array.isArray(rawForm.discounts) ? rawForm.discounts : []
  const discounts = discountItems
    .map((disc) => ({
      description: (disc.description || '').toString().trim(),
      percentage: disc.percentage !== undefined && disc.percentage !== null && `${disc.percentage}`.trim() !== ''
        ? Number(disc.percentage)
        : null
    }))
    .filter((disc) => disc.description.length > 0 || disc.percentage !== null)

  const paymentMethods = rawForm.payment_methods || { en: '', ar: '' }
  const sanitizedPaymentMethods = {
    en: (paymentMethods.en || '').toString().trim(),
    ar: (paymentMethods.ar || '').toString().trim()
  }
  const hasPaymentMethods = Boolean(sanitizedPaymentMethods.en || sanitizedPaymentMethods.ar)

  const policies = rawForm.policies || { en: [], ar: [] }
  const sanitizedPolicies = {
    en: Array.isArray(policies.en)
      ? policies.en.map((note: any) => (note || '').toString().trim()).filter((note: string) => note.length > 0)
      : [],
    ar: Array.isArray(policies.ar)
      ? policies.ar.map((note: any) => (note || '').toString().trim()).filter((note: string) => note.length > 0)
      : []
  }
  const hasPolicies = sanitizedPolicies.en.length > 0 || sanitizedPolicies.ar.length > 0

  const payload: Record<string, any> = {
    program_code: (rawForm.program_code || '').toString().trim() || null,
    intake: (rawForm.intake || '').toString().trim() || null,
    tuition_fee_per_credit_hour: Number(rawForm.tuition_fee_per_credit_hour),
    is_active: Boolean(rawForm.is_active)
  }

  if (semesters.length > 0) payload.semesters = semesters
  if (oneTimeFees.length > 0) payload.one_time_fees = oneTimeFees
  if (discounts.length > 0) payload.discounts = discounts
  if (hasPaymentMethods) payload.payment_methods = sanitizedPaymentMethods
  if (hasPolicies) payload.policies = sanitizedPolicies
  if (rawForm.i18n && Object.keys(rawForm.i18n).length > 0) payload.i18n = rawForm.i18n

  const normalizedPayload = JSON.parse(JSON.stringify(payload))
  emit('save', normalizedPayload)
}

const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')

function getFieldError(key: string) {
  const e = (props.errors as any)?.errors?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}
</script>


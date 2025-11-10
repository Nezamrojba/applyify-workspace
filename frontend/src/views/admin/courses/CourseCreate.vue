<template>
  <div class="stack max-w-4xl">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-xl font-semibold">Create Course</h3>
      <Button variant="ghost" @click="$router.push('/admin/courses')">Cancel</Button>
    </div>

    <Card>
      <div class="stack">
        <div>
          <label class="text-sm font-medium mb-2 block">University *</label>
          <select v-model.number="form.university_id" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full" :class="fieldError('university_id') ? 'border-danger/60' : ''">
            <option disabled :value="0">Select university</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
          <p v-if="fieldError('university_id')" class="mt-1 text-xs text-danger">{{ fieldError('university_id') }}</p>
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

        <div class="pt-6 border-t-2 border-black/20">
          <h4 class="text-lg font-semibold mb-4">Fee Structure</h4>
          
          <div class="space-y-6 bg-muted/5 rounded-lg p-6 border border-black/10">
            <div class="grid md:grid-cols-2 gap-4">
              <Input
                v-model="feeStructureForm.program_code"
                label="Program Code"
                placeholder="e.g., KP/JPS (R3/0731/6/0001)10/28"
                :error="getFeeStructureFieldError('program_code')"
              />
              <Input
                v-model="feeStructureForm.intake"
                label="Intake"
                placeholder="e.g., OCT/NOV 2024"
                :error="getFeeStructureFieldError('intake')"
              />
            </div>

            <Input
              v-model.number="feeStructureForm.tuition_fee_per_credit_hour"
              type="number"
              step="0.01"
              min="0"
              label="Tuition Fee Per Credit Hour (RM) *"
              placeholder="e.g., 690"
              :error="getFeeStructureFieldError('tuition_fee_per_credit_hour')"
            />

            <div>
              <label class="text-sm font-medium mb-2 block">Semesters</label>
              <div class="space-y-3">
                <div v-for="(sem, idx) in feeStructureForm.semesters" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3 bg-white">
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-sm">Semester {{ idx + 1 }}</span>
                    <button type="button" @click="removeSemester(idx)" class="text-danger text-sm hover:underline">Remove</button>
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
                      <label class="text-xs font-medium uppercase tracking-wide text-muted">Additional Fees</label>
                      <button
                        type="button"
                        class="text-xs text-primary hover:underline"
                        @click="addCustomFee(idx)"
                      >
                        + Add Fee
                      </button>
                    </div>
                    <p v-if="!sem.custom_fees || sem.custom_fees.length === 0" class="text-xs text-muted bg-muted/10 rounded-lg px-3 py-2">
                      No additional fees for this semester.
                    </p>
                    <div v-else class="space-y-3">
                      <div
                        v-for="(customFee, feeIdx) in sem.custom_fees"
                        :key="feeIdx"
                        class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,160px)_auto] items-end"
                      >
                        <Input
                          v-model="customFee.name"
                          label="Fee Name"
                          placeholder="e.g., Application Fee"
                        />
                        <Input
                          v-model.number="customFee.amount"
                          type="number"
                          step="0.01"
                          min="0"
                          label="Amount (RM)"
                          placeholder="e.g., 500"
                        />
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
                <Button type="button" variant="ghost" size="sm" @click="addSemester">+ Add Semester</Button>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium mb-2 block">One-Time Fees</label>
              <div class="space-y-3">
                <div v-for="(fee, idx) in feeStructureForm.one_time_fees" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3 bg-white">
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-sm">Fee {{ idx + 1 }}</span>
                    <button type="button" @click="removeOneTimeFee(idx)" class="text-danger text-sm hover:underline">Remove</button>
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
                <Button type="button" variant="ghost" size="sm" @click="addOneTimeFee">+ Add One-Time Fee</Button>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium mb-2 block">Discounts</label>
              <div class="space-y-3">
                <div v-for="(discount, idx) in feeStructureForm.discounts" :key="idx" class="border border-black/10 rounded-lg p-4 space-y-3 bg-white">
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-sm">Discount {{ idx + 1 }}</span>
                    <button type="button" @click="removeDiscount(idx)" class="text-danger text-sm hover:underline">Remove</button>
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
                <Button type="button" variant="ghost" size="sm" @click="addDiscount">+ Add Discount</Button>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium mb-2 block">Payment Methods</label>
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="text-xs text-muted mb-1 block">English</label>
                  <textarea
                    v-model="feeStructureForm.payment_methods.en"
                    rows="6"
                    class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
                    placeholder="Describe payment methods in English..."
                  ></textarea>
                </div>
                <div>
                  <label class="text-xs text-muted mb-1 block">Arabic</label>
                  <textarea
                    v-model="feeStructureForm.payment_methods.ar"
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
                    <div v-for="(note, idx) in feeStructureForm.policies.en" :key="idx" class="flex gap-2 items-start">
                      <Input v-model="feeStructureForm.policies.en[idx]" :placeholder="`Note ${idx + 1}`" class="flex-1" />
                      <button type="button" @click="removePolicyNote('en', idx)" class="text-danger text-sm px-2 py-2">×</button>
                    </div>
                    <Button type="button" variant="ghost" size="sm" @click="addPolicyNote('en')">+ Add Note</Button>
                  </div>
                </div>
                <div>
                  <label class="text-xs text-muted mb-1 block">Arabic Notes</label>
                  <div class="space-y-2">
                    <div v-for="(note, idx) in feeStructureForm.policies.ar" :key="idx" class="flex gap-2 items-start">
                      <Input v-model="feeStructureForm.policies.ar[idx]" :placeholder="`Note ${idx + 1}`" class="flex-1" />
                      <button type="button" @click="removePolicyNote('ar', idx)" class="text-danger text-sm px-2 py-2">×</button>
                    </div>
                    <Button type="button" variant="ghost" size="sm" @click="addPolicyNote('ar')">+ Add Note</Button>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" v-model="feeStructureForm.is_active" class="rounded" />
                <span class="text-sm font-medium">Active (only one fee structure can be active per course)</span>
              </label>
            </div>
          </div>
        </div>

        <p v-if="generalError" class="text-sm text-danger">{{ generalError }}</p>

        <div class="flex justify-end gap-2 pt-4 border-t border-black/5">
          <Button variant="ghost" @click="$router.push('/admin/courses')" :disabled="submitting">Cancel</Button>
          <Button :disabled="submitting" @click="handleSubmit">Create Course</Button>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'

const router = useRouter()
const toast = useToast()

const universities = ref<{ id: number; name: string }[]>([])
const submitting = ref(false)
const errors = ref<Record<string, any>>({})
const feeStructureErrors = ref<Record<string, any>>({})

const form = ref<{ 
  university_id: number
  name: string
  code: string
  level: string
  acceptance_percent: number | ''
  duration_months: number | ''
  total_years: number | ''
  details: string
  allow_installments: boolean
  is_active: boolean
}>({
  university_id: 0,
  name: '',
  code: '',
  level: '',
  acceptance_percent: '',
  duration_months: '',
  total_years: '',
  details: '',
  allow_installments: false,
  is_active: true
})

const feeStructureForm = ref({
  program_code: '',
  intake: '',
  tuition_fee_per_credit_hour: 0,
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
  is_active: true
})

async function loadUniversities(){
  try {
    const r = await api.admin.universities.list() as any
    universities.value = (r.data || r || [])
  } catch(e) {
    toast.error('Failed to load universities')
  }
}

function addSemester() {
  feeStructureForm.value.semesters.push({
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
  feeStructureForm.value.semesters.splice(idx, 1)
}

function addCustomFee(semesterIdx: number) {
  if (!feeStructureForm.value.semesters[semesterIdx].custom_fees) {
    feeStructureForm.value.semesters[semesterIdx].custom_fees = []
  }
  feeStructureForm.value.semesters[semesterIdx].custom_fees.push({
    name: '',
    amount: 0
  })
}

function removeCustomFee(semesterIdx: number, feeIdx: number) {
  if (feeStructureForm.value.semesters[semesterIdx].custom_fees) {
    feeStructureForm.value.semesters[semesterIdx].custom_fees.splice(feeIdx, 1)
  }
}

function addOneTimeFee() {
  feeStructureForm.value.one_time_fees.push({
    name: '',
    amount: 0,
    refundable: false
  })
}

function removeOneTimeFee(idx: number) {
  feeStructureForm.value.one_time_fees.splice(idx, 1)
}

function addDiscount() {
  feeStructureForm.value.discounts.push({
    description: '',
    percentage: 0
  })
}

function removeDiscount(idx: number) {
  feeStructureForm.value.discounts.splice(idx, 1)
}

function addPolicyNote(lang: 'en' | 'ar') {
  feeStructureForm.value.policies[lang].push('')
}

function removePolicyNote(lang: 'en' | 'ar', idx: number) {
  feeStructureForm.value.policies[lang].splice(idx, 1)
}

function getFeeStructureFieldError(key: string) {
  const e = feeStructureErrors.value?.errors?.[key] || feeStructureErrors.value?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

async function handleSubmit(){
  submitting.value = true
  errors.value = {}
  feeStructureErrors.value = {}
  try{
    const data: any = { ...form.value }
    if (data.acceptance_percent === '') delete data.acceptance_percent
    if (data.duration_months === '') delete data.duration_months
    if (data.total_years === '') delete data.total_years
    
    const course = await api.admin.courses.create(data) as any
    toast.success('Course created')
    
    try {
      const courseId = course.id || course.data?.id || (course as any).data?.id
      if (!courseId) {
        throw new Error('Failed to get course ID')
      }
      
      const feeStructureData: any = {
        program_code: feeStructureForm.value.program_code || null,
        intake: feeStructureForm.value.intake || null,
        tuition_fee_per_credit_hour: feeStructureForm.value.tuition_fee_per_credit_hour,
        semesters: feeStructureForm.value.semesters.length > 0 ? feeStructureForm.value.semesters.map((sem: any) => {
          const semData: any = { ...sem }
          if (sem.custom_fees && sem.custom_fees.length > 0) {
            semData.custom_fees = sem.custom_fees.filter((f: any) => f.name && f.amount)
          } else {
            delete semData.custom_fees
          }
          return semData
        }) : null,
        one_time_fees: feeStructureForm.value.one_time_fees.length > 0 ? feeStructureForm.value.one_time_fees.filter((f: any) => f.name && f.amount) : null,
        discounts: feeStructureForm.value.discounts.length > 0 ? feeStructureForm.value.discounts.filter((d: any) => d.description || d.percentage) : null,
        payment_methods: feeStructureForm.value.payment_methods.en || feeStructureForm.value.payment_methods.ar ? {
          en: feeStructureForm.value.payment_methods.en || null,
          ar: feeStructureForm.value.payment_methods.ar || null
        } : null,
        policies: feeStructureForm.value.policies.en.filter((n: string) => n.trim()).length > 0 || feeStructureForm.value.policies.ar.filter((n: string) => n.trim()).length > 0 ? {
          en: feeStructureForm.value.policies.en.filter((n: string) => n.trim()),
          ar: feeStructureForm.value.policies.ar.filter((n: string) => n.trim())
        } : null,
        is_active: feeStructureForm.value.is_active
      }
      
      await api.admin.feeStructures.create(courseId, feeStructureData)
      toast.success('Fee structure created')
    } catch (e: any) {
      feeStructureErrors.value = e || { message: 'Failed to create fee structure' }
      toast.error(feeStructureErrors.value.message || 'Failed to create fee structure')
      return
    }
    
    router.push('/admin/courses')
  }catch(e:any){
    errors.value = e || { message: 'Failed to create course' }
    toast.error(errors.value.message || 'Failed to create course')
  }finally{
    submitting.value = false
  }
}

onMounted(loadUniversities)

const generalError = computed(() => (errors.value as any)?.message || (errors.value as any)?.error || '')

function fieldError(key: string){
  const e = (errors.value as any)?.errors?.[key] || (errors.value as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}
</script>

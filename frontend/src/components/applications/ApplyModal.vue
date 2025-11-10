<template>
  <Modal :open="open" @close="handleClose" size="lg">
    <div class="space-y-6 max-h-[90vh] overflow-y-auto">
      <!-- Step 0: University Selection (only if no university provided) -->
      <div v-if="currentStep === 0" class="space-y-4">
        <div>
          <h3 class="text-xl font-semibold mb-1">{{ t('applyModal.chooseUniversity') }}</h3>
          <div class="text-xs text-muted">{{ t('applyModal.stepUniversity', { current: 1, total: 3 }) }}</div>
        </div>
        <select v-model.number="selectedUniversityId" class="h-10 border rounded-lg px-3 bg-white w-full" :class="(universityTouched && !selectedUniversityId) ? 'border-danger/60' : 'border-black/10'" @blur="universityTouched = true" @change="onUniversitySelected">
          <option disabled value="">{{ t('applyModal.selectUniversity') }}</option>
          <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
        <p v-if="universityTouched && !selectedUniversityId" class="text-xs text-danger">{{ t('applyModal.selectUniversityError') }}</p>
        <Button class="w-full" :disabled="!selectedUniversityId" @click="goToCourseSelection">{{ t('common.continue') }}</Button>
      </div>

      <!-- Step 1: Course Selection -->
      <div v-if="currentStep === 1" class="space-y-4">
        <div>
          <h3 class="text-xl font-semibold mb-1">{{$t('universities.apply')}} — {{ (selectedUniversity || props.university)?.name || 'Select University' }}</h3>
          <div class="text-xs text-muted">
            {{ needsUniversitySelection ? t('applyModal.stepCourse', { current: 2, total: 3 }) : t('applyModal.stepCourse', { current: 1, total: 2 }) }}
          </div>
        </div>
        <select v-model.number="courseId" class="h-10 border rounded-lg px-3 bg-white w-full" :class="(showCourseError ? courseError : undefined) ? 'border-danger/60' : 'border-black/10'" @blur="courseTouched = true">
          <option disabled value="">{{ t('applyModal.selectCourse') }}</option>
          <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="showCourseError && courseError" class="text-xs text-danger">{{ courseError }}</p>
        <Input 
          v-model="passport" 
          :placeholder="$t('fields.passport') as string" 
          :error="(passportTouched || submitted) ? passportError : undefined" 
          @blur="passportTouched = true"
          :disabled="isLoggedIn"
        />
        <p v-if="isLoggedIn" class="text-xs text-muted">{{ t('applyModal.usingAccountPassport') }}</p>
        
        <!-- Application Limit Error Message -->
        <div v-if="limitError" class="p-3 rounded-lg bg-danger/5 border border-danger/20">
          <div class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-danger mt-0.5 flex-shrink-0">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <div class="text-sm font-medium text-danger">{{ t('applyModal.limitTitle') }}</div>
              <div class="text-xs text-danger/80 mt-1">{{ limitError }}</div>
            </div>
          </div>
        </div>
        
        <div class="flex gap-2">
          <Button v-if="needsUniversitySelection" variant="ghost" class="flex-1" @click="currentStep = 0">← {{ t('common.back') }}</Button>
          <Button 
            class="w-full" 
            :class="needsUniversitySelection ? 'flex-1' : ''" 
            :disabled="invalid || checkingLimit || !!limitError" 
            @click="goToPaymentStep"
          >
            {{ checkingLimit ? t('common.checking') : t('common.continueToPayment') }}
          </Button>
        </div>
      </div>

      <!-- Step 2: Payment -->
      <div v-if="currentStep === 2" class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-semibold mb-1">{{ t('applyModal.paymentStepTitle') }}</h3>
            <div class="text-xs text-muted">
              {{ needsUniversitySelection ? t('applyModal.paymentStepIndicator', { current: 3, total: 3 }) : t('applyModal.paymentStepIndicator', { current: 2, total: 2 }) }}
            </div>
          </div>
          <button @click="currentStep = 1" class="text-sm text-primary hover:underline">← {{ t('common.back') }}</button>
        </div>
        <PaymentStep :passport-no="passport" @receipt-uploaded="handleReceiptUploaded" />
        <div v-if="!receiptUrl" class="p-4 rounded-lg bg-warning/5 border border-warning/20">
          <div class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-warning mt-0.5 flex-shrink-0">
              <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <div class="text-sm font-medium text-warning">{{ t('applyModal.paymentReceiptRequiredTitle') }}</div>
              <div class="text-xs text-muted mt-1">{{ t('applyModal.paymentReceiptRequiredDescription') }}</div>
            </div>
          </div>
        </div>
        <div class="flex gap-3 pt-4 border-t border-black/10">
          <Button variant="ghost" class="flex-1" @click="currentStep = 1">{{ t('common.back') }}</Button>
          <Button class="flex-1" :disabled="!receiptUrl || submitting" @click="submit">
            {{ submitting ? t('applyModal.creatingApplication') : $t('applications.create') }}
          </Button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import PaymentStep from './PaymentStep.vue'
import { api } from '@/services/api'
import { useUniversityStore } from '@/stores/universities'
import { useAuthStore } from '@/stores/auth'

type Uni = { id: number; name: string }
const props = defineProps<{ open: boolean; university?: Uni; defaultCourseId?: number }>()
const emit = defineEmits(['close','created'])
const universities = ref<{id:number; name:string}[]>([])
const selectedUniversityId = ref<number|''>('')
const selectedUniversity = ref<Uni | null>(null)
const universityTouched = ref(false)
const courses = ref<{id:number; name:string}[]>([])
const courseId = ref<number|''>('')
const passport = ref('')
const courseError = ref<string|undefined>()
const passportError = ref<string|undefined>()
const { t } = useI18n()
const submitted = ref(false)
const submitting = ref(false)
const courseTouched = ref(false)
const passportTouched = ref(false)
const currentStep = ref(0)
const receiptUrl = ref<string | null>(null)
const toast = useToast()
const uniStore = useUniversityStore()
const auth = useAuthStore()

const needsUniversitySelection = computed(() => !props.university)
const isLoggedIn = computed(() => !!auth.user && !!auth.token)
const userPassport = computed(() => auth.user?.passport_no || '')
const checkingLimit = ref(false)
const limitError = ref<string | null>(null)

async function loadUniversities(){
  await uniStore.fetch()
  universities.value = (uniStore.items || []).map(u => ({ id: u.id, name: u.name }))
}

async function onUniversitySelected() {
  if (selectedUniversityId.value) {
    const uni = universities.value.find(u => u.id === selectedUniversityId.value)
    if (uni) {
      selectedUniversity.value = uni
      await loadCourses(Number(selectedUniversityId.value))
    }
  }
}

function goToCourseSelection() {
  if (!selectedUniversityId.value) return
  currentStep.value = 1
}

async function goToPaymentStep() {
  validate()
  submitted.value = true
  const universityId = selectedUniversity.value?.id || props.university?.id
  // For logged-in users, use their passport from account; otherwise use the form field
  const passportToUse = isLoggedIn.value ? userPassport.value : passport.value
  if (!universityId || !passportToUse || invalid.value) {
    toast.error(t('toasts.fieldsRequired') as string)
    return
  }
  
  // Check application limit for logged-in students before proceeding to payment
  if (isLoggedIn.value && auth.user?.role === 'student') {
    checkingLimit.value = true
    limitError.value = null
    try {
      const limitCheck = await api.applications.checkLimit()
      if (limitCheck.limit_reached || !limitCheck.can_create) {
        limitError.value = t('applyModal.limitMessage', { current: limitCheck.active_count, max: limitCheck.max_allowed }) as string
        toast.error(limitError.value)
        checkingLimit.value = false
        return
      }
    } catch (e: any) {
      // If check fails, log but don't block (let backend handle it)
      console.error('Failed to check application limit:', e)
      // Continue to payment step - backend will validate on submission
    } finally {
      checkingLimit.value = false
    }
  }
  
  currentStep.value = 2
}

async function loadCourses(uid?: number){
  if (!uid) { courses.value = []; return }
  const r = await api.courses.list(uid) as any
  courses.value = r.data || r
}

onMounted(async () => {
  await loadUniversities()
  
  // Initialize based on props when component mounts
  if (props.university && props.open) {
    selectedUniversity.value = props.university
    selectedUniversityId.value = props.university.id
    await loadCourses(props.university.id)
    currentStep.value = 1
  } else if (!props.university && props.open) {
    currentStep.value = 0
  }
  
  if (props.defaultCourseId) courseId.value = props.defaultCourseId
  // Pre-fill via global event dispatched from Landing (when arriving from course detail)
  window.addEventListener('prefill-course', (e: any) => {
    if (e?.detail?.courseId) courseId.value = e.detail.courseId
  }, { once: true })
})

function handleReceiptUploaded(url: string) {
  receiptUrl.value = url
}

async function submit(){
  // Validate that payment receipt is uploaded
  if (!receiptUrl.value) {
    toast.error(t('applyModal.paymentReceiptRequiredTitle') as string)
    return
  }
  
  // Get the university ID (either from selected or prop)
  const universityId = selectedUniversity.value?.id || props.university?.id
  
  // For logged-in users, use their passport from account; otherwise use the form field
  const passportToUse = isLoggedIn.value ? userPassport.value : passport.value
  
  // Validate all required fields
  if (!universityId || !courseId.value || !passportToUse) {
    toast.error(t('toasts.fieldsRequired') as string)
    return
  }
  
  submitting.value = true
  try {
    emit('created', { 
      university_id: universityId, 
      course_id: courseId.value, 
      passport_no: passportToUse,
      payment_receipt_url: receiptUrl.value
    })
  } catch (e: any) {
    toast.error(e?.message || (t('toasts.failedCreateApplication') as string))
  } finally {
    submitting.value = false
  }
}

function handleClose() {
  // Prevent closing if payment receipt is uploaded but application not yet submitted
  // Allow closing if on step 1 or if no receipt uploaded yet
  if (currentStep.value === 2 && receiptUrl.value && !submitting.value) {
    // User uploaded receipt but hasn't submitted - warn them
    if (confirm(t('common.confirmCloseReceipt') as string)) {
      emit('close')
    }
  } else {
    emit('close')
  }
}

watch(() => props.open, (newVal) => {
  if (newVal) {
    // Reset to appropriate step based on whether university is provided
    // IMPORTANT: Always use props.university directly (fresh data) and create new object reference
    if (props.university) {
      currentStep.value = 1
      // Create new object reference to ensure Vue reactivity detects the change
      selectedUniversity.value = { id: props.university.id, name: props.university.name }
      selectedUniversityId.value = props.university.id
      loadCourses(props.university.id)
    } else {
      currentStep.value = 0
      selectedUniversity.value = null
      selectedUniversityId.value = ''
      courses.value = []
    }
    // Reset all form state when modal opens
    receiptUrl.value = null
    submitted.value = false
    submitting.value = false
    courseId.value = props.defaultCourseId || ''
    // Pre-fill passport from user account if logged in, otherwise clear it
    passport.value = isLoggedIn.value ? (userPassport.value || '') : ''
    courseTouched.value = false
    passportTouched.value = false
    universityTouched.value = false
    courseError.value = undefined
    passportError.value = undefined
    limitError.value = null
  } else {
    // When modal closes, clear state to prevent stale data
    selectedUniversity.value = null
    selectedUniversityId.value = ''
    courseId.value = ''
    passport.value = ''
    receiptUrl.value = null
    courses.value = []
  }
})

// Watch for university prop changes to update the selected university immediately
watch(() => props.university, (newUni, oldUni) => {
  // Only update if university actually changed (compare IDs to avoid unnecessary updates)
  if (newUni && newUni.id !== (oldUni?.id ?? null)) {
    // Create a new object reference to ensure reactivity
    selectedUniversity.value = { id: newUni.id, name: newUni.name }
    selectedUniversityId.value = newUni.id
    // Load courses if modal is open
    if (props.open) {
      loadCourses(newUni.id)
      // Reset form fields when university changes
      courseId.value = ''
      // Reset passport to user's passport if logged in, otherwise clear
      passport.value = isLoggedIn.value ? (userPassport.value || '') : ''
      receiptUrl.value = null
      courseTouched.value = false
      passportTouched.value = false
      submitted.value = false
      // If university changes, reset to course selection step
      if (currentStep.value === 0 || currentStep.value === 2) {
        currentStep.value = 1
      }
    }
  } else if (!newUni && oldUni && props.open) {
    // If university is cleared while modal is open, reset to university selection step
    selectedUniversity.value = null
    selectedUniversityId.value = ''
    courses.value = []
    courseId.value = ''
    // Reset passport to user's passport if logged in, otherwise clear
    passport.value = isLoggedIn.value ? (userPassport.value || '') : ''
    receiptUrl.value = null
    currentStep.value = 0
  }
}, { immediate: true })

function validate(){
  courseError.value = !courseId.value ? (t('errors.selectCourse') as string) : undefined
  // If logged in, passport should come from user account, so validation should check userPassport
  // If not logged in, require manual entry
  if (isLoggedIn.value) {
    passportError.value = !userPassport.value ? (t('applyModal.updatePassport') as string) : undefined
  } else {
    passportError.value = !passport.value ? (t('errors.required') as string) : undefined
  }
}

// Watch for changes in course, passport, and user passport (for logged-in users)
watch([courseId, passport, userPassport], validate, { immediate: true })
// Also watch auth.user to update passport when user data changes
watch(() => auth.user, (newUser) => {
  if (newUser && isLoggedIn.value && props.open) {
    // Update passport field when user data is loaded/updated
    passport.value = newUser.passport_no || ''
  }
}, { deep: true })

const invalid = computed(() => {
  // If logged in, check userPassport; otherwise check passport field
  const passportValid = isLoggedIn.value ? !!userPassport.value : !!passport.value
  return !!courseError.value || !passportValid || !!passportError.value
})
const showCourseError = computed(() => courseTouched.value || submitted.value)
</script>

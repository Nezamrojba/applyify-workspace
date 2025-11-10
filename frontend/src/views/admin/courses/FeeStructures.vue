<template>
  <div class="stack max-w-7xl">
    <div class="flex items-center justify-between gap-3">
      <div>
        <button @click="$router.push('/admin/courses')" class="text-muted hover:text-text transition-colors mb-2">
          ← Back to Courses
        </button>
        <h3 class="text-xl font-semibold">Fee Structures - {{ course?.name || 'Loading...' }}</h3>
      </div>
      <Button size="sm" @click="openCreate=true">New Fee Structure</Button>
    </div>

    <Card v-if="loading">
      <div class="text-center py-8 text-muted">Loading...</div>
    </Card>

    <div v-else class="space-y-4">
      <div v-if="feeStructures.length === 0" class="text-center py-12 text-muted">
        No fee structures yet. Create one to get started.
      </div>

      <Card v-for="fs in feeStructures" :key="fs.id" class="relative">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h4 class="font-semibold">{{ fs.program_code || 'Fee Structure' }}</h4>
              <span v-if="fs.is_active" class="px-2 py-1 rounded text-xs font-medium bg-success/10 text-success">Active</span>
              <span v-else class="px-2 py-1 rounded text-xs font-medium bg-muted/10 text-muted">Inactive</span>
            </div>
            <div class="text-sm text-muted space-y-1">
              <div v-if="fs.intake">Intake: {{ fs.intake }}</div>
              <div>Tuition per Credit Hour: RM {{ Number(fs.tuition_fee_per_credit_hour).toLocaleString() }}</div>
              <div v-if="fs.semesters && fs.semesters.length > 0">
                Semesters: {{ fs.semesters.length }} semester(s)
              </div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="ghost" size="sm" @click="requestEdit(fs)">Edit</Button>
            <Button variant="ghost" size="sm" @click="requestDelete(fs.id)" class="text-danger">Delete</Button>
          </div>
        </div>
      </Card>
    </div>

    <FeeStructureModal
      :open="openCreate || openEdit"
      :fee-structure="editingFeeStructure"
      :course-id="courseId"
      :errors="modalErrors"
      :submitting="submitting"
      @close="closeModal"
      @save="handleSubmit"
    />

    <ConfirmModal
      :open="confirmOpen"
      title="Delete Fee Structure"
      message="This action cannot be undone."
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen=false"
      @confirm="deleteFeeStructure"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import FeeStructureModal from '@/components/admin/courses/FeeStructureModal.vue'
import { api } from '@/services/api'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const courseId = Number(route.params.id)
const course = ref<any>(null)
const feeStructures = ref<any[]>([])
const loading = ref(true)
const openCreate = ref(false)
const openEdit = ref(false)
const editingFeeStructure = ref<any>(null)
const submitting = ref(false)
const modalErrors = ref<Record<string, any>>({})
const confirmOpen = ref(false)
const confirmFeeStructureId = ref<number | null>(null)

async function loadCourse() {
  try {
    course.value = await api.admin.courses.show(courseId)
  } catch (e: any) {
    toast.error('Failed to load course')
  }
}

async function loadFeeStructures() {
  loading.value = true
  try {
    feeStructures.value = await api.admin.feeStructures.list(courseId) as any
  } catch (e: any) {
    toast.error('Failed to load fee structures')
  } finally {
    loading.value = false
  }
}

function requestEdit(fs: any) {
  editingFeeStructure.value = { ...fs }
  openEdit.value = true
}

function requestDelete(id: number) {
  confirmFeeStructureId.value = id
  confirmOpen.value = true
}

async function deleteFeeStructure() {
  if (!confirmFeeStructureId.value) return
  try {
    await api.admin.feeStructures.delete(courseId, confirmFeeStructureId.value)
    toast.success('Fee structure deleted')
    confirmOpen.value = false
    confirmFeeStructureId.value = null
    await loadFeeStructures()
  } catch (e: any) {
    toast.error(e?.message || 'Failed to delete fee structure')
  }
}

async function handleSubmit(payload: any) {
  // Check if payload contains an error (from client-side validation)
  if (payload?.error) {
    toast.error(payload.error)
    modalErrors.value = { message: payload.error }
    return
  }
  
  submitting.value = true
  modalErrors.value = {}
  
  try {
    if (editingFeeStructure.value?.id) {
      await api.admin.feeStructures.update(courseId, editingFeeStructure.value.id, payload)
      toast.success('Fee structure updated')
    } else {
      await api.admin.feeStructures.create(courseId, payload)
      toast.success('Fee structure created')
    }
    // Close modal and reload data
    closeModal()
    await loadFeeStructures()
  } catch (e: any) {
    // Handle different error response formats
    let errorData: any = {}
    if (e?.response?.data) {
      errorData = e.response.data
    } else if (e?.data) {
      errorData = e.data
    } else if (e?.response) {
      errorData = e.response
    } else if (typeof e === 'object') {
      errorData = e
    } else {
      errorData = { message: String(e) }
    }
    
    modalErrors.value = errorData
    
    // Extract error message from various possible locations
    const errorMessage = 
      errorData?.message || 
      errorData?.error || 
      (Array.isArray(errorData?.errors) ? errorData.errors[0] : errorData?.errors) ||
      (errorData?.errors && typeof errorData.errors === 'object' ? Object.values(errorData.errors)[0] : null) ||
      e?.message ||
      'Failed to save fee structure'
    toast.error(String(errorMessage))
  } finally {
    submitting.value = false
  }
}

function closeModal() {
  openCreate.value = false
  openEdit.value = false
  editingFeeStructure.value = null
  modalErrors.value = {}
}

onMounted(async () => {
  await Promise.all([loadCourse(), loadFeeStructures()])
})
</script>




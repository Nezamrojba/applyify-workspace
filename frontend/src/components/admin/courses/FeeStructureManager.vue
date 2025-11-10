<template>
  <div class="space-y-4">
    <div v-if="loading" class="text-center py-8 text-sm text-muted">Loading fee structures...</div>
    <div v-else-if="feeStructures.length === 0" class="text-center py-8 text-sm text-muted">No fee structures added yet</div>
    <div v-else class="space-y-3">
      <div 
        v-for="fs in feeStructures" 
        :key="fs.id" 
        class="p-4 rounded-lg border border-black/5 hover:border-primary/20 transition-colors"
        :class="fs.is_active ? 'bg-primary/5' : ''"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span class="font-medium">{{ fs.intake || 'No Intake' }}</span>
              <span v-if="fs.program_code" class="text-sm text-muted">({{ fs.program_code }})</span>
              <span v-if="fs.is_active" class="px-2 py-0.5 rounded text-xs font-medium bg-success/10 text-success">Active</span>
            </div>
            <div class="text-sm text-muted">
              Tuition per credit: RM {{ (fs.tuition_fee_per_credit_hour || 0).toLocaleString() }}
            </div>
            <div v-if="fs.semesters && fs.semesters.length > 0" class="text-sm text-muted mt-1">
              {{ fs.semesters.length }} semester(s)
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button size="sm" variant="ghost" @click="editFeeStructure(fs)">Edit</Button>
            <Button size="sm" variant="ghost" @click="requestDelete(fs.id)">Delete</Button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      :open="confirmOpen"
      title="Delete Fee Structure"
      message="This action cannot be undone. Are you sure you want to delete this fee structure?"
      confirm-text="Delete"
      variant="danger"
      @close="confirmOpen = false"
      @confirm="deleteFeeStructure"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import Button from '@/components/ui/Button.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { api } from '@/services/api'

const props = defineProps<{ courseId: number }>()
const emit = defineEmits(['edit'])

const feeStructures = ref<any[]>([])
const loading = ref(false)
const confirmOpen = ref(false)
const deletingId = ref<number | null>(null)
const toast = useToast()

async function loadFeeStructures() {
  loading.value = true
  try {
    feeStructures.value = await api.admin.feeStructures.list(props.courseId) as any
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load fee structures')
  } finally {
    loading.value = false
  }
}

function editFeeStructure(fs: any) {
  emit('edit', fs)
}

function requestDelete(id: number) {
  deletingId.value = id
  confirmOpen.value = true
}

async function deleteFeeStructure() {
  if (!deletingId.value) return
  try {
    await api.admin.feeStructures.delete(props.courseId, deletingId.value)
    toast.success('Fee structure deleted')
    confirmOpen.value = false
    deletingId.value = null
    await loadFeeStructures()
  } catch (e: any) {
    toast.error(e?.message || 'Failed to delete fee structure')
  }
}

onMounted(loadFeeStructures)

defineExpose({
  reload: loadFeeStructures,
})
</script>


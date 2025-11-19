<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">{{ $t('admin.faqs.title') }}</h3>
      <Button size="sm" @click="openCreate = true">{{ $t('admin.faqs.createNew') }}</Button>
    </div>

    <Card>
      <template #header>
        <div class="text-sm text-muted">{{ $t('admin.faqs.list') }}</div>
      </template>
      <div v-if="loading" class="p-4 text-center text-sm text-muted">{{ $t('common.loading') }}</div>
      <div v-else-if="faqs.length === 0" class="p-4 text-center text-sm text-muted">
        {{ $t('admin.faqs.noFaqs') }}
      </div>
      <div v-else class="divide-y divide-black/5">
        <div
          v-for="faq in faqs"
          :key="faq.id"
          class="p-4 hover:bg-black/2 transition-colors"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-start gap-3 mb-2">
                <div class="text-sm font-medium text-text">
                  {{ getQuestion(faq) }}
                </div>
              </div>
              <div class="text-xs text-muted mb-2">
                {{ $t('admin.faqs.assignedStaff') }}:
                <span v-if="faq.assigned_staff" class="font-medium text-text">
                  {{ faq.assigned_staff?.name || '—' }}
                </span>
                <span v-else class="text-warning">{{ $t('admin.faqs.notAssigned') }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <Button size="sm" variant="secondary" @click="openEditFaq(faq)">{{ $t('common.edit') }}</Button>
              <Button
                v-if="faq.assigned_staff_id"
                size="sm"
                variant="ghost"
                @click="unassignStaff(faq.id)"
              >
                {{ $t('admin.faqs.unassign') }}
              </Button>
              <select
                v-model="staffAssignments[faq.id]"
                class="text-sm border border-black/10 rounded-lg px-3 py-1.5 bg-white"
                @change="assignStaff(faq.id, Number(staffAssignments[faq.id]))"
              >
                <option value="">{{ $t('admin.faqs.selectStaff') }}</option>
                <option
                  v-for="staff in staffList"
                  :key="staff.id"
                  :value="staff.id"
                >
                  {{ staff.name }}
                </option>
              </select>
              <Button
                size="sm"
                variant="danger"
                @click="confirmDelete(faq.id)"
              >
                {{ $t('common.remove') }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Card>

    <!-- Create FAQ Modal -->
    <FaqCreateModal
      :open="openCreate"
      :errors="createErrors"
      :submitting="creating"
      @close="openCreate = false"
      @submit="createFaq"
    />

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :open="confirmOpen"
      :title="$t('admin.faqs.deleteConfirmTitle')"
      :message="$t('admin.faqs.deleteConfirmMessage')"
      :confirm-text="$t('common.remove')"
      variant="danger"
      @close="confirmOpen = false"
      @confirm="deleteFaq"
    />

    <!-- Edit FAQ Modal -->
    <FaqEditModal
      :open="openEdit"
      :faq="selectedFaq || undefined"
      :errors="editErrors"
      :submitting="editing"
      @close="openEdit = false"
      @submit="updateFaq"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import FaqCreateModal from '@/components/admin/faqs/FaqCreateModal.vue'
import FaqEditModal from '@/components/staff/faqs/FaqEditModal.vue'
import { api } from '@/services/api'

type Faq = {
  id: number
  assigned_staff_id?: number
  assigned_staff?: { id: number; name: string; email: string }
  i18n: {
    question: { en: string; ar: string }
    answer: { en: string; ar: string }
  }
  order: number
  is_active: boolean
}

type Staff = { id: number; name: string; email: string; role: string }

const { t, locale } = useI18n()
const toast = useToast()

const faqs = ref<Faq[]>([])
const staffList = ref<Staff[]>([])
const staffAssignments = ref<Record<number, string>>({})
const loading = ref(true)
const openCreate = ref(false)
const creating = ref(false)
const createErrors = ref<Record<string, any>>({})
const confirmOpen = ref(false)
const confirmFaqId = ref<number | null>(null)
const openEdit = ref(false)
const selectedFaq = ref<Faq | null>(null)
const editing = ref(false)
const editErrors = ref<Record<string, any>>({})

function getQuestion(faq: Faq): string {
  const l = locale.value as 'en' | 'ar'
  return faq.i18n?.question?.[l] || faq.i18n?.question?.en || '—'
}

async function loadFaqs() {
  loading.value = true
  try {
    const response = await api.admin.faqs.list() as any
    faqs.value = Array.isArray(response) ? response : response?.data || []
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedLoad') as string)
  } finally {
    loading.value = false
  }
}

async function loadStaff() {
  try {
    const response = await api.admin.users.list({ role: 'staff' }) as any
    staffList.value = Array.isArray(response) ? response : response?.data || []
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedLoad') as string)
  }
}

async function createFaq(payload: {
  i18n: { question: { en: string; ar: string }; answer: { en: string; ar: string } }
  order?: number
  is_active?: boolean
}) {
  creating.value = true
  createErrors.value = {}
  try {
    await api.admin.faqs.create(payload)
    toast.success(t('admin.faqs.created') as string)
    openCreate.value = false
    await loadFaqs()
  } catch (e: any) {
    if (e?.errors) {
      createErrors.value = e.errors
    } else {
      toast.error(e?.message || t('toasts.failedCreate') as string)
    }
  } finally {
    creating.value = false
  }
}

async function assignStaff(faqId: number, staffId: number) {
  if (!staffId) return
  try {
    await api.admin.faqs.assignStaff(faqId, staffId)
    toast.success(t('admin.faqs.staffAssigned') as string)
    await loadFaqs()
    staffAssignments.value[faqId] = ''
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedUpdate') as string)
  }
}

async function unassignStaff(faqId: number) {
  try {
    await api.admin.faqs.unassignStaff(faqId)
    toast.success(t('admin.faqs.staffUnassigned') as string)
    await loadFaqs()
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedUpdate') as string)
  }
}

function confirmDelete(id: number) {
  confirmFaqId.value = id
  confirmOpen.value = true
}

async function deleteFaq() {
  if (!confirmFaqId.value) return
  try {
    await api.admin.faqs.delete(confirmFaqId.value)
    toast.success(t('admin.faqs.deleted') as string)
    confirmOpen.value = false
    confirmFaqId.value = null
    await loadFaqs()
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedDelete') as string)
  }
}

function openEditFaq(faq: Faq) {
  selectedFaq.value = faq
  openEdit.value = true
  editErrors.value = {}
}

async function updateFaq(payload: {
  i18n: { question: { en: string; ar: string }; answer: { en: string; ar: string } }
  order?: number
  is_active?: boolean
}) {
  if (!selectedFaq.value) return
  editing.value = true
  editErrors.value = {}
  try {
    await api.admin.faqs.update(selectedFaq.value.id, payload)
    openEdit.value = false
    selectedFaq.value = null
    await loadFaqs()
    toast.success(t('common.saved') as string)
  } catch (e: any) {
    if (e?.errors) {
      editErrors.value = e.errors
    } else {
      editErrors.value = e
    }
  } finally {
    editing.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadFaqs(), loadStaff()])
})
</script>


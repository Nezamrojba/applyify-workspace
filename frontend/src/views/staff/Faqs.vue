<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">{{ $t('staff.faqs.title') }}</h3>
    </div>

    <Card>
      <template #header>
        <div class="text-sm text-muted">{{ $t('staff.faqs.myFaqs') }}</div>
      </template>
      <div v-if="loading" class="p-4 text-center text-sm text-muted">{{ $t('common.loading') }}</div>
      <div v-else-if="faqs.length === 0" class="p-4 text-center text-sm text-muted">
        {{ $t('staff.faqs.noFaqs') }}
      </div>
      <div v-else class="divide-y divide-black/5">
        <div
          v-for="faq in faqs"
          :key="faq.id"
          class="p-4 hover:bg-black/2 transition-colors"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-text mb-2">
                {{ getQuestion(faq) }}
              </div>
              <div class="text-xs text-muted">
                {{ getAnswer(faq) }}
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <Button size="sm" variant="ghost" @click="editFaq(faq)">{{ $t('common.view') }}</Button>
            </div>
          </div>
        </div>
      </div>
    </Card>

    <!-- Edit FAQ Modal -->
    <FaqEditModal
      :open="openEdit"
      :faq="editingFaq"
      :errors="editErrors"
      :submitting="updating"
      @close="openEdit = false"
      @submit="updateFaq"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import FaqEditModal from '@/components/staff/faqs/FaqEditModal.vue'
import { api } from '@/services/api'

type Faq = {
  id: number
  i18n: {
    question: { en: string; ar: string }
    answer: { en: string; ar: string }
  }
  order: number
  is_active: boolean
}

const { t, locale } = useI18n()
const toast = useToast()

const faqs = ref<Faq[]>([])
const loading = ref(true)
const openEdit = ref(false)
const updating = ref(false)
const editErrors = ref<Record<string, any>>({})
const editingFaq = ref<Faq | undefined>(undefined)

function getQuestion(faq: Faq): string {
  const l = locale.value as 'en' | 'ar'
  return faq.i18n?.question?.[l] || faq.i18n?.question?.en || '—'
}

function getAnswer(faq: Faq): string {
  const l = locale.value as 'en' | 'ar'
  const answer = faq.i18n?.answer?.[l] || faq.i18n?.answer?.en || '—'
  return answer.length > 100 ? answer.substring(0, 100) + '...' : answer
}

async function loadFaqs() {
  loading.value = true
  try {
    const response = await api.staff.faqs.list() as any
    faqs.value = Array.isArray(response) ? response : response?.data || []
  } catch (e: any) {
    toast.error(e?.message || t('toasts.failedLoad') as string)
  } finally {
    loading.value = false
  }
}

function editFaq(faq: Faq) {
  editingFaq.value = faq
  openEdit.value = true
}

async function updateFaq(payload: {
  i18n: { question: { en: string; ar: string }; answer: { en: string; ar: string } }
  order?: number
  is_active?: boolean
}) {
  if (!editingFaq.value) return
  updating.value = true
  editErrors.value = {}
  try {
    await api.staff.faqs.update(editingFaq.value.id, payload)
    toast.success(t('staff.faqs.updated') as string)
    openEdit.value = false
    editingFaq.value = undefined
    await loadFaqs()
  } catch (e: any) {
    if (e?.errors) {
      editErrors.value = e.errors
    } else {
      toast.error(e?.message || t('toasts.failedUpdate') as string)
    }
  } finally {
    updating.value = false
  }
}

onMounted(loadFaqs)
</script>


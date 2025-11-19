<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h4 class="text-lg font-semibold">{{ $t('staff.faqs.editTitle') }}</h4>
      
      <!-- Question (English) -->
      <div>
        <label class="block text-sm font-medium text-text mb-1">{{ $t('staff.faqs.questionEn') }}</label>
        <textarea
          v-model="form.questionEn"
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
          :class="{ 'border-danger': fieldError('i18n.question.en') }"
          :placeholder="$t('staff.faqs.questionPlaceholderEn')"
        />
        <p v-if="fieldError('i18n.question.en')" class="mt-1 text-xs text-danger">
          {{ fieldError('i18n.question.en') }}
        </p>
      </div>

      <!-- Question (Arabic) -->
      <div>
        <label class="block text-sm font-medium text-text mb-1">{{ $t('staff.faqs.questionAr') }}</label>
        <textarea
          v-model="form.questionAr"
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
          :class="{ 'border-danger': fieldError('i18n.question.ar') }"
          :placeholder="$t('staff.faqs.questionPlaceholderAr')"
          dir="rtl"
        />
        <p v-if="fieldError('i18n.question.ar')" class="mt-1 text-xs text-danger">
          {{ fieldError('i18n.question.ar') }}
        </p>
      </div>

      <!-- Answer (English) -->
      <div>
        <label class="block text-sm font-medium text-text mb-1">{{ $t('staff.faqs.answerEn') }}</label>
        <textarea
          v-model="form.answerEn"
          rows="5"
          class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
          :class="{ 'border-danger': fieldError('i18n.answer.en') }"
          :placeholder="$t('staff.faqs.answerPlaceholderEn')"
        />
        <p v-if="fieldError('i18n.answer.en')" class="mt-1 text-xs text-danger">
          {{ fieldError('i18n.answer.en') }}
        </p>
      </div>

      <!-- Answer (Arabic) -->
      <div>
        <label class="block text-sm font-medium text-text mb-1">{{ $t('staff.faqs.answerAr') }}</label>
        <textarea
          v-model="form.answerAr"
          rows="5"
          class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm"
          :class="{ 'border-danger': fieldError('i18n.answer.ar') }"
          :placeholder="$t('staff.faqs.answerPlaceholderAr')"
          dir="rtl"
        />
        <p v-if="fieldError('i18n.answer.ar')" class="mt-1 text-xs text-danger">
          {{ fieldError('i18n.answer.ar') }}
        </p>
      </div>

      <!-- Order -->
      <Input
        v-model.number="form.order"
        type="number"
        :placeholder="$t('staff.faqs.order')"
        :error="fieldError('order')"
      />

      <!-- Active Status -->
      <div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            v-model="form.isActive"
            type="checkbox"
            class="rounded border-black/10"
          />
          <span class="text-sm text-text">{{ $t('staff.faqs.isActive') }}</span>
        </label>
      </div>

      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
      
      <div class="flex justify-end gap-2">
        <Button :disabled="submitting" @click="handleSubmit">{{ $t('common.continue') }}</Button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

type Faq = {
  id: number
  i18n: {
    question: { en: string; ar: string }
    answer: { en: string; ar: string }
  }
  order: number
  is_active: boolean
}

const props = defineProps<{
  open: boolean
  faq?: Faq
  errors?: Record<string, any>
  submitting?: boolean
}>()
const emit = defineEmits(['close', 'submit'])
const { t } = useI18n()

const form = ref<{
  questionEn: string
  questionAr: string
  answerEn: string
  answerAr: string
  order: number
  isActive: boolean
}>({
  questionEn: '',
  questionAr: '',
  answerEn: '',
  answerAr: '',
  order: 0,
  isActive: true
})

const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')

function fieldError(key: string) {
  const e = (props.errors as any)?.errors?.[key] || (props.errors as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

watch(() => props.faq, (faq) => {
  if (faq) {
    form.value = {
      questionEn: faq.i18n?.question?.en || '',
      questionAr: faq.i18n?.question?.ar || '',
      answerEn: faq.i18n?.answer?.en || '',
      answerAr: faq.i18n?.answer?.ar || '',
      order: faq.order || 0,
      isActive: faq.is_active ?? true
    }
  }
})

watch(() => props.open, (isOpen) => {
  if (!isOpen && props.faq) {
    form.value = {
      questionEn: props.faq.i18n?.question?.en || '',
      questionAr: props.faq.i18n?.question?.ar || '',
      answerEn: props.faq.i18n?.answer?.en || '',
      answerAr: props.faq.i18n?.answer?.ar || '',
      order: props.faq.order || 0,
      isActive: props.faq.is_active ?? true
    }
  }
})

function handleSubmit() {
  emit('submit', {
    i18n: {
      question: {
        en: form.value.questionEn,
        ar: form.value.questionAr
      },
      answer: {
        en: form.value.answerEn,
        ar: form.value.answerAr
      }
    },
    order: form.value.order || 0,
    is_active: form.value.isActive
  })
}
</script>


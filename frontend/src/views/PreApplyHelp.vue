<template>
  <section class="container section stack">
    <div class="space-y-3 text-center md:text-left">
      <h1 class="text-3xl font-semibold">{{ t('publicHelp.title') }}</h1>
      <p class="text-sm text-muted max-w-2xl mx-auto md:mx-0">
        {{ t('publicHelp.subtitle') }}
      </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,360px)]">
      <Card class="p-6 space-y-5">
        <form class="space-y-4" @submit.prevent="submit">
          <div>
            <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('publicHelp.form.name') }}</label>
            <Input v-model="form.name" class="h-11 mt-1" autocomplete="name" />
          </div>
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('publicHelp.form.email') }}</label>
              <Input v-model="form.email" class="h-11 mt-1" type="email" autocomplete="email" />
              <p v-if="errors.email" class="mt-1 text-xs text-danger">{{ errors.email }}</p>
            </div>
            <div>
              <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('publicHelp.form.whatsapp') }}</label>
              <Input v-model="form.whatsapp" class="h-11 mt-1" autocomplete="tel" />
              <p v-if="errors.whatsapp" class="mt-1 text-xs text-danger">{{ errors.whatsapp }}</p>
            </div>
          </div>
          <div>
            <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('publicHelp.form.question') }}</label>
            <textarea
              v-model="form.question"
              rows="5"
              class="w-full mt-1 rounded-lg border border-black/10 px-3 py-3 text-sm leading-6 focus:outline-none focus:ring-2 focus:ring-primary/30"
              :placeholder="t('publicHelp.form.placeholder')"
            ></textarea>
            <div class="flex justify-between items-start mt-1">
              <p class="text-xs text-muted">{{ t('publicHelp.form.helper') }}</p>
              <p v-if="errors.question" class="text-xs text-danger">{{ errors.question }}</p>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-black/5">
            <div v-if="successMessage" class="text-xs text-success">{{ successMessage }}</div>
            <Button
              type="submit"
              variant="primary"
              class="sm:w-auto w-full"
              :disabled="submitting"
            >
              <span v-if="submitting" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ t('publicHelp.form.sending') }}
              </span>
              <span v-else>{{ t('publicHelp.form.submit') }}</span>
            </Button>
          </div>
        </form>
      </Card>

      <div class="space-y-4">
        <Card class="p-5 space-y-3">
          <h2 class="text-lg font-semibold">{{ t('publicHelp.tips.title') }}</h2>
          <ul class="space-y-2 text-sm text-muted leading-6">
            <li>{{ t('publicHelp.tips.item1') }}</li>
            <li>{{ t('publicHelp.tips.item2') }}</li>
            <li>{{ t('publicHelp.tips.item3') }}</li>
          </ul>
        </Card>

        <Card v-if="assignedStaff" class="p-5 space-y-4 border-primary/20 bg-primary/5">
          <div>
            <h2 class="text-lg font-semibold text-primary">{{ t('publicHelp.success.title', { name: assignedStaff.name }) }}</h2>
            <p class="text-sm text-muted">{{ t('publicHelp.success.description') }}</p>
          </div>
          <div class="space-y-3">
            <div v-if="assignedStaff.email">
              <div class="text-xs uppercase text-muted">{{ t('publicHelp.staff.email') }}</div>
              <a :href="`mailto:${assignedStaff.email}`" class="text-sm font-medium text-primary hover:underline">{{ assignedStaff.email }}</a>
            </div>
            <div>
              <div class="text-xs uppercase text-muted">{{ t('publicHelp.staff.whatsapp') }}</div>
              <template v-if="assignedStaff.whatsapp">
                <a :href="`https://wa.me/${normalizedWhatsapp}`" target="_blank" rel="noopener" class="text-sm font-medium text-primary hover:underline">
                  {{ assignedStaff.whatsapp }}
                </a>
              </template>
              <span v-else class="text-sm text-muted">{{ t('publicHelp.staff.noWhatsapp') }}</span>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import Button from '@/components/ui/Button.vue'
import Card from '@/components/ui/Card.vue'
import Input from '@/components/ui/Input.vue'

type InquiryResponse = {
  inquiry_id: number
  assigned_staff: {
    id: number
    name: string
    email?: string
    whatsapp?: string
  } | null
}

const { t } = useI18n()

const form = reactive({
  name: '',
  email: '',
  whatsapp: '',
  question: ''
})

const errors = reactive<Record<string, string>>({})
const submitting = ref(false)
const successMessage = ref<string | null>(null)
const assignedStaff = ref<InquiryResponse['assigned_staff']>(null)

const normalizedWhatsapp = computed(() => {
  if (!assignedStaff.value?.whatsapp) return ''
  return assignedStaff.value.whatsapp.replace(/\D/g, '')
})

function resetErrors() {
  errors.name = ''
  errors.email = ''
  errors.whatsapp = ''
  errors.question = ''
}

async function submit() {
  resetErrors()
  successMessage.value = null
  submitting.value = true
  assignedStaff.value = null

  try {
    const response = await api.publicHelp.submitInquiry({
      name: form.name || undefined,
      email: form.email || undefined,
      whatsapp: form.whatsapp || undefined,
      question: form.question
    }) as InquiryResponse & { message?: string }

    successMessage.value = response.message || t('publicHelp.success.toast')
    assignedStaff.value = response.assigned_staff
    form.name = ''
    form.email = ''
    form.whatsapp = ''
    form.question = ''
  } catch (error: any) {
    if (error?.status === 422 && error?.errors) {
      Object.entries(error.errors).forEach(([key, value]) => {
        errors[key] = Array.isArray(value) ? value[0] : value
      })
    } else {
      successMessage.value = t('publicHelp.errors.generic') as string
    }
  } finally {
    submitting.value = false
  }
}
</script>


<template>
  <div class="min-h-screen bg-surface">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary/5 via-white to-success/5 py-12 lg:py-20">
      <div class="container text-center space-y-4">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-primary">
          {{ $t('faqs.badge') }}
        </span>
        <h1 class="text-3xl lg:text-4xl font-bold leading-tight text-balance">
          {{ $t('faqs.title') }}
        </h1>
        <p class="text-sm lg:text-base text-muted max-w-2xl mx-auto">
          {{ $t('faqs.subtitle') }}
        </p>
      </div>
    </section>

    <!-- FAQs Section -->
    <section class="container py-12 lg:py-20">
      <div v-if="loading" class="text-center py-12">
        <div class="text-sm text-muted">{{ $t('common.loading') }}</div>
      </div>

      <div v-else-if="faqs.length === 0" class="text-center py-12">
        <div class="text-sm text-muted">{{ $t('faqs.noFaqsAvailable') }}</div>
      </div>

      <div v-else class="space-y-4 max-w-4xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
        <div
          v-for="(faq, index) in faqs"
          :key="faq.id"
          class="rounded-xl border border-black/10 bg-white overflow-hidden"
        >
          <button
            @click="toggleFaq(index)"
            class="w-full p-5 lg:p-6 flex items-start justify-between gap-4 hover:bg-black/2 transition-colors"
          >
            <div class="flex-1 min-w-0">
              <div class="text-base lg:text-lg font-semibold text-text pr-8" :class="isRtl ? 'text-right' : 'text-left'">
                {{ getQuestion(faq) }}
              </div>
            </div>
            <div class="shrink-0">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-5 w-5 text-muted transition-transform"
                :class="{ 'rotate-180': openFaqs.includes(index) }"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </div>
          </button>

          <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[500px] opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-[500px] opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="openFaqs.includes(index)" class="px-5 lg:px-6 pb-5 lg:pb-6">
              <div class="pt-4 border-t border-black/10">
                <div class="text-sm lg:text-base text-muted leading-relaxed whitespace-pre-line" :class="isRtl ? 'text-right' : 'text-left'">
                  {{ getAnswer(faq) }}
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- CTA Section -->
      <div v-if="faqs.length > 0" class="mt-12 text-center">
        <Card class="p-6 lg:p-8 bg-primary/5 border-primary/20 max-w-2xl mx-auto">
          <h2 class="text-xl lg:text-2xl font-bold mb-3">{{ $t('faqs.cta.title') }}</h2>
          <p class="text-sm lg:text-base text-muted mb-6">
            {{ $t('faqs.cta.description') }}
          </p>
          <router-link to="/ask-advisor">
            <Button size="lg" variant="primary">
              {{ $t('faqs.cta.button') }}
            </Button>
          </router-link>
        </Card>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'

type Faq = {
  id: number
  i18n: {
    question: { en: string; ar: string }
    answer: { en: string; ar: string }
  }
  order: number
}

const { t, locale } = useI18n()
const isRtl = computed(() => locale.value === 'ar')

const faqs = ref<Faq[]>([])
const loading = ref(true)
const openFaqs = ref<number[]>([])

function getQuestion(faq: Faq): string {
  const l = locale.value as 'en' | 'ar'
  return faq.i18n?.question?.[l] || faq.i18n?.question?.en || '—'
}

function getAnswer(faq: Faq): string {
  const l = locale.value as 'en' | 'ar'
  return faq.i18n?.answer?.[l] || faq.i18n?.answer?.en || '—'
}

function toggleFaq(index: number) {
  const idx = openFaqs.value.indexOf(index)
  if (idx > -1) {
    openFaqs.value.splice(idx, 1)
  } else {
    openFaqs.value.push(index)
  }
}

async function loadFaqs() {
  loading.value = true
  try {
    const response = await api.publicHelp.faqs.list() as any
    faqs.value = Array.isArray(response) ? response : response?.data || []
  } catch (e: any) {
    console.error('Failed to load FAQs', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadFaqs)
</script>


<template>
  <div class="min-h-screen bg-surface">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary/5 via-white to-success/5 py-12 lg:py-20">
      <div class="container">
        <div class="text-center space-y-4 max-w-3xl mx-auto">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
              <path d="M10 2a.75.75 0 01.66.395l1.45 2.694 2.964.447a.75.75 0 01.416 1.279l-2.144 2.066.506 2.937a.75.75 0 01-1.088.791L10 11.547l-2.764 1.462a.75.75 0 01-1.088-.79l.507-2.938L4.511 6.815a.75.75 0 01.415-1.279l2.965-.447 1.45-2.694A.75.75 0 0110 2z" />
            </svg>
            {{ t('howItWorks.badge') }}
          </span>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight text-balance">
            {{ t('howItWorks.title') }}
          </h1>
          <p class="text-base sm:text-lg text-muted max-w-2xl mx-auto">
            {{ t('howItWorks.subtitle') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Steps Section -->
    <section class="container py-12 lg:py-12">
      <div class="grid gap-8 md:grid-cols-2 lg:gap-12">
        <StepCard
          v-for="step in steps"
          :key="step.id"
          :step="step"
        />
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-white py-12 lg:py-20 border-t border-black/10">
      <div class="container">
        <div class="text-center space-y-6 max-w-2xl mx-auto">
          <h2 class="text-2xl lg:text-3xl font-bold">{{ t('howItWorks.cta.title') }}</h2>
          <p class="text-base text-muted">{{ t('howItWorks.cta.description') }}</p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button size="lg" @click="$router.push('/')">
              {{ t('howItWorks.cta.primaryButton') }}
            </Button>
            <Button size="lg" variant="ghost" @click="$router.push('/ask-advisor')">
              {{ t('howItWorks.cta.secondaryButton') }}
            </Button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useHowItWorks } from '@/composables/useHowItWorks'
import StepCard from '@/components/howItWorks/StepCard.vue'
import Button from '@/components/ui/Button.vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const { steps } = useHowItWorks()
const { t } = useI18n()
const router = useRouter()
</script>

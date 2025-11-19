<template>
  <section class="bg-surface py-12 lg:py-12">
    <div class="container space-y-12">
      <div class="text-center space-y-4">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-primary">
          {{ t('landing.processSteps.badge') }}
        </span>
        <h2 class="text-2xl lg:text-3xl font-bold leading-tight text-balance">
          {{ t('landing.processSteps.title') }}
        </h2>
        <p class="text-sm lg:text-base text-muted max-w-2xl mx-auto">
          {{ t('landing.processSteps.subtitle') }}
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
        <div
          v-for="step in steps"
          :key="step.number"
          class="rounded-2xl border border-black/10 bg-white p-6 lg:p-8 space-y-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start gap-4">
            <div
              :class="[
                'h-12 w-12 rounded-full flex items-center justify-center shrink-0 font-bold text-lg text-white',
                {
                  'bg-primary': step.color === 'primary',
                  'bg-success': step.color === 'success',
                  'bg-sky-500/100': step.color === 'info',
                  'bg-warning': step.color === 'warning',
                },
              ]"
            >
              {{ step.number }}
            </div>
            <div class="flex-1 space-y-2">
              <h3 class="text-lg lg:text-xl font-bold text-text">{{ step.title }}</h3>
              <p class="text-sm text-muted leading-relaxed">{{ step.description }}</p>
              <ul class="space-y-2 mt-4">
                <li
                  v-for="(item, index) in step.items"
                  :key="index"
                  class="flex items-start gap-2 text-sm text-muted"
                >
                  <span
                    :class="[
                      'mt-0.5',
                      {
                        'text-primary': step.color === 'primary',
                        'text-success': step.color === 'success',
                        'text-info': step.color === 'info',
                        'text-warning': step.color === 'warning',
                      },
                    ]"
                  >
                    •
                  </span>
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useStudyProcessSteps } from '@/composables/useStudyProcessSteps'
import { useI18n } from 'vue-i18n'

const { steps } = useStudyProcessSteps()
const { t } = useI18n()
</script>


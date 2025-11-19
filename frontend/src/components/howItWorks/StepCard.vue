<template>
  <div
    class="rounded-2xl border border-black/10 bg-white p-6 lg:p-8 space-y-4 hover:shadow-lg transition-all duration-300 h-full flex flex-col"
  >
    <!-- Header with number and icon -->
    <div class="flex items-start gap-4">
      <div
        :class="[
          'h-14 w-14 rounded-2xl flex items-center justify-center shrink-0 font-bold text-xl text-white shadow-md',
          {
            'bg-primary': step.color === 'primary',
            'bg-success': step.color === 'success',
            'bg-info': step.color === 'info',
            'bg-warning': step.color === 'warning',
          },
        ]"
      >
        <span>{{ step.number }}</span>
      </div>
      <div class="flex-1 pt-1">
        <div
          :class="[
            'text-xs font-semibold uppercase tracking-wide mb-1',
            {
              'text-primary': step.color === 'primary',
              'text-success': step.color === 'success',
              'text-info': step.color === 'info',
              'text-warning': step.color === 'warning',
            },
          ]"
        >
          {{ t('howItWorks.stepLabel', { number: step.number }) }}
        </div>
        <h3 class="text-lg lg:text-xl font-bold text-text leading-tight">{{ step.title }}</h3>
      </div>
    </div>

    <!-- Image (if provided) -->
    <div v-if="step.imageUrl" class="rounded-xl overflow-hidden bg-surface">
      <img
        :src="step.imageUrl"
        :alt="step.imageAlt || step.title"
        class="w-full h-48 object-cover"
        loading="lazy"
      />
    </div>

    <!-- Description -->
    <p class="text-sm lg:text-base text-muted leading-relaxed flex-1">{{ step.description }}</p>

    <!-- Features list (if provided) -->
    <ul v-if="step.features && step.features.length > 0" class="space-y-2 mt-4">
      <li
        v-for="(feature, index) in step.features"
        :key="index"
        class="flex items-start gap-2 text-sm text-muted"
      >
        <span
          :class="[
            'mt-0.5 shrink-0',
            {
              'text-primary': step.color === 'primary',
              'text-success': step.color === 'success',
              'text-info': step.color === 'info',
              'text-warning': step.color === 'warning',
            },
          ]"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="h-4 w-4"
          >
            <path
              fill-rule="evenodd"
              d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
              clip-rule="evenodd"
            />
          </svg>
        </span>
        <span>{{ feature }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import type { HowItWorksStep } from '@/composables/useHowItWorks'
import { useI18n } from 'vue-i18n'

defineProps<{
  step: HowItWorksStep
}>()

const { t } = useI18n()
</script>


<template>
  <div
    class="rounded-2xl border border-black/10 bg-white p-6 lg:p-8 space-y-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
  >
    <div class="flex items-start gap-4">
      <!-- Step Number Badge -->
      <div
        :class="[
          'h-14 w-14 rounded-2xl flex items-center justify-center shrink-0 font-bold text-xl text-white shadow-lg',
          {
            'bg-primary': step.color === 'primary',
            'bg-success': step.color === 'success',
            'bg-info': step.color === 'info',
            'bg-warning': step.color === 'warning',
          },
        ]"
      >
        {{ step.id }}
      </div>

      <!-- Content -->
      <div class="flex-1 space-y-3">
        <!-- Icon or Image -->
        <div v-if="step.icon || step.image" class="flex items-center gap-3">
          <div
            v-if="step.icon && !step.image"
            :class="[
              'h-10 w-10 rounded-lg flex items-center justify-center',
              {
                'bg-primary/10 text-primary': step.color === 'primary',
                'bg-success/10 text-success': step.color === 'success',
                'bg-info/10 text-info': step.color === 'info',
                'bg-warning/10 text-warning': step.color === 'warning',
              },
            ]"
          >
            <ExploreIcon v-if="step.icon === 'explore'" class="h-6 w-6" />
            <ApplyIcon v-else-if="step.icon === 'apply'" class="h-6 w-6" />
            <TrackIcon v-else-if="step.icon === 'track'" class="h-6 w-6" />
            <ArriveIcon v-else-if="step.icon === 'arrive'" class="h-6 w-6" />
            <ExploreIcon v-else class="h-6 w-6" />
          </div>
          <img
            v-else-if="step.image"
            :src="step.image"
            :alt="step.title"
            class="h-12 w-12 rounded-lg object-cover"
          />
        </div>

        <!-- Title -->
        <h3 class="text-lg lg:text-xl font-bold text-text leading-snug">
          {{ step.title }}
        </h3>

        <!-- Description -->
        <p class="text-sm lg:text-base text-muted leading-relaxed">
          {{ step.description }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { HowItWorksStep } from '@/composables/useHowItWorks'

interface Props {
  step: HowItWorksStep
}

defineProps<Props>()

const ExploreIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
  </svg>
)

const ApplyIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
)

const TrackIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75v6.75m0 0l-3-3m3 3l3-3m-8.25-6h15M9 3.75h.007v.008H9V3.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
  </svg>
)

const ArriveIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
  </svg>
)
</script>


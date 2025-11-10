<template>
  <div class="grid gap-4 items-start justify-items-start md:grid-cols-[minmax(0,320px)_minmax(200px,240px)] lg:grid-cols-[minmax(0,360px)_minmax(220px,260px)]">
    <div class="space-y-2 w-full md:w-[320px] lg:w-[360px]">
      <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('universities.filters.searchLabel') }}</label>
      <Input
        v-model="courseModel"
        :placeholder="t('universities.searchCourse') as string"
        class="h-10 w-full"
      />
    </div>

    <div class="space-y-2 w-full md:w-[240px] lg:w-[260px]">
      <div class="flex flex-wrap items-center gap-2">
        <label class="flex-1 text-xs font-medium uppercase tracking-wide text-muted leading-snug">{{ t('universities.filters.acceptanceLabel') }}</label>
        <span class="inline-flex shrink-0 items-center text-[11px] font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary">
          {{ minModel }}%
        </span>
      </div>
      <select
        v-model.number="minModelProxy"
        class="h-10 w-full rounded-lg border border-black/10 bg-white px-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
        @change="syncMin"
      >
        <option v-for="option in acceptancePresets" :key="option" :value="option">
          {{ option === 0 ? t('universities.filters.acceptanceAny') : `${option}%+` }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Input from '@/components/ui/Input.vue'
import { useI18n } from 'vue-i18n'

const courseModel = defineModel<string>('course')
const minModel = defineModel<number>('min', { default: 50 })
defineModel<number>('max', { default: 99 })

const { t } = useI18n()

const acceptancePresets = [0, 50, 60, 70, 80, 90]
const minModelProxy = ref(minModel.value ?? 50)

watch(minModel, (value) => {
  if (value !== minModelProxy.value) {
    minModelProxy.value = value ?? 0
  }
})

function syncMin() {
  minModel.value = minModelProxy.value
}
</script>

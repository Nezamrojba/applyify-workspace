<template>
  <Card class="h-full flex flex-col">
    <template #header>
      <div class="stack">
        <div class="flex items-center justify-between">
          <div class="text-sm text-muted font-medium">{{ universityName }}</div>
          <span class="text-xs px-2 py-1 rounded bg-primary/10 text-primary">{{ course.acceptance_percent || acceptance || '-' }}%</span>
        </div>
        <router-link class="font-medium hover:text-primary" :to="{ path: '/courses/'+course.id }">{{ courseName }}</router-link>
      </div>
    </template>
    <template #default>
      <div class="space-y-3 flex-1">
        <div class="grid sm:grid-cols-2 gap-2 text-sm">
          <div class="flex items-center gap-2">
            <span class="text-muted">{{ t('courseCard.level') }}:</span>
            <span class="px-2 py-0.5 rounded bg-black/5 font-medium">{{ levelLabel }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-muted">{{ t('courseCard.acceptance') }}:</span>
            <span class="px-2 py-0.5 rounded bg-primary/10 text-primary font-medium">{{ course.acceptance_percent || acceptance || '—' }}%</span>
          </div>
          <div v-if="displayLocation" class="flex items-center gap-2 sm:col-span-2">
            <span class="text-muted">{{ t('courseCard.location') }}:</span>
            <span class="font-medium">📍 {{ displayLocation }}</span>
          </div>
        </div>
        <div v-if="intakes.length > 0" class="flex flex-wrap gap-2">
          <span class="text-xs text-muted">{{ t('courseCard.intakes') }}:</span>
          <span v-for="m in intakes" :key="m" class="text-xs px-2 py-1 rounded-full border border-primary/10 bg-primary/5 text-primary">{{ m }}</span>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex items-center gap-3 pt-3 border-t border-black/5">
        <router-link class="text-primary" :to="{ path: '/courses/'+course.id }">{{ t('courseCard.viewDetails') }}</router-link>
        <button :disabled="!applicationsEnabled" :class="['rounded-lg h-9 px-4 shadow-md transition-all', applicationsEnabled ? 'bg-primary text-white hover:shadow-lg cursor-pointer' : 'bg-muted text-muted cursor-not-allowed opacity-50']" @click="applicationsEnabled && $emit('apply', { course, university: { id: course.university_id, name: universityName } })">{{$t('universities.apply')}}</button>
      </div>
    </template>
  </Card>
  </template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Card from '@/components/ui/Card.vue'
import { useModuleStatus } from '@/composables/useModuleStatus'

const props = defineProps<{ course: any; universityName: string; acceptance?: number; blurb?: string; location?: string | Record<string,string> }>()
defineEmits(['apply'])
const { locale, t } = useI18n()
const { isModuleEnabled } = useModuleStatus()

const applicationsEnabled = computed(() => isModuleEnabled('applications'))
const courseName = computed(() => {
  const i18nName = props.course?.i18n?.name
  if (!i18nName) return props.course?.name || '—'
  const l = (locale.value as 'en'|'ar')
  if (typeof i18nName === 'string') return i18nName
  return i18nName?.[l] || i18nName?.en || props.course?.name || '—'
})
const normalizedLevelKey = computed(() => {
  const raw = (props.course?.level || '').toString().trim().toLowerCase()
  if (!raw) return ''
  return raw.replace(/[^a-z0-9]+/g, '_')
})
const levelLabel = computed(() => {
  const key = normalizedLevelKey.value
  if (!key) return props.course?.level || '—'
  const translationKey = `courseCard.levels.${key}`
  const translated = t(translationKey)
  return translated !== translationKey ? translated : (props.course?.level || '—')
})
const intakes = computed<string[]>(() => {
  const li = props.course?.i18n?.intakes
  if (!li) return []
  const l = (locale.value as 'en'|'ar')
  return Array.isArray(li) ? li : (li?.[l] || li?.en || [])
})
const displayLocation = computed(() => {
  const loc = props.location as any
  if (!loc) return ''
  if (typeof loc === 'string') return loc
  const l = (locale.value as 'en'|'ar')
  return loc?.[l] || loc?.en || ''
})
</script>

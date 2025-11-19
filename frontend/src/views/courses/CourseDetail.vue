<template>
  <section class="container section stack">
    <div class="stack">
      <h2 class="text-2xl font-semibold">{{ title }}</h2>
      <div class="text-sm text-muted">{{ university?.name }}</div>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="stack">
        <div class="text-sm">{{ blurb }}</div>
        <div v-if="overview?.length" class="stack">
          <h3 class="text-lg font-semibold">{{ t('courseDetail.about.title') }}</h3>
          <p v-for="(p,i) in overview" :key="i" class="text-sm leading-6">{{ p }}</p>
        </div>
        <div v-if="outcomes?.length" class="stack">
          <h3 class="text-lg font-semibold">{{ t('courseDetail.about.outcomes') }}</h3>
          <ul class="list-disc list-inside text-sm leading-6">
            <li v-for="(o,i) in outcomes" :key="i">{{ o }}</li>
          </ul>
        </div>
      </div>
      <div class="stack">
        <div class="flex items-center gap-3">
          <Button class="flex-1" @click="apply">{{$t('universities.apply')}}</Button>
          <button class="h-10 px-4 rounded-lg border border-[#eeeeee] bg-white" @click="goBack">{{ t('courseDetail.actions.back') }}</button>
        </div>
        <div class="stack text-sm">
          <h3 class="text-lg font-semibold">{{ t('courseDetail.keyInfo.title') }}</h3>
          <div class="grid grid-cols-2 gap-2">
            <div>{{ t('courseDetail.keyInfo.level') }}: {{ levelLabel }}</div>
            <div>{{ t('courseDetail.keyInfo.duration') }}: {{ durationLabel }}</div>
            <div>
              {{ t('courseDetail.keyInfo.intakes') }}:
              <template v-if="intakes.length">
                <span v-for="m in intakes" :key="m" class="px-2 py-0.5 rounded bg-primary/5 text-primary mr-1">{{ m }}</span>
              </template>
              <span v-else>{{ t('courseDetail.shared.none') }}</span>
            </div>
            <div>{{ t('courseDetail.keyInfo.language') }}: {{ languageLabel }}</div>
            <div>{{ t('courseDetail.keyInfo.accreditation') }}: {{ accreditationLabel }}</div>
          </div>
        </div>
        <div v-if="requirements?.length" class="stack">
          <h3 class="text-lg font-semibold">{{ t('courseDetail.sections.requirements') }}</h3>
          <ul class="list-disc list-inside text-sm leading-6">
            <li v-for="(r,i) in requirements" :key="i">{{ r }}</li>
          </ul>
        </div>
        <div v-if="careers?.length" class="stack">
          <h3 class="text-lg font-semibold">{{ t('courseDetail.sections.careers') }}</h3>
          <ul class="list-disc list-inside text-sm leading-6">
            <li v-for="(c,i) in careers" :key="i">{{ c }}</li>
          </ul>
        </div>
      </div>
    </div>

    <AuthModal :open="showAuth" @close="showAuth=false" @authed="onAuthed" />
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import Button from '@/components/ui/Button.vue'
import AuthModal from '@/components/auth/AuthModal.vue'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()
const course = ref<any>(null)
const university = ref<any>(null)
onMounted(async () => {
  const id = Number(route.params.id)
  const data = await api.courses.detail(id) as any
  course.value = data
  university.value = data.university
})

const title = computed(() => {
  const i18nName = course.value?.i18n?.name
  if (!i18nName) return course.value?.name || ''
  const l = locale.value as 'en'|'ar'
  if (typeof i18nName === 'string') return i18nName
  return i18nName?.[l] || i18nName?.en || course.value?.name || ''
})
const blurb = computed(() => course.value?.i18n?.blurb?.[locale.value as 'en'|'ar'] || '')

const years = computed(() => Math.max(1, Math.round(((course.value?.duration_months as number) || 12) / 12)))
const durationLabel = computed(() => t('courseCard.years', { count: years.value }))
const normalizedLevelKey = computed(() => {
  const raw = (course.value?.level || '').toString().trim().toLowerCase()
  if (!raw) return ''
  return raw.replace(/[^a-z0-9]+/g, '_')
})
const levelLabel = computed(() => {
  const key = normalizedLevelKey.value
  if (!key) return course.value?.level || t('courseDetail.shared.none')
  const translationKey = `courseCard.levels.${key}`
  const translated = t(translationKey) as string
  return translated === translationKey ? (course.value?.level || key) : translated
})
const intakes = computed<string[]>(() => {
  const li = course.value?.i18n?.intakes
  if (!li) return []
  const l = locale.value as 'en'|'ar'
  return Array.isArray(li) ? li : (li?.[l] || li?.en || [])
})
const overview = computed<string[]>(() => {
  const src = course.value?.i18n?.overview
  if (!src) return []
  const l = locale.value as 'en'|'ar'
  const v = (src as any)?.[l] || (src as any)?.en || src
  return Array.isArray(v) ? v : (v ? [String(v)] : [])
})
const outcomes = computed<string[]>(() => {
  const src = course.value?.i18n?.outcomes
  if (!src) return []
  const l = locale.value as 'en'|'ar'
  const v = (src as any)?.[l] || (src as any)?.en || src
  return Array.isArray(v) ? v : (v ? [String(v)] : [])
})
const requirements = computed<string[]>(() => {
  const src = course.value?.i18n?.requirements
  if (!src) return []
  const l = locale.value as 'en'|'ar'
  const v = (src as any)?.[l] || (src as any)?.en || src
  return Array.isArray(v) ? v : (v ? [String(v)] : [])
})
const careers = computed<string[]>(() => {
  const src = course.value?.i18n?.careers
  if (!src) return []
  const l = locale.value as 'en'|'ar'
  const v = (src as any)?.[l] || (src as any)?.en || src
  return Array.isArray(v) ? v : (v ? [String(v)] : [])
})
const languageLabel = computed(() => {
  const v = (course.value?.i18n as any)?.language
  const l = locale.value as 'en'|'ar'
  return (v?.[l] || v?.en || course.value?.language || t('courseDetail.shared.none')) as string
})
const accreditationLabel = computed(() => {
  const v = (course.value?.i18n as any)?.accreditation
  const l = locale.value as 'en'|'ar'
  return (v?.[l] || v?.en || course.value?.accreditation || t('courseDetail.shared.none')) as string
})
const showAuth = ref(false)
function isAuthed(){ return !!localStorage.getItem('token') }
function apply(){
  if (!isAuthed()) { showAuth.value = true; return }
  router.push({ path: '/', query: { apply: '1', university_id: university.value?.id, course_id: course.value?.id, from: route.fullPath } })
}
function onAuthed(){
  showAuth.value = false
  router.push({ path: '/', query: { apply: '1', university_id: university.value?.id, course_id: course.value?.id, from: route.fullPath } })
}
function goBack(){ router.push({ path: '/' }) }

</script>

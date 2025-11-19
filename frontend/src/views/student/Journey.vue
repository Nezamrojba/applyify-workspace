<template>
  <section class="container section stack">
    <div class="space-y-2">
      <h1 class="text-2xl font-semibold">{{ t('studentJourney.title') }}</h1>
      <p class="text-sm text-muted max-w-2xl">
        {{ t('studentJourney.subtitle') }}
      </p>
    </div>

    <Card class="p-5 space-y-4">
      <div class="grid gap-4 md:grid-cols-[minmax(0,280px)_minmax(0,220px)] lg:grid-cols-[minmax(0,320px)_minmax(0,240px)]">
        <div class="space-y-2">
          <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('studentJourney.filters.university') }}</label>
          <select
            v-model.number="selectedUniversityId"
            class="h-11 w-full rounded-lg border border-black/10 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
            @change="handleFilterChange"
          >
            <option :value="0">{{ t('studentJourney.filters.anyUniversity') }}</option>
            <option v-for="uni in universities" :key="uni.id" :value="uni.id">
              {{ displayName(uni) }}
            </option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-medium uppercase tracking-wide text-muted">{{ t('studentJourney.filters.search') }}</label>
          <Input
            v-model="search"
            :placeholder="t('studentJourney.filters.searchPlaceholder') as string"
            class="h-11"
            @keydown.enter="handleFilterChange"
          />
        </div>
      </div>
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="text-xs text-muted">
          {{ t('studentJourney.results.summary', { count: courses.length }) }}
        </div>
        <div class="flex items-center gap-2">
          <Button size="sm" variant="ghost" @click="resetFilters" :disabled="isDefaultFilter">
            {{ t('studentJourney.filters.reset') }}
          </Button>
          <Button size="sm" variant="primary" @click="handleFilterChange">
            {{ t('studentJourney.filters.apply') }}
          </Button>
        </div>
      </div>
    </Card>

    <div v-if="loading" class="grid gap-4 md:grid-cols-2">
      <Card v-for="i in 4" :key="i" class="p-5 animate-pulse">
        <div class="h-6 bg-muted/40 rounded w-2/3 mb-4" />
        <div class="space-y-2">
          <div class="h-4 bg-muted/30 rounded w-full" />
          <div class="h-4 bg-muted/30 rounded w-5/6" />
          <div class="h-4 bg-muted/30 rounded w-1/2" />
        </div>
      </Card>
    </div>

    <div v-else-if="courses.length === 0" class="rounded-2xl border border-dashed border-black/10 bg-white p-10 text-center text-sm text-muted">
      {{ t('studentJourney.results.empty') }}
    </div>

    <div v-else class="grid gap-6 lg:grid-cols-2">
      <Card v-for="course in courses" :key="course.id" class="p-5 space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
          <div class="space-y-2">
            <h2 class="text-xl font-semibold text-text">{{ displayCourseName(course) }}</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs text-muted uppercase tracking-wide">
              <span class="px-2 py-0.5 rounded bg-primary/10 text-primary">{{ t('courseCard.level') }}: {{ levelLabel(course) }}</span>
              <span class="px-2 py-0.5 rounded bg-black/5 text-text">{{ t('studentJourney.labels.duration', { value: formatDuration(course.duration_months) }) }}</span>
              <span class="px-2 py-0.5 rounded bg-black/5 text-text">{{ t('studentJourney.labels.acceptance', { value: course.acceptance_percent ?? '—' }) }}</span>
              <span v-if="course.allow_installments" class="px-2 py-0.5 rounded bg-success/10 text-success">
                {{ t('studentJourney.labels.installments') }}
              </span>
            </div>
            <p v-if="course.details" class="text-sm leading-6 text-muted">
              {{ course.details }}
            </p>
          </div>
          <div class="space-y-2 text-right text-sm">
            <div class="text-xs uppercase text-muted">{{ t('studentJourney.labels.university') }}</div>
            <div class="font-semibold text-text">{{ displayName(course.university) }}</div>
            <div v-if="course.university?.location" class="text-xs text-muted">
              {{ displayLocation(course.university) }}
            </div>
            <div class="pt-2 border-t border-black/5">
              <div class="text-xs uppercase text-muted">{{ t('studentJourney.labels.totalFees') }}</div>
              <div class="text-lg font-semibold text-primary">
                <span v-if="course.total_tuition_fees">{{ currency(course.total_tuition_fees) }}</span>
                <span v-else>{{ t('courseDetail.shared.none') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="course.active_fee_structure?.semesters?.length" class="space-y-4">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-lg font-semibold">{{ t('studentJourney.timeline.title') }}</h3>
            <div class="text-xs text-muted">
              {{ t('studentJourney.timeline.summary', { semesters: course.active_fee_structure.semesters.length }) }}
            </div>
          </div>
          <div class="space-y-3">
            <div
              v-for="(semester, index) in course.active_fee_structure.semesters"
              :key="index"
              class="rounded-2xl border border-black/10 bg-gradient-to-r from-white via-white to-primary/5 p-4 shadow-sm space-y-2"
            >
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                  <div class="text-xs uppercase text-muted tracking-wide">
                    {{ t('studentJourney.timeline.semester', { index: index + 1 }) }}
                  </div>
                  <div class="text-sm font-semibold">
                    {{ semester.semester_type || t('studentJourney.timeline.unspecifiedSemester') }}
                  </div>
                  <div v-if="semester.academic_period" class="text-xs text-muted">
                    {{ semester.academic_period }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs uppercase text-muted">{{ t('studentJourney.timeline.estimatedCost') }}</div>
                  <div class="text-lg font-semibold text-primary">
                    <span v-if="semesterTotal(semester)">{{ currency(semesterTotal(semester)) }}</span>
                    <span v-else>{{ t('courseDetail.shared.none') }}</span>
                  </div>
                </div>
              </div>
              <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 text-xs text-muted">
                <div v-if="semester.credit_hours" class="rounded-lg border border-black/5 bg-white px-3 py-2">
                  <div class="text-[10px] uppercase tracking-wide">{{ t('studentJourney.timeline.creditHours') }}</div>
                  <div class="text-sm font-semibold text-text">{{ semester.credit_hours }}</div>
                </div>
                <div v-if="semester.tuition_fee" class="rounded-lg border border-black/5 bg-white px-3 py-2">
                  <div class="text-[10px] uppercase tracking-wide">{{ t('studentJourney.timeline.tuition') }}</div>
                  <div class="text-sm font-semibold text-text">{{ currency(semester.tuition_fee) }}</div>
                </div>
                <div v-if="semester.custom_fees?.length" class="sm:col-span-2 lg:col-span-1 rounded-lg border border-primary/20 bg-primary/5 px-3 py-2">
                  <div class="text-[10px] uppercase tracking-wide text-primary">{{ t('studentJourney.timeline.additional') }}</div>
                  <ul class="mt-1 space-y-1 text-xs text-primary/80">
                    <li v-for="(fee, idx) in semester.custom_fees" :key="idx">
                      {{ fee.name }} • {{ currency(fee.amount) }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="course.active_fee_structure?.one_time_fees?.length" class="space-y-2 rounded-2xl border border-black/10 bg-white p-4 text-sm">
          <div class="text-xs uppercase text-muted">{{ t('studentJourney.timeline.oneTime') }}</div>
          <div class="divide-y divide-black/5">
            <div v-for="(fee, idx) in course.active_fee_structure.one_time_fees" :key="idx" class="flex items-start justify-between gap-3 py-2">
              <div>
                <div class="font-medium text-text">{{ fee.name }}</div>
                <div v-if="fee.refundable" class="text-xs text-success">{{ t('studentJourney.timeline.refundable') }}</div>
              </div>
              <div class="font-semibold text-text">{{ currency(fee.amount) }}</div>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

type UniversityOption = {
  id: number
  name: string
  i18n?: Record<string, any>
  location?: string | Record<string, string>
}

type JourneyCourse = {
  id: number
  name: string
  code?: string
  level?: string
  duration_months?: number
  total_tuition_fees?: number
  acceptance_percent?: number
  allow_installments?: boolean
  payment_method?: string | null
  details?: string | null
  i18n?: Record<string, any>
  university: UniversityOption
  active_fee_structure?: {
    semesters?: Array<Record<string, any>>
    one_time_fees?: Array<Record<string, any>>
  } | null
}

const { t, locale } = useI18n()

const universities = ref<UniversityOption[]>([])
const selectedUniversityId = ref<number>(0)
const search = ref('')
const courses = ref<JourneyCourse[]>([])
const loading = ref(false)

const isDefaultFilter = computed(() => selectedUniversityId.value === 0 && !search.value)

onMounted(async () => {
  await fetchUniversities()
  await fetchCourses()
})

async function fetchUniversities() {
  const response = await api.student.journeyUniversities({})
  universities.value = Array.isArray(response)
    ? response
    : Array.isArray((response as any)?.data)
      ? (response as any).data
      : []
}

async function fetchCourses() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedUniversityId.value) params.university_id = selectedUniversityId.value
    if (search.value) params.search = search.value
    const response = await api.student.journeyCourses(params) as { data: JourneyCourse[] }
    courses.value = response?.data || []
  } finally {
    loading.value = false
  }
}

function handleFilterChange() {
  fetchCourses()
}

function resetFilters() {
  selectedUniversityId.value = 0
  search.value = ''
  fetchCourses()
}

function displayName(entity?: UniversityOption | JourneyCourse['university']) {
  if (!entity) return ''
  const l = locale.value as 'en' | 'ar'
  const name = (entity.i18n as any)?.name?.[l] || (entity.i18n as any)?.name?.en
  return name || entity.name
}

function displayLocation(entity?: UniversityOption) {
  if (!entity) return ''
  const l = locale.value as 'en' | 'ar'
  if (typeof entity.location === 'string') return entity.location
  if (entity.location && typeof entity.location === 'object') {
    return entity.location[l] || entity.location.en || ''
  }
  const localized = (entity.i18n as any)?.location
  if (localized) {
    if (typeof localized === 'string') return localized
    return localized[l] || localized.en || ''
  }
  return ''
}

function displayCourseName(course?: JourneyCourse) {
  if (!course) return '—'
  const i18nName = course.i18n?.name
  if (!i18nName) return course.name || '—'
  const l = locale.value as 'en' | 'ar'
  if (typeof i18nName === 'string') return i18nName
  return i18nName?.[l] || i18nName?.en || course.name || '—'
}

function formatDuration(months?: number) {
  if (!months) return t('courseDetail.shared.none')
  const years = Math.max(1, Math.round(months / 12))
  return t('courseCard.years', { count: years })
}

function levelLabel(course: JourneyCourse) {
  const raw = (course.level || '').toString().trim().toLowerCase()
  if (!raw) return course.level || '—'
  const key = raw.replace(/[^a-z0-9]+/g, '_')
  const label = t(`courseCard.levels.${key}`)
  return label !== `courseCard.levels.${key}` ? label : (course.level || '—')
}

function currency(value?: number) {
  if (!value) return t('courseDetail.shared.none')
  const localeCode = locale.value === 'ar' ? 'ar-EG' : 'en-MY'
  return Number(value).toLocaleString(localeCode, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

function semesterTotal(semester: Record<string, any>) {
  const base =
    Number(semester.tuition_fee || 0) +
    Number(semester.library_fee || 0) +
    Number(semester.student_club_fee || 0) +
    Number(semester.ikad_fee || 0) +
    Number(semester.visa_processing_fee || 0) +
    Number(semester.medical_insurance_fee || 0) +
    Number(semester.medical_examination_fee || 0)

  const customTotal = Array.isArray(semester.custom_fees)
    ? semester.custom_fees.reduce((sum: number, fee: any) => sum + Number(fee.amount || 0), 0)
    : 0

  return base + customTotal
}
</script>


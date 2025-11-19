<template>
  <div>
    <Hero @explore="scrollToCatalog" @apply="handleOpenApply" />

    <section class="container py-12 lg:py-12">
      <div class="grid md:grid-cols-2 gap-6 lg:gap-12 items-center">
        <div class="space-y-4">
          <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-primary">
            {{ t('landing.value.badge') }}
          </span>
          <h2 class="text-2xl lg:text-3xl font-semibold leading-snug text-balance">
            {{ t('landing.value.title') }}
          </h2>
          <p class="text-sm lg:text-base text-muted leading-relaxed">
            {{ t('landing.value.description') }}
          </p>
          <div class="grid gap-4">
            <div class="flex gap-3 items-start">
              <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" class="h-5 w-5">
                  <path d="M16.704 2.79a1 1 0 011.096.801l1.5 7.5a1 1 0 01-.621 1.118l-8 3a1 1 0 01-.72 0l-8-3a1 1 0 01-.621-1.118l1.5-7.5A1 1 0 013.456 2.79L10 4.613l6.704-1.824z" />
                  <path d="M10 6.701L4.064 5.07l-.97 4.85 6.906 2.589 6.905-2.59-.97-4.848L10 6.7z" />
                  <path d="M10.75 11.55l4.281-1.604.651 3.254a1 1 0 01-.67 1.148l-4 1.333a1 1 0 01-.5870 0l-4-1.333a1 1 0 01-.670-1.148l.652-3.254 4.28 1.604a.75.75 0 00.463 0z" />
                </svg>
              </div>
              <div class="space-y-1 text-sm">
                <div class="font-semibold text-text">{{ t('landing.value.highlights.tracking.title') }}</div>
                <p class="text-muted leading-relaxed">{{ t('landing.value.highlights.tracking.copy') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <StudyProcessSteps />

    <section ref="catalogSection" class="bg-surface py-12 lg:py-12">
      <div class="container space-y-8">
        <div class="space-y-3 text-center">
          <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-info">
            {{ t('landing.catalog.badge') }}
          </span>
          <h2 class="text-2xl lg:text-3xl font-semibold leading-snug text-balance">{{ t('landing.catalog.title') }}</h2>
          <p class="text-sm text-muted max-w-2xl mx-auto">{{ t('landing.catalog.description') }}</p>
        </div>

        <div class="space-y-6">
          <div class="rounded-3xl border border-black/5 bg-white shadow-sm p-5 space-y-4">
            <div class="space-y-2">
              <h3 class="text-lg font-semibold">{{ t('landing.catalog.filters.title') }}</h3>
              <p class="text-xs text-muted">{{ t('landing.catalog.filters.hint') }}</p>
            </div>
            <UniversityFilters v-model:course="course" v-model:min="min" v-model:max="max" />
          </div>

          <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              <CourseCard
                v-for="c in courseCards"
                :key="c.course.id"
                :course="c.course"
                :university-name="c.uni?.name || ''"
                :acceptance="c.uni?.acceptance_percent"
                :blurb="c.blurb"
                :location="c.uni?.i18n?.location?.[locale] || c.uni?.i18n?.location || ''"
                @apply="onApply({ id: c.uni?.id, name: c.uni?.name })"
              />
            </div>
            <div v-if="courseCards.length === 0" class="border border-black/5 rounded-3xl bg-white p-8 text-center">
              <div class="text-sm text-muted">{{ t('landing.catalog.filters.empty') }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AuthModal :open="showAuth" @close="showAuth=false" @authed="onAuthed" @openApply="handleOpenApply" />
    <ApplyModal :open="showApply" :university="selectedUni" @close="showApply=false" @created="createApp" />
    <CourseDetailModal :open="showDetails" :course="detailCtx?.course" :university-id="detailCtx?.universityId" @close="showDetails=false" />
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Hero from '@/components/landing/Hero.vue'
import StudyProcessSteps from '@/components/landing/StudyProcessSteps.vue'
import UniversityFilters from '@/components/universities/UniversityFilters.vue'
import CourseCard from '@/components/courses/CourseCard.vue'
import CourseDetailModal from '@/components/courses/CourseDetailModal.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import ApplyModal from '@/components/applications/ApplyModal.vue'
import { useUniversityStore } from '@/stores/universities'
import { useApplicationStore } from '@/stores/applications'
import { api } from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

const unis = useUniversityStore()
const route = useRoute()
const router = useRouter()
const apps = useApplicationStore()
const course = ref('')
const min = ref(50)
const max = ref(99)
const showAuth = ref(false)
const showApply = ref(false)
const selectedUni = ref<{ id:number; name:string }|undefined>()
const showDetails = ref(false)
const detailCtx = ref<{ course:any; universityId?:number }|undefined>()
let pendingApply = false

const courses = ref<any[]>([])
const { t, locale: activeLocale } = useI18n()
const catalogSection = ref<HTMLElement | null>(null)

async function load(){
  await unis.fetch({ course: course.value, min_accept: min.value, max_accept: 99 })
  const items: any[] = []
  for (const u of (unis.items || [])) {
    const list = await api.courses.list(u.id) as any
    const arr = (list.data || list || []).slice(0, 2)
    for (const cr of arr) items.push({ course: { ...cr, university_id: u.id }, uni: u })
  }
  courses.value = items
}
watch([course,min,max], load, { immediate: true })

function isAuthed(){ return !!localStorage.getItem('token') }

function onApply(u: { id:number; name:string }){
  selectedUni.value = { id: u.id, name: u.name }
  if (!isAuthed()){ 
    const existing = localStorage.getItem('pendingApply')
    let pendingData: any = {}
    if (existing) {
      try {
        pendingData = JSON.parse(existing)
      } catch {}
    }
    pendingData.university_id = u.id
    localStorage.setItem('pendingApply', JSON.stringify(pendingData))
    showAuth.value = true
    pendingApply = true
    return 
  }
  showApply.value = true
}

function onAuthed(){ 
  if (pendingApply){ 
    showAuth.value = false
    setTimeout(() => {
      showApply.value = true
      pendingApply = false
    }, 100)
  } 
}

function handleOpenApply() {
  // Always check authentication first
  if (!isAuthed()) {
    // Store intent to apply without university (general application)
    localStorage.setItem('pendingApply', JSON.stringify({}))
    showAuth.value = true
    pendingApply = true
    return
  }

  // User is authenticated, proceed with application
  showAuth.value = false
  try {
    const stored = localStorage.getItem('pendingApply')
    if (stored) {
      const data = JSON.parse(stored)
      if (data.university_id) {
        api.universities.detail(data.university_id).then((uni: any) => {
          selectedUni.value = { id: uni.id, name: uni.name }
          showApply.value = true
          if (data.course_id) {
            setTimeout(() => {
              const ev = new CustomEvent('prefill-course', { detail: { courseId: data.course_id } })
              window.dispatchEvent(ev)
            }, 100)
          }
        }).catch(() => {
          selectedUni.value = undefined
          showApply.value = true
        })
      } else {
        selectedUni.value = undefined
        showApply.value = true
      }
    } else {
      selectedUni.value = undefined
      showApply.value = true
    }
  } catch (e) {
    selectedUni.value = undefined
    showApply.value = true
  }
}

async function createApp(p: { university_id:number; course_id:number; passport_no:string; payment_receipt_url?:string }){
  const toast = useToast()
  try{
    const response = await apps.create(p.university_id, p.course_id, p.passport_no, p.payment_receipt_url) as any
    const applicationId = response?.id
    
    if (applicationId) {
      toast.success(t('landing.applySuccessRedirect') as string)
      showApply.value = false
      router.push(`/dashboard/applications/${applicationId}`)
    } else {
      toast.success(t('landing.applySuccess') as string)
      showApply.value = false
      router.push('/dashboard/applications')
    }
  }catch(e:any){ 
    toast.error(e?.message || (t('landing.applyError') as string)) 
  }
}

const courseCards = computed(() => courses.value.map((x) => ({
  course: x.course,
  uni: x.uni,
  blurb: t('landing.courseBlurb', { level: x.course.level, months: x.course.duration_months, name: x.uni.name }) as string
})))

const locale = computed(() => activeLocale.value as 'en' | 'ar')

function onDetails(c: any){
  detailCtx.value = { course: c.course, universityId: c.uni?.id }
  showDetails.value = true
}

function scrollToCatalog() {
  if (catalogSection.value) {
    catalogSection.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

onMounted(async () => {
  try {
    const stored = localStorage.getItem('pendingApply')
    if (stored && isAuthed()) {
      const data = JSON.parse(stored)
      if (data.university_id) {
        const uid = Number(data.university_id)
        const cid = data.course_id ? Number(data.course_id) : undefined
        let uni = (unis.items || []).find(u => u.id === uid)
        if (!uni) {
          try { uni = await api.universities.detail(uid) as any } catch {}
        }
        if (uni) {
          selectedUni.value = { id: uni.id, name: uni.name }
          showApply.value = true
          localStorage.removeItem('pendingApply')
          if (cid) {
            setTimeout(() => {
              const ev = new CustomEvent('prefill-course', { detail: { courseId: cid } })
              window.dispatchEvent(ev)
            }, 200)
          }
          return
        }
      }
    }
  } catch (e) {
    // ignore
  }
  
  const q = route.query as any
  if (q && (q.apply === '1' || q.apply === 1)) {
    const uid = Number(q.university_id)
    const cid = Number(q.course_id)
    if (uid) {
      let uni = (unis.items || []).find(u => u.id === uid)
      if (!uni) {
        try { uni = await api.universities.detail(uid) as any } catch {}
      }
      if (uni) {
        selectedUni.value = { id: uni.id, name: uni.name }
        if (!isAuthed()) { 
          localStorage.setItem('pendingApply', JSON.stringify({
            university_id: uid,
            course_id: cid
          }))
          showAuth.value = true
          pendingApply = true
        }
        else { 
          showApply.value = true
          if (cid) {
            setTimeout(() => {
              const ev = new CustomEvent('prefill-course', { detail: { courseId: cid } })
              window.dispatchEvent(ev)
            }, 100)
          }
        }
      }
    }
  }
})
</script>

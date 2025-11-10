<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">{{ t('student.applications.title') }}</h3>
      <Button @click="$router.push('/')" variant="primary" size="sm">{{ t('student.applications.newButton') }}</Button>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-sm text-muted">{{ t('student.applications.loading') }}</div>
    </div>

    <div v-else-if="applications.length === 0" class="flex flex-col items-center justify-center py-12">
      <div class="text-sm text-muted mb-4">{{ t('student.dashboard.noRecentApplications') }}</div>
      <Button @click="$router.push('/')" variant="primary">{{ t('common.browseCourses') }}</Button>
    </div>

    <div v-else class="grid gap-4">
      <Card v-for="app in applications" :key="app.id" class="cursor-pointer hover:shadow-md transition-shadow" @click="viewApplication(app.id)">
        <template #header>
          <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex-1">
              <div class="font-medium">{{ t('staff.assignments.applicationLabel', { id: app.id }) }}</div>
              <div class="text-xs text-muted mt-1">
                {{ app.university?.name || t('common.unknown') }} • {{ app.course?.name || t('common.unknown') }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(app.status)]">
                {{ getStatusLabel(app.status) }}
              </span>
              <span v-if="app.status !== 'draft'" class="px-2 py-1 rounded text-xs font-medium bg-primary/10 text-primary">
                {{ t('staff.assignments.stage', { current: app.stage_index }) }}
              </span>
            </div>
          </div>
        </template>
        <template #default>
          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
              <span class="text-muted">{{ t('student.applications.passport') }}</span>
              <span>{{ app.passport_no }}</span>
            </div>
            <div v-if="app.staff" class="flex items-center gap-2">
              <span class="text-muted">{{ t('student.applications.assignedStaff') }}</span>
              <span>{{ app.staff?.name || t('student.applications.notAssigned') }}</span>
            </div>
            <div v-if="getCurrentStage(app)" class="flex items-center gap-2 mt-3">
              <span class="text-muted">{{ t('student.applications.currentStage') }}</span>
              <span :class="getCurrentStage(app) ? getStageStatusClass(getCurrentStage(app)!.status) : ''">
                {{ getStageLabel(getCurrentStage(app)) }}
              </span>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import { getStatusBadgeClass } from '@/utils/applicationStatuses'
import { useI18n } from 'vue-i18n'

type Application = {
  id: number
  university_id: number
  course_id: number
  passport_no: string
  status: string
  stage_index: number
  assigned_staff_id?: number
  university?: { id: number; name: string }
  course?: { id: number; name: string }
  staff?: { id: number; name: string }
  stages?: Array<{ stage_key: string; status: string }>
}

const applications = ref<Application[]>([])
const loading = ref(true)
const router = useRouter()
const toast = useToast()
const { t } = useI18n()

async function loadApplications() {
  loading.value = true
  try {
    const response = await api.applications.list() as any
    applications.value = (response.data || response || []).map((app: any) => ({
      ...app,
      stages: app.stages || []
    }))
  } catch (e: any) {
    toast.error(e?.message || (t('toasts.failedApplications') as string))
    console.error(e)
  } finally {
    loading.value = false
  }
}

function viewApplication(id: number) {
  router.push(`/dashboard/applications/${id}`)
}

function getCurrentStage(app: Application) {
  if (!app.stages || app.stages.length === 0) return null
  
  for (const stage of app.stages) {
    if (stage.status !== 'locked') {
      return {
        key: stage.stage_key,
        label: (t(`stage.labels.${stage.stage_key}`) as string) || stage.stage_key,
        status: stage.status
      }
    }
  }
  return null
}

function getStageLabel(stage: { key: string; label: string; status: string } | null) {
  if (!stage) return '—'
  const statusLabel = (t(`stage.statuses.${stage.status}`) as string) || stage.status
  return `${stage.label} (${statusLabel})`
}

function getStageStatusClass(status: string) {
  const classes: Record<string, string> = {
    'draft': 'text-muted',
    'submitted': 'text-primary',
    'approved': 'text-success',
    'rejected': 'text-danger',
    'locked': 'text-muted'
  }
  return classes[status] || 'text-muted'
}

function getStatusLabel(status: string): string {
  return (t(`statuses.${status}`) as string) || status
}

onMounted(loadApplications)
</script>

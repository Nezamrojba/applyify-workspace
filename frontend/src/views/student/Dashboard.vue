<template>
  <div class="stack">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-semibold">{{ t('student.dashboard.title') }}</h2>
      <Button @click="loadData" :disabled="loading" variant="ghost" size="sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" :class="['h-4 w-4', loading ? 'animate-spin' : '']">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        {{ t('common.refresh') }}
      </Button>
    </div>

    <div v-if="loading && !dashboard.total_applications" class="flex items-center justify-center py-12">
      <div class="text-sm text-muted">{{ t('student.dashboard.loading') }}</div>
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <Card :class="['bg-gradient-to-br from-primary/5 to-primary/10 border-primary/20', applicationsEnabled ? 'cursor-pointer hover:shadow-md transition-shadow' : 'opacity-50 cursor-not-allowed']" @click="applicationsEnabled && $router.push('/dashboard/applications')">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('student.dashboard.totalApplications') }}</div>
          </template>
          <div class="text-3xl font-bold text-primary">{{ dashboard.total_applications ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">
            {{ applicationsEnabled ? t('student.dashboard.activeCount', { count: dashboard.active_applications ?? 0 }) : t('student.dashboard.moduleDisabled') }}
          </div>
        </Card>

        <Card class="bg-gradient-to-br from-warning/5 to-warning/10 border-warning/20">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('student.dashboard.pendingDocuments') }}</div>
          </template>
          <div class="text-3xl font-bold text-warning">{{ dashboard.pending_documents ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ t('student.dashboard.pendingDocumentsDescription') }}</div>
        </Card>

        <Card class="bg-gradient-to-br from-info/5 to-info/10 border-info/20">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('student.dashboard.uploadedDocuments') }}</div>
          </template>
          <div class="text-3xl font-bold text-info">{{ dashboard.uploaded_documents ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ t('student.dashboard.uploadedDocumentsDescription') }}</div>
        </Card>

        <Card :class="['bg-gradient-to-br from-success/5 to-success/10 border-success/20', applicationsEnabled ? 'cursor-pointer hover:shadow-md transition-shadow' : 'opacity-50 cursor-not-allowed']" @click="applicationsEnabled && $router.push('/dashboard/applications')">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('student.dashboard.unreadMessages') }}</div>
          </template>
          <div class="text-3xl font-bold text-success">{{ dashboard.unread_messages ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ t('student.dashboard.unreadMessagesDescription') }}</div>
        </Card>
      </div>

      <Card v-if="applicationsEnabled">
        <template #header>
          <div class="flex items-center justify-between">
            <div class="text-lg font-semibold">{{ t('student.dashboard.applicationsByStatus') }}</div>
          </div>
        </template>
        <div class="space-y-3">
          <div v-for="(count, status) in dashboard.applications_by_status" :key="status" class="flex items-center justify-between p-3 rounded-lg bg-surface border border-black/5">
            <div class="flex items-center gap-3">
              <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(status)]">
                {{ getStatusLabel(status) }}
              </span>
            </div>
            <span class="text-lg font-semibold">{{ count }}</span>
          </div>
          <div v-if="!dashboard.applications_by_status || Object.keys(dashboard.applications_by_status).length === 0" class="text-sm text-muted text-center py-4">
            <span>{{ t('student.dashboard.noApplicationsYet') }}</span>
            <router-link to="/" class="text-primary hover:underline">{{ t('student.dashboard.browseCoursesCta') }}</router-link>
          </div>
        </div>
      </Card>

      <Card v-if="applicationsEnabled">
        <template #header>
          <div class="flex items-center justify-between">
            <div class="text-lg font-semibold">{{ t('student.dashboard.recentApplications') }}</div>
            <Button @click="$router.push('/dashboard/applications')" variant="ghost" size="sm">{{ t('common.viewAll') }}</Button>
          </div>
        </template>
        <div v-if="dashboard.recent_applications && dashboard.recent_applications.length > 0" class="space-y-3">
          <div
            v-for="app in dashboard.recent_applications"
            :key="app.id"
            class="p-4 rounded-lg border border-black/5 hover:border-primary/30 hover:shadow-md transition-all cursor-pointer"
            @click="applicationsEnabled && $router.push(`/dashboard/applications/${app.id}`)"
            :class="applicationsEnabled ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="font-semibold mb-1">{{ t('staff.assignments.applicationLabel', { id: app.id }) }}</div>
                <div class="text-sm text-muted mb-2">
                  {{ app.university?.name || t('common.unknown') }} • {{ app.course?.name || t('common.unknown') }}
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                  <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(app.status)]">
                    {{ getStatusLabel(app.status) }}
                  </span>
                  <span v-if="app.stage_index" class="text-xs text-muted">
                    {{ t('staff.assignments.stage', { current: app.stage_index }) }}
                  </span>
                  <span v-if="app.staff" class="text-xs text-muted">
                    {{ t('common.staff') }}: {{ app.staff.name }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" @click.stop="$router.push(`/dashboard/applications/${app.id}`)">
                  {{ t('common.view') }}
                </Button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <div class="text-sm text-muted mb-4">{{ t('student.dashboard.noRecentApplications') }}</div>
          <Button @click="$router.push('/')" variant="primary" :disabled="!isModuleEnabled('universities') || !isModuleEnabled('courses')">
            {{ t('common.browseCourses') }}
          </Button>
        </div>
      </Card>

      <Card v-if="applicationsEnabled && dashboard.pending_documents > 0" class="bg-warning/5 border-warning/20">
        <template #header>
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-warning">
              <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
            </svg>
            <div class="text-lg font-semibold text-warning">{{ t('common.actionRequired') }}</div>
          </div>
        </template>
        <div class="text-sm text-muted" v-html="t('student.dashboard.pendingDocumentsCard', { count: dashboard.pending_documents })"></div>
        <div class="mt-4">
          <Button @click="$router.push('/dashboard/applications')" variant="primary" size="sm" :disabled="!applicationsEnabled">
            {{ t('common.reviewApplications') }}
          </Button>
        </div>
    </Card>
    </div>
  </div>
  </template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'
import { useModuleStatus } from '@/composables/useModuleStatus'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const dashboard = ref<any>({})
const loading = ref(false)
const { isModuleEnabled } = useModuleStatus()
const { t } = useI18n()

const applicationsEnabled = computed(() => isModuleEnabled('applications'))

async function loadData() {
  loading.value = true
  try {
    dashboard.value = await api.student.dashboard() as any
  } catch (e: any) {
    console.error('Failed to load dashboard', e)
  } finally {
    loading.value = false
  }
}

function getStatusLabel(status: string): string {
  return (t(`statuses.${status}`) as string) || status
}

function getStatusBadgeClass(status: string): string {
  const classes: Record<string, string> = {
    draft: 'bg-muted/10 text-muted',
    active: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    arriving: 'bg-info/10 text-info',
    soft_deleted: 'bg-danger/10 text-danger'
  }
  return classes[status] || 'bg-muted/10 text-muted'
}

onMounted(() => {
  loadData()
})
</script>

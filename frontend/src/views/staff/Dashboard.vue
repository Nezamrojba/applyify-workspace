<template>
  <div class="stack">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 sm:mb-6">
    <h2 class="text-xl sm:text-2xl font-semibold">{{ t('staff.dashboard.title') }}</h2>
      <Button @click="loadData" :disabled="loading" variant="ghost" size="sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" :class="['h-4 w-4', loading ? 'animate-spin' : '']">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        {{ t('common.refresh') }}
      </Button>
    </div>

    <div v-if="loading && !dashboard.total_applications" class="flex items-center justify-center py-12">
      <div class="text-sm text-muted">{{ t('staff.dashboard.loading') }}</div>
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <Card :class="['bg-gradient-to-br from-primary/5 to-primary/10 border-primary/20', applicationsEnabled ? 'cursor-pointer hover:shadow-md transition-shadow' : 'opacity-50 cursor-not-allowed']" @click="applicationsEnabled && $router.push('/staff/assignments')">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('staff.dashboard.totalAssignments') }}</div>
          </template>
          <div class="text-3xl font-bold text-primary">{{ dashboard.total_applications ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ applicationsEnabled ? t('staff.dashboard.totalAssignmentsHint') : t('staff.dashboard.moduleDisabled') }}</div>
        </Card>

        <Card class="bg-gradient-to-br from-warning/5 to-warning/10 border-warning/20">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('staff.dashboard.pendingReviews') }}</div>
          </template>
          <div class="text-3xl font-bold text-warning">{{ dashboard.pending_reviews ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ t('staff.dashboard.pendingReviewsDescription') }}</div>
        </Card>

        <Card :class="['bg-gradient-to-br from-info/5 to-info/10 border-info/20', applicationsEnabled ? 'cursor-pointer hover:shadow-md transition-shadow' : 'opacity-50 cursor-not-allowed']" @click="applicationsEnabled && $router.push('/staff/assignments')">
          <template #header>
            <div class="text-sm text-muted font-medium">{{ t('staff.dashboard.unreadMessages') }}</div>
          </template>
          <div class="text-3xl font-bold text-info">{{ dashboard.unread_messages ?? 0 }}</div>
          <div class="text-xs text-muted mt-1">{{ t('staff.dashboard.unreadMessagesDescription') }}</div>
        </Card>

      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <Card v-if="applicationsEnabled">
          <template #header>
            <div class="flex items-center justify-between">
              <div class="text-lg font-semibold">{{ t('staff.dashboard.applicationsByStatus') }}</div>
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
              {{ t('staff.dashboard.noApplicationsAssigned') }}
            </div>
          </div>
        </Card>

      </div>

      <Card v-if="applicationsEnabled">
        <template #header>
          <div class="flex items-center justify-between">
            <div class="text-lg font-semibold">{{ t('staff.dashboard.recentAssignments') }}</div>
            <Button @click="$router.push('/staff/assignments')" variant="ghost" size="sm" :disabled="!applicationsEnabled">{{ t('common.viewAll') }}</Button>
          </div>
        </template>
        <div v-if="dashboard.recent_applications && dashboard.recent_applications.length > 0" class="space-y-3">
          <div
            v-for="app in dashboard.recent_applications"
            :key="app.id"
            class="p-4 rounded-lg border border-black/5 hover:border-primary/30 hover:shadow-md transition-all cursor-pointer"
            @click="applicationsEnabled && $router.push(`/staff/assignments/${app.id}`)"
            :class="applicationsEnabled ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="font-semibold mb-1">{{ t('staff.assignments.applicationLabel', { id: app.id }) }}</div>
                <div class="text-sm text-muted mb-2">
                  {{ app.student?.name || t('common.student') }} • {{ app.university?.name || t('common.unknown') }} • {{ app.course?.name || t('common.unknown') }}
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                  <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(app.status)]">
                    {{ getStatusLabel(app.status) }}
                  </span>
                  <span v-if="app.stage_index" class="text-xs text-muted">
                    {{ t('staff.assignments.stage', { current: app.stage_index }) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-sm text-muted text-center py-8">
          {{ t('staff.dashboard.noRecentAssignments') }}
        </div>
      </Card>
      <Card v-else class="border-warning/20 bg-warning/5">
        <div class="text-center py-8">
          <div class="text-sm text-muted">{{ t('staff.dashboard.moduleDisabled') }}</div>
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
    dashboard.value = await api.staff.dashboard() as any
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

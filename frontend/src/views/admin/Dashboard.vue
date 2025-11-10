<template>
  <div class="stack">
    <h2 class="text-2xl font-semibold">Admin Dashboard</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card>
        <template #header><div class="text-sm text-muted">Active Applications</div></template>
        <div class="text-2xl font-semibold">{{ metrics.active_applications ?? '—' }}</div>
      </Card>
      <Card>
        <template #header><div class="text-sm text-muted">Avg Assignment Time (s)</div></template>
        <div class="text-2xl font-semibold">{{ metrics.avg_assignment_seconds ?? '—' }}</div>
      </Card>
      <Card>
        <template #header><div class="text-sm text-muted">Storage Used</div></template>
        <div class="text-2xl font-semibold">{{ metrics.storage_used_mb ?? '—' }} MB</div>
      </Card>
      <Card>
        <template #header><div class="text-sm text-muted">Staff With Max Load</div></template>
        <div class="text-2xl font-semibold">{{ metrics.staff_max_load ?? '—' }}</div>
      </Card>
    </div>

    <Card>
      <template #header><div class="flex items-center justify-between"><div class="text-sm text-muted">Modules & Limits</div></div></template>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
        <div v-for="(v,k) in flatSettings" :key="k" class="flex items-center justify-between p-3 rounded-lg bg-surface border border-black/5">
          <span class="text-muted">{{ k }}</span>
          <span class="font-medium">{{ formatSetting(v) }}</span>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import Card from '@/components/ui/Card.vue'
import { api } from '@/services/api'

const metrics = ref<Record<string, any>>({})
const settings = ref<Record<string, any>>({})

onMounted(async () => {
  try { metrics.value = await api.admin.metrics() as any } catch {}
  try { 
    const r = await api.admin.settings.list() as any
    settings.value = r?.data || r || {}
  } catch {}
})

const flatSettings = computed<Record<string, any>>(() => {
  return settings.value || {}
})

function formatSetting(v: any){
  if (typeof v === 'boolean') return v ? 'On' : 'Off'
  if (typeof v === 'object') return JSON.stringify(v)
  return String(v)
}
</script>

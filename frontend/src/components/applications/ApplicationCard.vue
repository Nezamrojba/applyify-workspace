<template>
  <Card>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="font-medium">{{$t('nav.applications')}} #{{ app.id }}</div>
        <span class="text-xs px-2 py-1 rounded bg-white/5">{{$t('applications.stage')}} {{ app.stage_index }}</span>
      </div>
    </template>
    <template #default>
      <div class="text-sm text-muted">{{$t('applications.status')}}: {{ app.status }}</div>
    </template>
    <template #footer>
      <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="file" class="hidden" @change="onFile" />
        <span class="text-primary">{{$t('applications.uploadStage1')}}</span>
      </label>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from '@/components/ui/Card.vue'
import { api } from '@/services/api'
import { useApplicationStore } from '@/stores/applications'
const props = defineProps<{ app: { id:number; stage_index:number; status:string } }>()
const apps = useApplicationStore()
async function onFile(e: Event) {
  const input = e.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return
  const file = input.files[0]
  const up = await api.uploads.local(file)
  await apps.uploadDoc(props.app.id, { stage_key: 'stage1', doc_type: 'high_school_cert', file_url: up.file_url, file_type: up.file_type, size_bytes: up.size_bytes })
  await apps.fetch()
}
</script>


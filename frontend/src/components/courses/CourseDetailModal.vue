<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h3 class="text-xl font-semibold">{{ courseName }}</h3>
      <div class="text-sm text-muted">{{ info }}</div>
      <div class="grid md:grid-cols-2 gap-3 text-sm">
        <div><span class="text-muted">University:</span> {{ university?.name }}</div>
        <div><span class="text-muted">Level:</span> {{ course?.level }}</div>
        <div><span class="text-muted">Duration:</span> {{ course?.duration_months }} months</div>
        <div><span class="text-muted">Acceptance:</span> {{ university?.acceptance_percent ?? '-' }}%</div>
      </div>
      <div class="text-sm">{{ about }}</div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import { api } from '@/services/api'
import { useI18n } from 'vue-i18n'

const props = defineProps<{ open: boolean; course?: any; universityId?: number }>()
const university = ref<any>(null)
const { locale } = useI18n()
const info = 'Great for ambitious students seeking global-ready skills.'
const about = 'Our partners provide supportive campuses, career-focused programs, and vibrant student life.'

const courseName = computed(() => {
  const i18nName = props.course?.i18n?.name
  if (!i18nName) return props.course?.name || '—'
  const l = locale.value as 'en'|'ar'
  if (typeof i18nName === 'string') return i18nName
  return i18nName?.[l] || i18nName?.en || props.course?.name || '—'
})

watch(() => props.universityId, async (v) => {
  if (!v) { university.value = null; return }
  university.value = await api.universities.detail(v) as any
})
</script>


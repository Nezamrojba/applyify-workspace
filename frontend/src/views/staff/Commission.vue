<template>
  <div class="stack">
    <h3 class="text-xl font-semibold">{{ t('staff.commission.title') }}</h3>
    <Card>
      <div class="text-sm text-muted">{{ t('staff.commission.description') }}</div>
    </Card>
  </div>
</template>
<script setup lang="ts">
import Card from '@/components/ui/Card.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>


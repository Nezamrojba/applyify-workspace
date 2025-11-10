<template>
  <Card>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="font-medium">{{ name }}</div>
        <span class="text-xs px-2 py-1 rounded bg-white/5">{{ acceptance || '-' }}%</span>
      </div>
    </template>
    <template #default>
      <div class="text-sm text-muted">ID {{ id }}</div>
    </template>
    <template #footer>
      <button class="text-primary" @click="$emit('apply', { id, name })">{{$t('universities.apply')}}</button>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from '@/components/ui/Card.vue'
defineProps<{ id: number; name: string; acceptance?: number }>()
defineEmits(['apply'])
</script>

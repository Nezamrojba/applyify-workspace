<template>
  <div class="flex items-center gap-2">
    <select v-model="model" class="h-8 px-2 rounded border border-black/10 bg-white text-sm">
      <option disabled value="">Assign role</option>
      <option value="super_admin">super_admin</option>
      <option value="staff">staff</option>
    </select>
    <Button size="sm" @click="$emit('assign', model)">Add</Button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{ value?: string }>()
const emit = defineEmits(['update:value','assign'])
const model = ref(props.value || '')
watch(model, v => emit('update:value', v))
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="text-sm font-medium mb-2 block">{{ label }}</label>
    <input
      v-bind="$attrs"
      :value="modelValue == null ? '' : String(modelValue)"
      @input="handleInput"
      :type="type"
      :disabled="disabled"
      :aria-invalid="!!error"
      class="w-full bg-white rounded-lg h-10 px-3 text-text placeholder:text-muted/70 focus:outline-none focus:ring-2 focus:border-primary/40"
      :class="[inputClass, disabled ? 'bg-muted cursor-not-allowed opacity-60' : '']"
    />
    <p v-if="error" class="mt-1 text-xs text-danger">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-xs text-muted">{{ hint }}</p>
  </div>
  </template>

<script setup lang="ts">
import { computed } from 'vue'
const props = defineProps<{ modelValue?: string | number; type?: string; error?: string; hint?: string; valid?: boolean; label?: string; disabled?: boolean }>()
const emit = defineEmits(['update:modelValue'])

const inputClass = computed(() => props.error ? 'border border-danger/60 focus:ring-danger/30' : 'border border-[#eeeeee] focus:ring-primary/30')

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement
  // For number inputs, emit the string value and let v-model.number handle conversion
  // This ensures compatibility with both v-model and v-model.number
  emit('update:modelValue', target.value)
}
</script>

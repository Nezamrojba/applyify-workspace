<template>
  <div class="flex flex-wrap items-center gap-2">
    <template v-for="f in schema" :key="f.key">
      <Input
        v-if="f.type==='search'"
        :placeholder="f.placeholder || 'Search'"
        class="w-full sm:w-64 md:w-72"
        :model-value="model[f.key] as any"
        @update:model-value="onSearch(f.key, $event)"
      />
      <select
        v-else-if="f.type==='select'"
        class="h-9 px-3 rounded-lg border border-black/10 bg-white text-sm"
        :value="model[f.key] as any || ''"
        @change="onSelect(f.key, ($event.target as HTMLSelectElement).value)"
      >
        <option :value="''">{{ f.placeholder || 'All' }}</option>
        <option v-for="opt in (f.options||[])" :value="opt.value">{{ opt.label }}</option>
      </select>
    </template>
    <Button size="sm" variant="ghost" @click="reset">Reset</Button>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { useDebounce } from '@/composables/useDebounce'

type SelectOpt = { value: string; label: string }
type FilterDef = { key: string; type: 'search'|'select'; placeholder?: string; options?: SelectOpt[] }

const props = defineProps<{ modelValue: Record<string, any>, schema: FilterDef[] }>()
const emit = defineEmits(['update:modelValue','change'])

const model = reactive({ ...(props.modelValue || {}) })
watch(() => props.modelValue, (v) => Object.assign(model, v || {}))

const debouncedChange = useDebounce(() => emit('change', { ...model }), 300)

function onSearch(key: string, val: string){
  ;(model as any)[key] = val
  emit('update:modelValue', { ...model })
  debouncedChange()
}
function onSelect(key: string, val: string){
  ;(model as any)[key] = val
  emit('update:modelValue', { ...model })
  emit('change', { ...model })
}
function reset(){
  for (const k of Object.keys(model)) (model as any)[k] = ''
  emit('update:modelValue', { ...model })
  emit('change', { ...model })
}
</script>

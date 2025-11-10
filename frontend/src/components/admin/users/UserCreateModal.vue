<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h4 class="text-lg font-semibold">Create User</h4>
      <Input v-model="form.name" placeholder="Full name" :error="fieldError('name')" />
      <Input v-model="form.email" type="email" placeholder="Email" :error="fieldError('email')" />
      <Input v-model="form.password" type="password" placeholder="Password" :error="fieldError('password')" />
      <Input v-model="form.whatsapp" placeholder="WhatsApp" :error="fieldError('whatsapp')" />
      <div>
        <select v-model="form.role" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full">
          <option disabled value="">Role</option>
          <option value="super_admin">Super Admin</option>
          <option value="staff">Staff</option>
        </select>
        <p v-if="fieldError('role')" class="mt-1 text-xs text-danger">{{ fieldError('role') }}</p>
      </div>
      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
      <div class="flex justify-end gap-2">
        <!-- Cancel button removed - modals cannot be closed via close button -->
        <Button :disabled="submitting" @click="handleSubmit">Create</Button>
      </div>
    </div>
  </Modal>
  </template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{ open: boolean; errors?: Record<string, any>; submitting?: boolean }>()
const emit = defineEmits(['close','submit'])
const form = ref<{ name:string; email:string; password:string; whatsapp?:string; role?:string }>({ name:'', email:'', password:'', whatsapp:'', role:'' })
const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')
function fieldError(key: string){
  const e = (props.errors as any)?.errors?.[key] || (props.errors as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

watch(() => props.open, (isOpen) => {
  if (!isOpen) {
    form.value = { name:'', email:'', password:'', whatsapp:'', role:'' }
  }
})

function handleSubmit() {
  const payload = JSON.parse(JSON.stringify(form.value))
  emit('submit', payload)
}
</script>

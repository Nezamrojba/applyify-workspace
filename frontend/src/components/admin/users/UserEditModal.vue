<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h4 class="text-lg font-semibold">Edit User</h4>
      <Input v-model="form.name" placeholder="Full name" :error="fieldError('name')" />
      <Input v-model="form.email" type="email" placeholder="Email" :error="fieldError('email')" />
      <Input v-model="form.password" type="password" placeholder="Password (leave blank to keep current)" :error="fieldError('password')" />
      <Input v-model="form.whatsapp" placeholder="WhatsApp" :error="fieldError('whatsapp')" />
      <Input v-model="form.passport_no" placeholder="Passport number" :error="fieldError('passport_no')" />
      <div>
        <select v-model="form.role" class="h-10 px-3 rounded-lg border border-black/10 bg-white w-full" :disabled="isSuperAdmin">
          <option disabled value="">Role</option>
          <option value="super_admin">Super Admin</option>
          <option value="staff">Staff</option>
          <option value="student">Student</option>
        </select>
        <p v-if="fieldError('role')" class="mt-1 text-xs text-danger">{{ fieldError('role') }}</p>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.is_active" class="rounded border-black/20" />
        <span class="text-sm">Active</span>
      </label>
      <p v-if="generalError" class="text-xs text-danger">{{ generalError }}</p>
      <div class="flex justify-end gap-2">
        <!-- Cancel button removed - modals cannot be closed via close button -->
        <Button :disabled="submitting" @click="handleSubmit">Save</Button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{ 
  open: boolean
  user?: { id: number; name: string; email: string; whatsapp?: string; passport_no?: string; role: string; is_active: boolean }
  errors?: Record<string, any>
  submitting?: boolean 
}>()

const emit = defineEmits(['close','submit'])

const form = ref<{ name: string; email: string; password: string; whatsapp: string; passport_no: string; role: string; is_active: boolean }>({ 
  name: '', 
  email: '', 
  password: '', 
  whatsapp: '', 
  passport_no: '', 
  role: '', 
  is_active: true 
})

watch(() => props.user, (u) => {
  if (u) {
    form.value = {
      name: u.name || '',
      email: u.email || '',
      password: '',
      whatsapp: u.whatsapp || '',
      passport_no: u.passport_no || '',
      role: u.role || '',
      is_active: u.is_active ?? true
    }
  } else {
    form.value = {
      name: '',
      email: '',
      password: '',
      whatsapp: '',
      passport_no: '',
      role: '',
      is_active: true
    }
  }
}, { immediate: true })

const isSuperAdmin = computed(() => props.user?.role === 'super_admin')
const generalError = computed(() => (props.errors as any)?.message || (props.errors as any)?.error || '')

function fieldError(key: string){
  const e = (props.errors as any)?.errors?.[key] || (props.errors as any)?.[key]
  if (!e) return ''
  return Array.isArray(e) ? e[0] : String(e)
}

watch(() => props.open, (isOpen) => {
  if (!isOpen) {
    form.value.password = ''
  }
})

function handleSubmit() {
  const payload = JSON.parse(JSON.stringify(form.value))
  if (!payload.password) {
    delete payload.password
  }
  emit('submit', payload)
}
</script>

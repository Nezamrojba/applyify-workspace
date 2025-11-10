<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <div class="flex gap-2">
        <button :class="tabClass('login')" @click="tab='login'">{{$t('auth.loginTitle')}}</button>
        <button :class="tabClass('register')" @click="tab='register'">{{$t('auth.createAccountTitle')}}</button>
      </div>
      <LoginForm v-if="tab==='login'" :redirect="false" @success="(data) => onSuccess(data)" />
      <RegisterForm v-else :redirect="false" @success="(data) => onSuccess(data)" />
    </div>
  </Modal>
  </template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Modal from '@/components/ui/Modal.vue'
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'

defineProps<{ open: boolean }>()
const emit = defineEmits(['close','authed','openApply'])
const tab = ref<'login'|'register'>('login')
const router = useRouter()
const auth = useAuthStore()
function tabClass(k: 'login'|'register'){
  return `px-3 py-1 rounded-lg ${tab.value===k ? 'bg-primary text-white' : 'bg-surface text-text'}`
}
function onSuccess(data?: { openApply?: boolean }){
  emit('authed')
  const role = auth.user?.role
  if (role === 'super_admin') {
    router.push('/admin')
  } else if (role === 'staff') {
    router.push('/staff')
  } else if (role === 'student' && tab.value === 'register') {
    // For students who just registered, always emit event to open course selection/apply modal
    // This allows them to choose a course immediately after creating their account
    emit('openApply')
  }
}
</script>

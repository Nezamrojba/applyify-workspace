<template>
  <form class="space-y-4" @submit.prevent="onSubmit">
    <Input v-model="name" :placeholder="$t('fields.name') as string" :error="(touched.name || submitted) ? errors.name : undefined" @blur="touched.name = true" />
    <Input v-model="email" type="email" :placeholder="$t('fields.email') as string" :error="(touched.email || submitted) ? errors.email : undefined" @blur="touched.email = true" />
    <Input v-model="whatsapp" :placeholder="$t('fields.whatsapp') as string" :error="(touched.whatsapp || submitted) ? errors.whatsapp : undefined" @blur="touched.whatsapp = true" />
    <Input v-model="passport" :placeholder="$t('fields.passport') as string" :error="(touched.passport || submitted) ? errors.passport : undefined" @blur="touched.passport = true" />
    <Input v-model="password" type="password" :placeholder="$t('fields.password') as string" :error="(touched.password || submitted) ? errors.password : undefined" @blur="touched.password = true" />
    <Input v-model="password2" type="password" :placeholder="$t('fields.confirmPassword') as string" :error="(touched.password2 || submitted) ? errors.password2 : undefined" @blur="touched.password2 = true" />
    <p v-if="errors.server" class="text-xs text-danger">{{ errors.server }}</p>
    <Button class="w-full" :disabled="submitting || invalid">{{$t('auth.createAccountTitle')}}</Button>
  </form>
  </template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useApplicationStore } from '@/stores/applications'
import { api } from '@/services/api'
import { useToast } from '@/composables/useToast'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const props = defineProps<{ redirect?: boolean; pendingApplication?: { university_id: number; course_id: number } | null }>()
const emit = defineEmits(['success', 'applicationCreated'])
const name = ref('')
const email = ref('')
const whatsapp = ref('')
const passport = ref('')
const password = ref('')
const password2 = ref('')
const auth = useAuthStore()
const errors = ref<Record<string,string|undefined>>({})
const submitting = ref(false)
const { t } = useI18n()
const submitted = ref(false)
const touched = ref<{name:boolean;email:boolean;whatsapp:boolean;passport:boolean;password:boolean;password2:boolean}>({name:false,email:false,whatsapp:false,passport:false,password:false,password2:false})

function validate(){
  errors.value = {}
  if (!name.value) errors.value.name = t('errors.required') as string
  if (!email.value) errors.value.email = t('errors.required') as string
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errors.value.email = t('errors.email') as string
  if (!whatsapp.value) errors.value.whatsapp = t('errors.required') as string
  else if (!/^\+?[0-9\s-]{8,}$/.test(whatsapp.value)) errors.value.whatsapp = t('errors.phone') as string
  if (!passport.value) errors.value.passport = t('errors.required') as string
  if (!password.value) errors.value.password = t('errors.required') as string
  else if (password.value.length < 8) errors.value.password = t('errors.min8') as string
  if (!password2.value) errors.value.password2 = t('errors.required') as string
  else if (password2.value !== password.value) errors.value.password2 = t('errors.passwordMismatch') as string
}

watch([name,email,whatsapp,passport,password,password2], validate, { immediate: true })
const invalid = computed(() => Object.values(errors.value).some(Boolean))

async function onSubmit() {
  validate()
  submitted.value = true
  if (invalid.value) return
  errors.value.server = undefined
  submitting.value = true
  try{
    await auth.register({ name: name.value, email: email.value, password: password.value, password_confirmation: password2.value, whatsapp: whatsapp.value, passport_no: passport.value })
    await auth.me()
    useToast().success('Account created')
    
    const pendingApplyData = props.pendingApplication || (() => {
      try {
        const stored = localStorage.getItem('pendingApply')
        if (stored) {
          const data = JSON.parse(stored)
          localStorage.removeItem('pendingApply')
          return data
        }
      } catch {}
      return null
    })()
    
    const passportNo = passport.value
    const uniId = pendingApplyData?.university_id
    const courseId = pendingApplyData?.course_id
    
    // If there's pending apply data, store it and emit event to open apply modal
    if (uniId && courseId && passportNo) {
      // Store pending application data for the apply modal
      localStorage.setItem('pendingApply', JSON.stringify({
        university_id: uniId,
        course_id: courseId
      }))
    }
    
    // Always emit openApply for students - if no pending data, show course selection modal
    // Emit success to close auth modal and open apply modal
    emit('success', { openApply: true })
    
    if (props.redirect !== false) {
      // Don't redirect immediately - let the modal handle navigation
      // The modal will be opened by the parent component
    }
  }catch(e:any){
    errors.value.server = e?.message || (t('errors.tryAgain') as string)
    useToast().error(errors.value.server)
  }finally{
    submitting.value = false
  }
}
</script>

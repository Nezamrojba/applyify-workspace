<template>
  <form class="space-y-4" @submit.prevent="onSubmit">
    <Input
      v-model="email"
      type="email"
      :placeholder="$t('fields.email') as string"
      :error="(touched.email || submitted) ? errors.email : undefined"
      @blur="touched.email = true"
    />
    <Input
      v-model="password"
      type="password"
      :placeholder="$t('fields.password') as string"
      :error="(touched.password || submitted) ? errors.password : undefined"
      @blur="touched.password = true"
    />
    <p v-if="errors.server" class="text-xs text-danger">{{ errors.server }}</p>
    <Button class="w-full" :disabled="submitting || invalid">{{$t('auth.loginTitle')}}</Button>
  </form>
  </template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const props = defineProps<{ redirect?: boolean }>()
const emit = defineEmits(['success'])
const email = ref('')
const password = ref('')
const auth = useAuthStore()
const submitting = ref(false)
const submitted = ref(false)
const touched = ref<{ email: boolean; password: boolean }>({ email: false, password: false })
const errors = ref<{ email?: string; password?: string; server?: string }>({})
const { t } = useI18n()

function validate() {
  errors.value = {}
  if (!email.value) errors.value.email = t('errors.required') as string
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errors.value.email = t('errors.email') as string
  if (!password.value) errors.value.password = t('errors.required') as string
}

watch([email, password], validate, { immediate: true })
const invalid = computed(() => Object.keys(errors.value).some(k => (errors.value as any)[k]))

async function onSubmit() {
  validate()
  submitted.value = true
  if (invalid.value) return
  errors.value.server = undefined
  submitting.value = true
  try {
    await auth.login(email.value, password.value)
    await auth.me()
    useToast().success('Welcome back')
    if (props.redirect !== false) {
      if (auth.user?.role === 'super_admin') {
        location.href = '/admin'
      } else if (auth.user?.role === 'staff') {
        location.href = '/staff'
      } else if (auth.user?.role === 'student') {
        sessionStorage.setItem('student_visited_dashboard', 'true')
        location.href = '/dashboard'
      }
    } else emit('success')
  } catch (e: any) {
    errors.value.server = e?.message || (t('errors.tryAgain') as string)
    useToast().error(errors.value.server)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <header class="border-b border-black/5 sticky top-0 backdrop-blur bg-white/80 z-50">
    <div class="w-full max-w-9xl mx-auto h-14 flex items-center justify-between px-4 lg:px-6">
      <router-link to="/" class="font-semibold">{{$t('app.title')}}</router-link>
      <nav class="flex items-center gap-3 relative">
        <LocaleSwitcher />
        <router-link to="/ask-us" class="text-sm text-muted transition-colors hover:text-primary">{{ t('header.ask') }}</router-link>
        <router-link v-if="isAdmin" to="/admin" class="text-sm text-primary">{{ t('header.admin') }}</router-link>
        <router-link v-else-if="isStaff" to="/staff" class="text-sm text-primary">{{ t('header.myAccount') }}</router-link>
        <router-link v-else-if="isStudent" to="/dashboard" class="text-sm text-primary">{{ t('header.myAccount') }}</router-link>
        <div v-if="isAuthed" class="flex items-center gap-2 relative">
          <NotificationsButton v-if="showNotifications" />
          <AccountMenu />
        </div>
        <Button v-else size="sm" @click="ui.openAuth()">{{$t('auth.loginTitle')}}</Button>
      </nav>
    </div>
  </header>
  </template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LocaleSwitcher from '@/components/LocaleSwitcher.vue'
import Button from '@/components/ui/Button.vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import NotificationsButton from '@/components/layout/NotificationsButton.vue'
import AccountMenu from '@/components/layout/AccountMenu.vue'
import { useModuleStatus } from '@/composables/useModuleStatus'

const auth = useAuthStore()
const ui = useUiStore()
const { isModuleEnabled } = useModuleStatus()
const { t } = useI18n()

const isAuthed = computed(() => !!auth.token)
const isAdmin = computed(() => !!auth.token && auth.user?.role === 'super_admin')
const isStaff = computed(() => !!auth.token && auth.user?.role === 'staff')
const isStudent = computed(() => !!auth.token && auth.user?.role === 'student')
const showNotifications = computed(() => isModuleEnabled('notifications'))

</script>

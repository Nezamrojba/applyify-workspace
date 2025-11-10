<template>
  <div class="min-h-screen flex flex-col app-bg">
    <AppHeader />
    <div v-if="isAccount" class="flex-1 pb-14">
      <router-view />
    </div>
    <main v-else class="flex-1 pb-14">
      <router-view />
    </main>
    <ToastContainer />
    <AppFooter />
    <AuthModal :open="ui.authOpen" @close="ui.closeAuth()" @authed="ui.closeAuth()" />
  </div>
  </template>

<script setup lang="ts">
import { computed } from 'vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import ToastContainer from '@/components/ui/ToastContainer.vue'
import { useUiStore } from '@/stores/ui'
import { useRoute } from 'vue-router'
const ui = useUiStore()
const route = useRoute()
const isAccount = computed(() => {
  const p = route.path
  return p.startsWith('/admin') || p.startsWith('/staff') || p.startsWith('/dashboard')
})
</script>

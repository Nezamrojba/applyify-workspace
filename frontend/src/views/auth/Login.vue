<template>
  <div class="container py-10 max-w-md">
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold">{{$t('auth.loginTitle')}}</h2>
      <LoginForm />
      <router-link to="/register" class="text-primary">{{$t('auth.newHere')}}</router-link>
    </div>
  </div>
  </template>

<script setup lang="ts">
import LoginForm from '@/components/auth/LoginForm.vue'
</script>

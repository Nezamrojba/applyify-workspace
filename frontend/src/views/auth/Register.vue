<template>
  <div class="container py-10 max-w-md">
    <div class="bg-white border border-[#f0f0f0] rounded-2xl shadow-lg p-6 stack">
      <div class="stack text-center">
        <h2 class="text-2xl font-semibold">{{$t('auth.createAccountTitle')}}</h2>
        <p class="text-muted text-sm">{{$t('auth.hintPassport')}}</p>
      </div>
      <RegisterForm />
    </div>
  </div>
</template>

<script setup lang="ts">
import RegisterForm from '@/components/auth/RegisterForm.vue'
</script>

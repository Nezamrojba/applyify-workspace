<template>
  <div class="rounded-2xl bg-white border border-black/5 shadow-sm transition-shadow hover:shadow-md">
    <div v-if="$slots.header" class="p-5 border-b border-black/5">
      <slot name="header" />
    </div>
    <div class="p-5">
      <slot />
    </div>
    <div v-if="$slots.footer" class="p-5 border-t border-black/5">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<template>
  <footer class="fixed bottom-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-t border-black/5">
    <div class="w-full max-w-7xl mx-auto h-12 flex items-center justify-between text-xs px-4 lg:px-6">
      <span class="text-muted">© {{ year }} Student Apply</span>
      <div class="flex items-center gap-3 text-muted">
        <a href="#" class="hover:text-primary">Privacy</a>
        <a href="#" class="hover:text-primary">Terms</a>
        <a href="#" class="hover:text-primary">Help</a>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
const year = new Date().getFullYear()
</script>

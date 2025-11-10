<template>
  <Modal :open="open" @close="$emit('close')">
    <div class="stack">
      <h4 class="text-lg font-semibold">{{ title }}</h4>
      <p class="text-sm text-muted">{{ message }}</p>
      <div class="flex justify-end gap-2 pt-2">
        <!-- Cancel button removed - modals cannot be closed via close button -->
        <Button :variant="variant || 'danger'" @click="$emit('confirm')">{{ confirmText || 'Delete' }}</Button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

defineProps<{ open: boolean; title?: string; message?: string; confirmText?: string; cancelText?: string; variant?: 'primary'|'ghost'|'danger' }>()
defineEmits(['close','confirm'])
</script>


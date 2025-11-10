<template>
  <div class="inline-block">
    <label class="inline-flex items-center gap-2 px-3 py-1.5 text-sm border border-primary/20 rounded-lg bg-primary/5 text-primary hover:bg-primary/10 cursor-pointer transition-colors">
      <input type="file" class="hidden" :accept="accept" @change="onFile" />
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
      </svg>
      <span>{{ label || 'Upload' }}</span>
    </label>
    
    <Modal :open="showErrorModal" @close="showErrorModal = false">
      <div class="stack">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-danger/10 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-danger">
              <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM9.53 8.47a.75.75 0 011.06 0L12 9.88l1.41-1.41a.75.75 0 111.06 1.06L13.06 10.94l1.41 1.41a.75.75 0 11-1.06 1.06L12 12l-1.47 1.41a.75.75 0 11-1.06-1.06l1.41-1.41-1.41-1.41a.75.75 0 010-1.06z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-text">File Too Large</h3>
            <p class="text-sm text-muted mt-1">The selected file exceeds the maximum allowed size.</p>
          </div>
        </div>
        
        <div class="mt-4 p-4 rounded-lg bg-danger/5 border border-danger/20">
          <div class="text-sm font-medium text-text mb-1">File Details</div>
          <div class="text-sm text-muted space-y-1">
            <div>File name: <span class="font-medium text-text">{{ errorFileName }}</span></div>
            <div>File size: <span class="font-medium text-text">{{ errorFileSize }}MB</span></div>
            <div>Maximum allowed: <span class="font-medium text-text">{{ props.maxSizeMB || 10 }}MB</span></div>
          </div>
        </div>
        
        <div class="mt-4 text-sm text-muted">
          Please compress or resize your file and try again. Supported formats: PDF, PNG, JPG, JPEG.
        </div>
        
        <div class="mt-6 flex justify-end gap-2">
          <!-- Close button removed - modals cannot be closed via close button -->
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{ label?: string; accept?: string; maxSizeMB?: number }>()
const emit = defineEmits(['select'])
const fileName = ref('')
const showErrorModal = ref(false)
const errorFileName = ref('')
const errorFileSize = ref('')

function onFile(e: Event) {
  const input = e.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return
  const f = input.files[0]
  
  const maxSize = (props.maxSizeMB || 10) * 1024 * 1024
  if (f.size > maxSize) {
    errorFileName.value = f.name
    errorFileSize.value = (f.size / (1024 * 1024)).toFixed(2)
    showErrorModal.value = true
    input.value = ''
    fileName.value = ''
    return
  }
  
  fileName.value = f.name
  emit('select', f)
}
</script>


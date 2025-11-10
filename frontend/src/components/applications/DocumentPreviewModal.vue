<template>
  <transition name="fade">
    <div v-if="open" class="fixed inset-0 z-[60] grid place-items-center">
      <!-- Backdrop click disabled - modals cannot be closed by clicking outside -->
      <div class="absolute inset-0 bg-black/40"></div>
      <transition name="pop">
        <div v-if="open" class="relative bg-white rounded-xl shadow-xl w-[90vw] max-w-4xl p-6 max-h-[90vh] overflow-y-auto">
          <div class="stack">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">{{ document?.doc_type ? getDocTypeLabel(document.doc_type) : 'Document Preview' }}</h3>
              <div class="flex items-center gap-2">
                <a :href="documentUrl" target="_blank" download class="text-primary text-sm hover:underline">Download</a>
                <button @click="$emit('close')" class="text-muted hover:text-text transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="mt-4">
              <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-sm text-muted">Loading document...</div>
              </div>
              <div v-else-if="error" class="text-center py-12">
                <div class="text-danger text-sm mb-2">{{ error }}</div>
                <a :href="documentUrl" target="_blank" class="text-primary text-sm hover:underline">Open in new tab</a>
              </div>
              <div v-else class="border rounded-lg overflow-hidden bg-muted/5">
                <template v-if="isPdf">
                  <object 
                    :data="documentUrl" 
                    type="application/pdf"
                    class="w-full h-[600px] border-0"
                    style="min-height: 600px;"
                  >
                    <iframe 
                      :src="documentUrl" 
                      class="w-full h-[600px] border-0"
                      style="min-height: 600px;"
                    ></iframe>
                    <p class="text-center py-12">
                      Your browser does not support PDF preview. 
                      <a :href="documentUrl" target="_blank" class="text-primary hover:underline">Click here to download</a>
                    </p>
                  </object>
                </template>
                <img 
                  v-else-if="isImage"
                  :src="documentUrl" 
                  :alt="document?.doc_type"
                  class="w-full h-auto max-h-[600px] object-contain mx-auto"
                  @load="onImageLoad"
                  @error="handleError"
                />
                <div v-else class="text-center py-12">
                  <div class="text-muted text-sm mb-2">Preview not available for this file type</div>
                  <a :href="documentUrl" target="_blank" class="text-primary text-sm hover:underline">Download to view</a>
                </div>
              </div>
            </div>
            <div v-if="document" class="mt-4 text-xs text-muted space-y-1">
              <div>File Type: {{ document.file_type?.toUpperCase() }}</div>
              <div v-if="document.size_bytes">File Size: {{ formatFileSize(document.size_bytes) }}</div>
              <div v-if="document.status">Status: {{ getDocStatusLabel(document.status) }}</div>
              <div v-if="document.comment" class="mt-2 p-2 bg-muted/10 rounded">
                <div class="font-medium">Comment:</div>
                <div>{{ document.comment }}</div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

const props = defineProps<{ open: boolean; document?: any }>()
const emit = defineEmits(['close'])

const loading = ref(true)
const error = ref('')

const isPdf = computed(() => props.document?.file_type === 'pdf')
const isImage = computed(() => ['png', 'jpg', 'jpeg'].includes(props.document?.file_type || ''))

function normalizeUrl(url: string | undefined): string {
  if (!url) return ''
  
  const base = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'
  
  if (url.startsWith('http://localhost') || url.startsWith('https://localhost')) {
    return url.replace(/^https?:\/\/localhost/, base.replace(/\/$/, ''))
  }
  
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }
  
  if (url.startsWith('/')) {
    return `${base}${url}`
  }
  
  return `${base}/${url.replace(/^\//, '')}`
}

const documentUrl = computed(() => normalizeUrl(props.document?.file_url))

let loadTimeout: number | null = null

watch(() => props.open, (newVal) => {
  if (loadTimeout) {
    clearTimeout(loadTimeout)
    loadTimeout = null
  }
  
  if (newVal && props.document) {
    if (!props.document?.file_url) {
      loading.value = false
      error.value = 'Document file URL is missing'
    } else if (isPdf.value) {
      loading.value = false
      error.value = ''
    } else {
      loading.value = true
      error.value = ''
      loadTimeout = window.setTimeout(() => {
        if (loading.value) {
          loading.value = false
          error.value = 'Document preview timed out. Please try downloading the file.'
        }
      }, 5000)
    }
  } else if (!newVal) {
    loading.value = false
    error.value = ''
  }
})

watch(() => props.document?.file_url, (newUrl) => {
  if (loadTimeout) {
    clearTimeout(loadTimeout)
    loadTimeout = null
  }
  
  if (props.open && newUrl) {
    if (isPdf.value) {
      loading.value = false
      error.value = ''
    } else {
      loading.value = true
      error.value = ''
      loadTimeout = window.setTimeout(() => {
        if (loading.value) {
          loading.value = false
          error.value = 'Document preview timed out. Please try downloading the file.'
        }
      }, 5000)
    }
  }
})

function handleError() {
  if (loadTimeout) {
    clearTimeout(loadTimeout)
    loadTimeout = null
  }
  loading.value = false
  error.value = 'Failed to load document preview. The file may not be accessible at: ' + documentUrl.value
}

function onIframeLoad(e: Event) {
  
  if (loadTimeout) {
    clearTimeout(loadTimeout)
    loadTimeout = null
  }
  
  setTimeout(() => {
    loading.value = false
    error.value = ''
  }, 300)
}

function onImageLoad() {
  
  if (loadTimeout) {
    clearTimeout(loadTimeout)
    loadTimeout = null
  }
  loading.value = false
  error.value = ''
}

function getDocTypeLabel(docType: string): string {
  const labels: Record<string, string> = {
    high_school_cert: 'High School Certificate',
    passport_first_page: 'Passport First Page',
    visa_receipt: 'Visa Receipt',
    full_passport_pdf: 'Full Passport PDF',
    embassy_noc_receipt: 'Embassy NOC Receipt',
    signed_offer_letter: 'Signed Offer Letter',
    passport_photo: 'Passport Photo',
    single_entry_fee_payment: 'Single Entry Fee Payment',
    bank_statement: 'Bank Statement',
    yellow_fever_card: 'Yellow Fever Card',
    eval_pdf: 'eVAL PDF',
    e_visa_pdf: 'E Visa PDF',
    annual_fee_slip: 'Annual Fee Payment Slip',
    one_way_ticket: 'One Way Ticket',
    airport_form: 'Airport Form',
    accommodation_form: 'Accommodation Application Form',
    payment_receipt: 'Payment Receipt'
  }
  return labels[docType] || docType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function getDocStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    uploaded: 'Uploaded',
    under_review: 'Under Review',
    approved: 'Approved',
    needs_reupload: 'Needs Reupload',
    rejected: 'Rejected'
  }
  return labels[status] || status
}

function formatFileSize(bytes?: number): string {
  if (!bytes) return '—'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}
</script>

<style scoped>
.fade-enter-active,.fade-leave-active{ transition: opacity .15s ease }
.fade-enter-from,.fade-leave-to{ opacity: 0 }
.pop-enter-active,.pop-leave-active{ transition: transform .16s ease, opacity .16s ease }
.pop-enter-from,.pop-leave-to{ transform: scale(.98); opacity: 0 }
</style>

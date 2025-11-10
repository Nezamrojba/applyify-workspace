<template>
  <div class="space-y-6">
    <!-- Marketing Content -->
    <div class="rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20 p-6">
      <div class="flex items-start gap-4">
        <div class="h-12 w-12 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-primary">
            <path d="M4.5 3.75a3 3 0 00-3 3v.75h21v-.75a3 3 0 00-3-3h-15z" />
            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 003 3h15a3 3 0 003-3v-7.5zm-18 3.75a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-lg font-semibold mb-2">{{ t('paymentStep.serviceFeeTitle') }}</h3>
          <p class="text-sm text-muted mb-3" v-html="t('paymentStep.serviceFeeDescription')"></p>
          <div class="space-y-2 text-sm">
            <div class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-success mt-0.5 flex-shrink-0">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <span>{{ t('paymentStep.benefitGuidance') }}</span>
            </div>
            <div class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-success mt-0.5 flex-shrink-0">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <span>{{ t('paymentStep.benefitVisa') }}</span>
            </div>
            <div class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-success mt-0.5 flex-shrink-0">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <span>{{ t('paymentStep.benefitSupport') }}</span>
            </div>
            <div class="flex items-start gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-success mt-0.5 flex-shrink-0">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <span>{{ t('paymentStep.benefitArrival') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Information -->
    <Card>
      <template #header>
        <div class="font-medium">{{ t('paymentStep.paymentInfo') }}</div>
      </template>
      <div class="space-y-4">
        <div class="p-3 sm:p-4 rounded-lg bg-muted/5 border border-black/10">
          <div class="text-sm text-muted mb-1">{{ t('paymentStep.amountToPay') }}</div>
          <div class="text-xl sm:text-2xl font-bold text-primary">$100.00 USD</div>
        </div>
        
        <div class="space-y-3">
          <div>
            <div class="text-sm font-medium mb-2">{{ t('paymentStep.accountNumber') }}</div>
            <div class="p-3 rounded-lg bg-muted/5 border border-black/10 font-mono text-base sm:text-lg break-all">{{ accountNumber || t('paymentStep.loadingAccounts') }}</div>
            <button 
              @click="copyAccountNumber" 
              class="mt-2 text-xs text-primary hover:underline flex items-center gap-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M13.887 3.182c.396.037.79.08 1.183.128C16.194 3.45 17 4.414 17 5.517V16.75A2.25 2.25 0 0114.75 19h-9.5A2.25 2.25 0 013 16.75V5.517c0-1.103.806-2.068 1.93-2.207.393-.048.787-.09 1.183-.128A3.001 3.001 0 019 1h2c1.373 0 2.531.923 2.887 2.182zM7.5 4A1.5 1.5 0 019 2.5h2A1.5 1.5 0 0112.5 4v.5h-5V4z" clip-rule="evenodd" />
              </svg>
              {{ t('paymentStep.copyAccount') }}
            </button>
          </div>
          
          <div v-if="accountName" class="text-sm">
            <div class="text-muted">{{ t('paymentStep.accountName') }}</div>
            <div class="font-medium">{{ accountName }}</div>
          </div>
          
          <div v-if="bankName" class="text-sm">
            <div class="text-muted">{{ t('paymentStep.bankName') }}</div>
            <div class="font-medium">{{ bankName }}</div>
          </div>
        </div>
      </div>
    </Card>

    <!-- Receipt Upload -->
    <Card>
      <template #header>
        <div class="font-medium">{{ t('paymentStep.uploadReceipt') }}</div>
        <div class="text-xs text-muted mt-1">{{ t('paymentStep.uploadReceiptDescription') }}</div>
      </template>
      <div class="space-y-4">
        <div v-if="receiptFile" class="p-4 rounded-lg border border-success/20 bg-success/5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-8 w-8 text-success">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <div>
                <div class="font-medium text-sm">{{ receiptFile.name }}</div>
                <div class="text-xs text-muted">{{ formatFileSize(receiptFile.size) }}</div>
              </div>
            </div>
            <button 
              @click="receiptFile = null; receiptUrl = null" 
              class="text-danger hover:text-danger/80 text-sm"
            >
              {{ t('common.remove') }}
            </button>
          </div>
        </div>
        
        <div v-else>
          <label class="block">
            <input 
              type="file" 
              accept="image/*,.pdf" 
              @change="handleFileSelect" 
              class="hidden" 
              ref="fileInput"
            />
            <div class="border-2 border-dashed border-black/20 rounded-lg p-8 text-center cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-12 w-12 mx-auto text-muted mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
              </svg>
              <div class="font-medium text-sm mb-1">{{ t('paymentStep.clickToUpload') }}</div>
              <div class="text-xs text-muted">{{ t('paymentStep.allowedFormats') }}</div>
            </div>
          </label>
        </div>
        
        <p v-if="receiptError" class="text-xs text-danger">{{ receiptError }}</p>
      </div>
    </Card>

    <!-- Student Info -->
    <Card>
      <template #header>
        <div class="font-medium">{{ t('paymentStep.yourInformation') }}</div>
      </template>
      <div class="space-y-2 text-sm">
        <div>
          <div class="text-muted">{{ t('common.name') }}</div>
          <div class="font-medium">{{ studentInfo?.name || '—' }}</div>
        </div>
        <div>
          <div class="text-muted">{{ t('common.email') }}</div>
          <div class="font-medium">{{ studentInfo?.email || '—' }}</div>
        </div>
        <div>
          <div class="text-muted">{{ t('common.passport') }}</div>
          <div class="font-medium">{{ passportNo || '—' }}</div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/services/api'
import Card from '@/components/ui/Card.vue'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
  passportNo: string
}>()

const emit = defineEmits<{
  receiptUploaded: [receiptUrl: string]
}>()

const auth = useAuthStore()
const toast = useToast()
const accountNumber = ref('')
const accountName = ref('')
const bankName = ref('')
const receiptFile = ref<File | null>(null)
const receiptUrl = ref<string | null>(null)
const receiptError = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const studentInfo = ref<{ name: string; email: string } | null>(null)
const { t } = useI18n()

onMounted(async () => {
  // Load student info
  if (auth.user) {
    studentInfo.value = {
      name: auth.user.name,
      email: auth.user.email
    }
  }
  
  // Load payment account details from settings
  try {
    const settings = await api.admin.settings.list() as any
    accountNumber.value = settings['payment.account_number'] || settings['payment.accountNumber'] || '1234567890'
    accountName.value = settings['payment.account_name'] || settings['payment.accountName'] || 'Student Application Platform'
    bankName.value = settings['payment.bank_name'] || settings['payment.bankName'] || 'Example Bank'
  } catch (e) {
    console.error('Failed to load payment settings', e)
    // Default values
    accountNumber.value = '1234567890'
    accountName.value = 'Student Application Platform'
    bankName.value = 'Example Bank'
  }
})

async function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  
  // Validate file
  const maxSize = 10 * 1024 * 1024 // 10MB
  if (file.size > maxSize) {
    receiptError.value = t('paymentStep.fileTooLarge') as string
    return
  }
  
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
  if (!validTypes.includes(file.type)) {
    receiptError.value = t('paymentStep.invalidFile') as string
    return
  }
  
  receiptError.value = ''
  receiptFile.value = file
  
  // Upload file
  try {
    // Use the local upload endpoint via API service
    const response = await api.uploads.local(file) as any
    receiptUrl.value = response.file_url || response.url || response.path
    
    if (receiptUrl.value) {
      emit('receiptUploaded', receiptUrl.value)
      toast.success(t('paymentStep.receiptUploaded') as string)
    } else {
      throw new Error('No URL returned from upload')
    }
  } catch (e: any) {
    receiptError.value = e?.message || (t('paymentStep.receiptUploadFailed') as string)
    receiptFile.value = null
    toast.error(receiptError.value)
  }
}

function copyAccountNumber() {
  if (accountNumber.value) {
    navigator.clipboard.writeText(accountNumber.value)
    toast.success(t('common.accountNumberCopied') as string)
  }
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>


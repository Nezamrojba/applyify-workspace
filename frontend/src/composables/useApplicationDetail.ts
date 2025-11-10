import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { api } from '@/services/api'
import { useI18n } from 'vue-i18n'

type Nullable<T> = T | null

export function useApplicationDetail() {
  const route = useRoute()
  const auth = useAuthStore()
  const toast = useToast()
  const { t, locale, tm } = useI18n()

  const isRtl = computed(() => locale.value === 'ar')
  const application = ref<any>(null)
  const documents = ref<any[]>([])
  const messages = ref<any[]>([])
  const letters = ref<any[]>([])
  const loading = ref(true)
  const approving = ref(false)
  const approvingPayment = ref(false)
  const showRejectModal = ref(false)
  const rejectReason = ref('')
  const issuingLetter = ref(false)
  const sendingMessage = ref(false)
  const newMessage = ref('')
  const letterFiles = ref<Record<string, File>>({})
  const letterFileInputs = ref<Record<string, HTMLInputElement>>({})
  const submittingStage = ref(false)
  const approvingStage = ref(false)
  const uploadingDocs = ref<Record<string, boolean>>({})
  const previewDoc = ref<any>(null)
  const showPreview = ref(false)
  const finalizingArrival = ref(false)
  const messagesContainer = ref<HTMLElement | null>(null)
  const refreshingMessages = ref(false)
  const editingMessageId = ref<number | null>(null)
  const editingMessageText = ref('')
  const savingEdit = ref(false)
  const deletingMessageId = ref<number | null>(null)
  const showDeleteConfirm = ref(false)
  const messageToDelete = ref<number | null>(null)
  const arrivalForm = ref({
    airport_contact_name: '',
    airport_contact_phone: '',
    airport_contact_whatsapp: '',
    arrival_date: '',
    arrival_flight: ''
  })
  const newReceiptFile = ref<File | null>(null)
  const newReceiptUrl = ref<string | null>(null)
  const resubmittingPayment = ref(false)
  const receiptFileInput = ref<HTMLInputElement | null>(null)

  let messagesPollInterval: number | null = null
  let visibilityHandler: Nullable<() => void> = null

  const backPath = computed(() => {
    if (auth.user?.role === 'staff') return '/staff/assignments'
    return '/dashboard/applications'
  })

  const isStudent = computed(() => auth.user?.role === 'student')
  const isStaff = computed(() => auth.user?.role === 'staff' || auth.user?.role === 'super_admin')

  const stage4Approved = computed(() => {
    if (!application.value || !application.value.stages) return false
    const stage4 = application.value.stages.find((s: any) => s.stage_key === 'stage4')
    return stage4?.status === 'approved' && application.value.status === 'arriving'
  })

  const stage4Data = computed(() => {
    if (!application.value || !application.value.stages) return null
    const stage4 = application.value.stages.find((s: any) => s.stage_key === 'stage4')
    if (!stage4 || !stage4.data) return null
    return stage4.data.airport_contact_name ? stage4.data : null
  })

  const arrivalSteps = computed(() => {
    const steps = tm?.('applicationDetail.arrival.steps') as Record<string, any> | undefined
    return steps || {}
  })

  const unreadMessagesCount = computed(() => {
    if (!messages.value || !auth.user?.id) return 0
    return messages.value.filter((msg: any) => !msg.read && msg.to_user_id === auth.user?.id).length
  })

  const currentStageLabel = computed(() => {
    if (!application.value || !application.value.stages) return '—'
    const stageIndex = application.value.stage_index || 1
    const stageKeys = ['stage1', 'stage2', 'stage3', 'stage4']
    const stageKey = stageKeys[stageIndex - 1]
    if (!stageKey) return '—'

    const stageInfo = application.value.stages.find((s: any) => s.stage_key === stageKey)
    if (!stageInfo) return '—'

    const stageName = t(`applicationDetail.stageDefinitions.${stageKey}.shortLabel`, stageKey)
    const statusLabel = getStageStatusLabel(stageInfo.status)

    return `${stageName} - ${statusLabel}`
  })

  const stages = computed(() => {
    if (!application.value || !application.value.stages) return []
    const stageData = [
      {
        key: 'stage1',
        label: t('applicationDetail.stageDefinitions.stage1.label'),
        description: t('applicationDetail.stageDefinitions.stage1.description'),
        letterType: 'MOL',
        requiredDocs: ['high_school_cert', 'passport_first_page']
      },
      {
        key: 'stage2',
        label: t('applicationDetail.stageDefinitions.stage2.label'),
        description: t('applicationDetail.stageDefinitions.stage2.description'),
        letterType: 'EVAL',
        requiredDocs: [
          'visa_receipt',
          'full_passport_pdf',
          'embassy_noc_receipt',
          'signed_offer_letter',
          'passport_photo'
        ]
      },
      {
        key: 'stage3',
        label: t('applicationDetail.stageDefinitions.stage3.label'),
        description: t('applicationDetail.stageDefinitions.stage3.description'),
        letterType: 'VISA',
        requiredDocs: [
          'single_entry_fee_payment',
          'passport_first_page',
          'bank_statement',
          'yellow_fever_card',
          'eval_pdf'
        ]
      },
      {
        key: 'stage4',
        label: t('applicationDetail.stageDefinitions.stage4.label'),
        description: t('applicationDetail.stageDefinitions.stage4.description'),
        letterType: null,
        requiredDocs: [
          'e_visa_pdf',
          'signed_offer_letter',
          'annual_fee_slip',
          'one_way_ticket',
          'airport_form',
          'accommodation_form'
        ]
      }
    ]

    return stageData.map((stage, idx) => {
      const stageInfo = application.value.stages.find((s: any) => s.stage_key === stage.key)
      const stageDocs = documents.value.filter(d => d.stage_key === stage.key)
      const currentStageIndex = application.value.stage_index || 1
      const stageNumber = idx + 1

      const isCurrent = stageNumber === currentStageIndex
      const isUnlocked = stageInfo?.status !== 'locked'

      const uploadedDocs = stage.requiredDocs.filter(docType => {
        const doc = getUploadedDoc(stage.key, docType)
        return doc !== undefined && doc !== null
      })
      const approvedDocs = stage.requiredDocs.filter(docType => {
        const doc = getUploadedDoc(stage.key, docType)
        return doc && doc.status === 'approved'
      })

      const stageStatus = stageInfo?.status || 'locked'
      const allDocsApproved = approvedDocs.length === stage.requiredDocs.length

      let canIssueLetter = false
      if (isStaff.value && stage.letterType) {
        if (isCurrent && stageInfo?.status === 'submitted' && allDocsApproved) {
          canIssueLetter = true
        }
      }

      return {
        ...stage,
        status: stageStatus,
        documents: stageDocs,
        canIssueLetter,
        isCurrent,
        isUnlocked,
        canSubmit: false,
        allDocsApproved,
        autoSubmitted: false,
        approvedDocs: approvedDocs.length,
        uploadedDocs: uploadedDocs.length
      }
    })
  })

  function getStatusLabel(status: string): string {
    return t(`applicationDetail.applicationStatuses.${status}`, status)
  }

  function getStatusBadgeClass(status: string): string {
    const classes: Record<string, string> = {
      draft: 'bg-warning/10 text-warning',
      active: 'bg-success/10 text-success',
      completed: 'bg-primary/10 text-primary',
      arriving: 'bg-warning/10 text-warning'
    }
    return classes[status] || 'bg-muted/10 text-muted'
  }

  function getPaymentStatusLabel(status: string): string {
    return t(`applicationDetail.paymentStatuses.${status}`, status)
  }

  function getPaymentStatusBadgeClass(status: string): string {
    const classes: Record<string, string> = {
      pending: 'bg-warning/10 text-warning',
      approved: 'bg-success/10 text-success',
      rejected: 'bg-danger/10 text-danger'
    }
    return classes[status] || 'bg-muted/10 text-muted'
  }

  function getStageStatusLabel(status: string): string {
    return t(`applicationDetail.stageStatuses.${status}`, status)
  }

  function getStageStatusBadgeClass(status: string): string {
    const classes: Record<string, string> = {
      draft: 'bg-primary/10 text-primary',
      submitted: 'bg-warning/10 text-warning',
      approved: 'bg-success/10 text-success',
      letter_issued: 'bg-success/10 text-success',
      locked: 'bg-muted/10 text-muted'
    }
    return classes[status] || 'bg-muted/10 text-muted'
  }

  function getDocTypeLabel(docType: string): string {
    const fallback = docType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    return t(`applicationDetail.documents.types.${docType}.label`, fallback)
  }

  function getDocTypeDescription(docType: string): string {
    const fallback = t('applicationDetail.documents.types.defaultDescription')
    return t(`applicationDetail.documents.types.${docType}.description`, fallback)
  }

  function getDocTypeAccept(docType: string): string {
    if (
      docType === 'high_school_cert' ||
      docType.includes('pdf') ||
      docType === 'full_passport_pdf' ||
      docType === 'eval_pdf' ||
      docType === 'e_visa_pdf'
    ) {
      return '.pdf'
    }
    if (docType === 'passport_first_page' || docType === 'passport_photo' || docType === 'visa_receipt') {
      return '.pdf,.png,.jpg,.jpeg'
    }
    return '.pdf,.png,.jpg,.jpeg'
  }

  function getUploadedDoc(stageKey: string, docType: string): any | undefined {
    return documents.value.find(d => d.stage_key === stageKey && d.doc_type === docType)
  }

  function getDocStatusLabel(status: string): string {
    return t(`applicationDetail.documents.status.${status}`, status)
  }

  function getDocStatusBadgeClass(status: string): string {
    const classes: Record<string, string> = {
      uploaded: 'bg-muted/10 text-muted',
      under_review: 'bg-warning/10 text-warning',
      approved: 'bg-success/10 text-success',
      needs_reupload: 'bg-danger/10 text-danger',
      rejected: 'bg-danger/10 text-danger'
    }
    return classes[status] || 'bg-muted/10 text-muted'
  }

  function formatFileSize(bytes?: number): string {
    if (!bytes) return '—'
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  }

  function formatDate(date: string): string {
    return new Date(date).toLocaleString()
  }

  function getFileName(fileUrl: string): string {
    if (!fileUrl) return ''
    try {
      const url = new URL(fileUrl)
      const pathname = url.pathname
      const parts = pathname.split('/')
      const filename = parts[parts.length - 1] || pathname
      return filename.length > 30 ? filename.substring(0, 27) + '...' : filename
    } catch {
      const parts = fileUrl.split('/')
      const filename = parts[parts.length - 1] || fileUrl
      return filename.length > 30 ? filename.substring(0, 27) + '...' : filename
    }
  }

  async function loadApplication() {
    const id = Number(route.params.id)
    if (!id) return
    try {
      const app = (await api.applications.show(id)) as any
      application.value = app
      if (app.letters) {
        letters.value = app.letters
      }
      await loadDocuments()
      await loadMessages()
    } catch (e) {
      console.error('Failed to load application', e)
      toast.error(t('applicationDetail.toasts.loadApplicationFailed'))
    } finally {
      loading.value = false
    }
  }

  async function loadDocuments() {
    if (!application.value?.id) return
    try {
      documents.value = (await api.applications.getDocuments(application.value.id)) as any[]
    } catch (e) {
      console.error('Failed to load documents', e)
    }
  }

  async function loadMessages(silent = false) {
    if (!application.value?.id) return
    if (!silent) refreshingMessages.value = true
    try {
      const msgs = (await api.applications.getMessages(application.value.id)) as any[]

      const existingIds = new Set(messages.value.map((m: any) => m.id))
      const newMessages = msgs.filter((m: any) => !existingIds.has(m.id))
      const newUnreadMessages = newMessages.filter((m: any) => !m.read && m.to_user_id === auth.user?.id)

      if (newUnreadMessages.length > 0 && !document.hidden) {
        const markReadPromises = newUnreadMessages.map((msg: any) =>
          api.applications.markMessageRead(application.value!.id, msg.id).catch(e => {
            console.error('Failed to mark message as read', e)
          })
        )
        await Promise.all(markReadPromises)

        for (const msg of newUnreadMessages) {
          const msgInArray = msgs.find((m: any) => m.id === msg.id)
          if (msgInArray) {
            msgInArray.read = true
          }
        }
      }

      messages.value = msgs

      if (newUnreadMessages.length > 0 && messagesContainer.value && !silent) {
        setTimeout(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
          }
        }, 100)
        const newMessageToast =
          newUnreadMessages.length === 1
            ? t('applicationDetail.toasts.newMessageSingle')
            : t('applicationDetail.toasts.newMessageMultiple', { count: newUnreadMessages.length })
        toast.success(newMessageToast)
      } else if (newMessages.length > 0 && messagesContainer.value) {
        setTimeout(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
          }
        }, 100)
      }
    } catch (e) {
      console.error('Failed to load messages', e)
    } finally {
      if (!silent) refreshingMessages.value = false
    }
  }

  async function refreshMessages() {
    await loadMessages(false)
  }

  function startMessagesPolling() {
    if (messagesPollInterval || !application.value?.id) return

    const pollMessages = () => {
      if (!document.hidden && application.value?.id) {
        loadMessages(true)
      }
    }

    messagesPollInterval = window.setInterval(pollMessages, 30000)

    visibilityHandler = () => {
      if (!document.hidden && application.value?.id) {
        loadMessages(true)
      }
    }

    document.addEventListener('visibilitychange', visibilityHandler)
  }

  function stopMessagesPolling() {
    if (messagesPollInterval) {
      clearInterval(messagesPollInterval)
      messagesPollInterval = null
    }
    if (visibilityHandler) {
      document.removeEventListener('visibilitychange', visibilityHandler)
      visibilityHandler = null
    }
  }

  async function handleApprovePayment() {
    if (!application.value) return
    approvingPayment.value = true
    try {
      const response = (await api.applications.approvePayment(application.value.id)) as any
      toast.success(t('applicationDetail.toasts.paymentApproved'))
      if (response?.application) {
        application.value = response.application
      } else {
        await loadApplication()
      }
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.paymentApproveFailed'))
    } finally {
      approvingPayment.value = false
    }
  }

  async function handleRejectPayment() {
    if (!application.value) return
    approvingPayment.value = true
    try {
      const response = (await api.applications.rejectPayment(application.value.id, rejectReason.value || undefined)) as any
      toast.success(t('applicationDetail.toasts.paymentRejected'))
      if (response?.application) {
        application.value = response.application
      } else {
        await loadApplication()
      }
      showRejectModal.value = false
      rejectReason.value = ''
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.paymentRejectFailed'))
    } finally {
      approvingPayment.value = false
    }
  }

  async function handleReceiptFileSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    const maxSize = 10 * 1024 * 1024
    if (file.size > maxSize) {
      toast.error(t('applicationDetail.toasts.receiptTooLarge'))
      return
    }

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
    if (!validTypes.includes(file.type)) {
      toast.error(t('applicationDetail.toasts.receiptInvalidType'))
      return
    }

    newReceiptFile.value = file

    try {
      const response = (await api.uploads.local(file)) as any
      newReceiptUrl.value = response.file_url || response.url || response.path

      if (!newReceiptUrl.value) {
        throw new Error('No URL returned from upload')
      }
      toast.success(t('applicationDetail.toasts.receiptUploadSuccess'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.receiptUploadFailed'))
      newReceiptFile.value = null
      newReceiptUrl.value = null
    }
  }

  async function handleResubmitPayment() {
    if (!application.value || !newReceiptUrl.value) return
    resubmittingPayment.value = true
    try {
      const response = (await api.applications.updatePaymentReceipt(application.value.id, newReceiptUrl.value)) as any
      toast.success(t('applicationDetail.toasts.receiptResubmitted'))
      if (response?.application) {
        application.value = response.application
      } else {
        await loadApplication()
      }
      newReceiptFile.value = null
      newReceiptUrl.value = null
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.receiptResubmitFailed'))
    } finally {
      resubmittingPayment.value = false
    }
  }

  async function handleApprove() {
    if (!application.value) return
    approving.value = true
    try {
      await api.applications.approve(application.value.id)
      toast.success(t('applicationDetail.toasts.applicationApproved'))
      await loadApplication()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.applicationApproveFailed'))
    } finally {
      approving.value = false
    }
  }

  async function updateDocStatus(docId: number, status: string) {
    if (!application.value) return
    try {
      const response = (await api.applications.updateDocStatus(application.value.id, docId, { status })) as any
      toast.success(t('applicationDetail.toasts.documentStatusUpdated'))

      await loadDocuments()

      if (response?.application?.stages) {
        application.value.stages = response.application.stages
        if (response.application.stage_index !== undefined) {
          application.value.stage_index = response.application.stage_index
        }
        if (response.application.status) {
          application.value.status = response.application.status
        }
      }

      await loadApplication()

      window.dispatchEvent(new Event('notification-refresh'))

      if (status === 'approved' && response?.application?.stages && application.value?.stage_index === 1) {
        const stage1 = response.application.stages.find((s: any) => s.stage_key === 'stage1')
        if (stage1 && stage1.status === 'submitted') {
          const requiredDocs = ['high_school_cert', 'passport_first_page']
          const allApproved = requiredDocs.every(docType => {
            const doc = documents.value.find(d => d.stage_key === 'stage1' && d.doc_type === docType)
            return doc && doc.status === 'approved'
          })
          if (allApproved && isStaff.value) {
            toast.success(t('applicationDetail.toasts.stage1AutoSubmitted'))
          }
        }
      }
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.documentStatusFailed'))
    }
  }

  async function uploadDocument(stageKey: string, docType: string, file: File) {
    if (!application.value?.id) return
    const key = `${stageKey}_${docType}`
    uploadingDocs.value[key] = true
    try {
      const maxSize = docType === 'full_passport_pdf' ? 50 * 1024 * 1024 : 10 * 1024 * 1024
      const maxSizeMB = docType === 'full_passport_pdf' ? 50 : 10
      if (file.size > maxSize) {
        toast.error(
          t('applicationDetail.toasts.documentTooLarge', { max: maxSizeMB, size: (file.size / (1024 * 1024)).toFixed(2) })
        )
        uploadingDocs.value[key] = false
        return
      }

      const upload = (await api.uploads.localWithDocType(file, docType)) as any
      await api.applications.uploadDoc(application.value.id, {
        stage_key: stageKey,
        doc_type: docType,
        file_url: upload.file_url,
        file_type: upload.file_type,
        size_bytes: upload.size_bytes
      })
      toast.success(t('applicationDetail.toasts.documentUploadSuccess'))
      await loadDocuments()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      let errorMessage = t('applicationDetail.toasts.documentUploadFailed')
      if (e?.status === 413 || e?.message?.includes('too large') || e?.message?.includes('413')) {
        const maxSizeMB = docType === 'full_passport_pdf' ? 50 : 10
        errorMessage = t('applicationDetail.toasts.documentTooLarge', {
          max: maxSizeMB,
          size: (file.size / (1024 * 1024)).toFixed(2)
        })
      } else if (e?.message) {
        errorMessage = e.message
      }
      toast.error(errorMessage)
      console.error('Upload error:', e)
    } finally {
      uploadingDocs.value[key] = false
    }
  }

  async function submitStage(stageKey: string) {
    if (!application.value?.id) return
    submittingStage.value = true
    try {
      await api.applications.submitStage(application.value.id, stageKey)
      toast.success(t('applicationDetail.toasts.stageSubmitted'))
      await loadApplication()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.stageSubmitFailed'))
    } finally {
      submittingStage.value = false
    }
  }

  async function approveStageAction(stageKey: string) {
    if (!application.value?.id) return
    approvingStage.value = true
    try {
      const response = (await api.applications.approveStage(application.value.id, stageKey)) as any
      toast.success(t('applicationDetail.toasts.stageApproved'))
      if (response?.application) {
        if (response.application.stages) {
          application.value.stages = response.application.stages
        }
        if (response.application.stage_index !== undefined) {
          application.value.stage_index = response.application.stage_index
        }
        if (response.application.status) {
          application.value.status = response.application.status
        }
      }
      await loadDocuments()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.stageApproveFailed'))
    } finally {
      approvingStage.value = false
    }
  }

  function handleLetterFile(e: Event, stageKey: string) {
    const input = e.target as HTMLInputElement
    if (input.files && input.files[0]) {
      letterFiles.value[stageKey] = input.files[0]
    }
  }

  async function issueLetter(stageKey: string, letterType: string) {
    if (!application.value || !letterFiles.value[stageKey]) return
    issuingLetter.value = true
    try {
      const file = letterFiles.value[stageKey]
      const maxSize = 10 * 1024 * 1024
      if (file.size > maxSize) {
        toast.error(t('applicationDetail.toasts.letterTooLarge', { size: (file.size / (1024 * 1024)).toFixed(2) }))
        issuingLetter.value = false
        return
      }

      const upload = (await api.uploads.local(file)) as any
      const response = (await api.applications.issueLetter(application.value.id, {
        type: letterType,
        file_url: upload.file_url
      })) as any
      toast.success(t('applicationDetail.toasts.letterIssued', { letterType }))
      delete letterFiles.value[stageKey]
      if (letterFileInputs.value[stageKey]) {
        letterFileInputs.value[stageKey].value = ''
      }
      if (response?.application) {
        if (response.application.stages) {
          application.value.stages = response.application.stages
        }
        if (response.application.letters) {
          letters.value = response.application.letters
        }
        if (response.application.stage_index !== undefined) {
          application.value.stage_index = response.application.stage_index
        }
        if (response.application.status) {
          application.value.status = response.application.status
        }
      }
      await loadDocuments()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      let errorMessage = t('applicationDetail.toasts.letterIssueFailed')
      const file = letterFiles.value[stageKey]
      if (e?.status === 413 || e?.message?.includes('too large') || e?.message?.includes('413')) {
        errorMessage = t('applicationDetail.toasts.letterTooLarge', {
          size: ((file?.size || 0) / (1024 * 1024)).toFixed(2)
        })
      } else if (e?.message) {
        errorMessage = e.message
      }
      toast.error(errorMessage)
    } finally {
      issuingLetter.value = false
    }
  }

  function getStageLetter(stageKey: string): any {
    const letterMap: Record<string, string> = {
      stage1: 'MOL',
      stage2: 'EVAL',
      stage3: 'VISA'
    }
    const letterType = letterMap[stageKey]
    if (!letterType) return null
    const letter = letters.value.find((l: any) => l.type === letterType)
    return letter || null
  }

  async function sendMessage() {
    if (!application.value || !newMessage.value.trim()) return
    sendingMessage.value = true
    try {
      await api.applications.sendMessage(application.value.id, { body: newMessage.value.trim() })
      newMessage.value = ''
      await loadMessages(true)
      toast.success(t('applicationDetail.toasts.messageSent'))
      window.dispatchEvent(new Event('notification-refresh'))

      if (messagesContainer.value) {
        setTimeout(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
          }
        }, 100)
      }
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.messageSendFailed'))
    } finally {
      sendingMessage.value = false
    }
  }

  function handleEditTextareaRef(el: any, messageId: number) {
    if (el && editingMessageId.value === messageId && el instanceof HTMLTextAreaElement) {
      editTextarea.value = el
    }
  }

  const editTextarea = ref<HTMLTextAreaElement | null>(null)

  function startEditMessage(messageId: number, currentText: string) {
    editingMessageId.value = messageId
    editingMessageText.value = currentText
    setTimeout(() => {
      if (editTextarea.value) {
        editTextarea.value.focus()
        editTextarea.value.setSelectionRange(editTextarea.value.value.length, editTextarea.value.value.length)
      }
    }, 100)
  }

  function cancelEdit() {
    editingMessageId.value = null
    editingMessageText.value = ''
  }

  async function saveEditMessage(messageId: number) {
    if (!application.value || !editingMessageText.value.trim()) return
    savingEdit.value = true
    try {
      await api.applications.updateMessage(application.value.id, messageId, { body: editingMessageText.value.trim() })
      await loadMessages()
      cancelEdit()
      toast.success(t('applicationDetail.toasts.messageUpdated'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.messageUpdateFailed'))
    } finally {
      savingEdit.value = false
    }
  }

  function confirmDeleteMessage(messageId: number) {
    messageToDelete.value = messageId
    showDeleteConfirm.value = true
  }

  async function executeDeleteMessage() {
    if (!application.value || !messageToDelete.value) return
    deletingMessageId.value = messageToDelete.value
    try {
      await api.applications.deleteMessage(application.value.id, messageToDelete.value)
      await loadMessages()
      toast.success(t('applicationDetail.toasts.messageDeleted'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.messageDeleteFailed'))
    } finally {
      deletingMessageId.value = null
      messageToDelete.value = null
      showDeleteConfirm.value = false
    }
  }

  function openPreview(doc: any) {
    previewDoc.value = doc
    showPreview.value = true
  }

  function openPaymentReceiptPreview() {
    if (!application.value?.payment_receipt_url) return

    const url = application.value.payment_receipt_url.toLowerCase()
    let fileType = 'pdf'
    if (url.includes('.jpg') || url.includes('.jpeg')) fileType = 'jpg'
    else if (url.includes('.png')) fileType = 'png'
    else if (url.includes('.pdf')) fileType = 'pdf'

    previewDoc.value = {
      file_url: application.value.payment_receipt_url,
      file_type: fileType,
      doc_type: 'payment_receipt'
    }
    showPreview.value = true
  }

  async function finalizeArrival() {
    if (!application.value) return
    if (!arrivalForm.value.airport_contact_name || !arrivalForm.value.airport_contact_phone) {
      toast.error(t('applicationDetail.toasts.arrivalMissingFields'))
      return
    }
    finalizingArrival.value = true
    try {
      await api.applications.finalizeArrival(application.value.id, {
        airport_contact_name: arrivalForm.value.airport_contact_name,
        airport_contact_phone: arrivalForm.value.airport_contact_phone,
        airport_contact_whatsapp: arrivalForm.value.airport_contact_whatsapp || undefined,
        arrival_date: arrivalForm.value.arrival_date || undefined,
        arrival_flight: arrivalForm.value.arrival_flight || undefined
      })
      toast.success(t('applicationDetail.toasts.arrivalFinalized'))
      await loadApplication()
      window.dispatchEvent(new Event('notification-refresh'))
    } catch (e: any) {
      toast.error(e?.message || t('applicationDetail.toasts.arrivalFinalizeFailed'))
    } finally {
      finalizingArrival.value = false
    }
  }

  watch(
    () => route.params.id,
    () => {
      if (route.params.id) {
        loading.value = true
        stopMessagesPolling()
        loadApplication()
      }
    }
  )

  watch(
    () => application.value?.id,
    newId => {
      if (newId) {
        stopMessagesPolling()
        startMessagesPolling()
      } else {
        stopMessagesPolling()
      }
    }
  )

  watch(
    () => messages.value.length,
    () => {
      if (messagesContainer.value) {
        setTimeout(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
          }
        }, 100)
      }
    }
  )

  onMounted(() => {
    loadApplication()
    if (messagesContainer.value) {
      setTimeout(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
      }, 300)
    }
    if (application.value?.id) {
      startMessagesPolling()
    }
  })

  onUnmounted(() => {
    stopMessagesPolling()
  })

  return {
    t,
    locale,
    tm,
    auth,
    isRtl,
    application,
    documents,
    messages,
    letters,
    loading,
    approving,
    approvingPayment,
    showRejectModal,
    rejectReason,
    issuingLetter,
    sendingMessage,
    newMessage,
    letterFiles,
    letterFileInputs,
    submittingStage,
    approvingStage,
    uploadingDocs,
    previewDoc,
    showPreview,
    finalizingArrival,
    messagesContainer,
    refreshingMessages,
    editingMessageId,
    editingMessageText,
    savingEdit,
    deletingMessageId,
    showDeleteConfirm,
    messageToDelete,
    arrivalForm,
    newReceiptFile,
    newReceiptUrl,
    resubmittingPayment,
    receiptFileInput,
    backPath,
    isStudent,
    isStaff,
    stage4Approved,
    stage4Data,
    arrivalSteps,
    unreadMessagesCount,
    currentStageLabel,
    stages,
    getStatusLabel,
    getStatusBadgeClass,
    getPaymentStatusLabel,
    getPaymentStatusBadgeClass,
    getStageStatusLabel,
    getStageStatusBadgeClass,
    getDocTypeLabel,
    getDocTypeDescription,
    getDocTypeAccept,
    getUploadedDoc,
    getDocStatusLabel,
    getDocStatusBadgeClass,
    formatFileSize,
    formatDate,
    getFileName,
    loadApplication,
    loadDocuments,
    loadMessages,
    refreshMessages,
    handleApprovePayment,
    handleRejectPayment,
    handleReceiptFileSelect,
    handleResubmitPayment,
    handleApprove,
    updateDocStatus,
    uploadDocument,
    submitStage,
    approveStageAction,
    handleLetterFile,
    issueLetter,
    getStageLetter,
    sendMessage,
    handleEditTextareaRef,
    startEditMessage,
    cancelEdit,
    saveEditMessage,
    confirmDeleteMessage,
    executeDeleteMessage,
    openPreview,
    openPaymentReceiptPreview,
    finalizeArrival
  }
}


<template>
  <div class="stack relative">
    <div v-if="application?.status !== 'draft' || !isStudent" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
        <button
          @click="$router.push(backPath)"
          :class="[
            'text-muted hover:text-text transition-colors text-sm sm:text-base flex items-center gap-1',
            isRtl ? 'flex-row-reverse' : ''
          ]"
        >
          <span aria-hidden="true">{{ isRtl ? '→' : '←' }}</span>
          <span class="hidden sm:inline">
            {{ t(auth.user?.role === 'staff' ? 'applicationDetail.backToAssignments' : 'applicationDetail.backToApplications') }}
          </span>
          <span class="sm:hidden">{{ t('applicationDetail.back') }}</span>
        </button>
        <h3 class="text-lg sm:text-xl font-semibold">{{ t('applicationDetail.title') }}</h3>
      </div>
    </div>
    
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-sm text-muted">{{ t('applicationDetail.loading') }}</div>
    </div>
    
    <Teleport to="body">
      <div v-if="application && application.status === 'draft' && isStudent" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" style="margin: 0;">
        <Card class="max-w-md w-full">
          <template #header>
            <div class="text-center">
              <div class="text-2xl mb-2">⏳</div>
              <h3 class="text-lg font-semibold">{{ t('applicationDetail.waiting.title') }}</h3>
            </div>
          </template>
          <div class="text-center space-y-4">
            <p class="text-sm text-muted">
              <span v-if="application.payment_status === 'pending'">{{ t('applicationDetail.waiting.pending') }}</span>
              <span v-else-if="application.payment_status === 'rejected'">{{ t('applicationDetail.waiting.rejected') }}</span>
              <span v-else>{{ t('applicationDetail.waiting.default') }}</span>
            </p>
            <div class="text-xs text-muted">
              {{ t('applicationDetail.waiting.applicationNumber', { id: application.id }) }}
            </div>
            <div v-if="application.payment_status" class="mt-2">
              <span :class="['px-2 py-1 rounded text-xs font-medium', getPaymentStatusBadgeClass(application.payment_status)]">
                {{ t('applicationDetail.waiting.payment', { status: getPaymentStatusLabel(application.payment_status) }) }}
              </span>
            </div>
            
            <!-- Payment Resubmission Section for Rejected Payments -->
            <div v-if="application.payment_status === 'rejected'" class="mt-6 p-4 rounded-lg bg-danger/5 border border-danger/20">
              <div class="text-sm font-medium text-danger mb-3">{{ t('applicationDetail.waiting.resubmitTitle') }}</div>
              <div class="space-y-3">
                <div v-if="newReceiptFile" class="p-3 rounded-lg border border-success/20 bg-success/5">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-success">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                      </svg>
                          <span class="text-sm font-medium">{{ newReceiptFile.name }}</span>
                    </div>
                        <button @click="newReceiptFile = null; newReceiptUrl = null" class="text-danger hover:text-danger/80 text-xs">{{ t('common.remove') }}</button>
                  </div>
                </div>
                <div v-else>
                  <label class="block">
                    <input type="file" accept="image/*,.pdf" @change="handleReceiptFileSelect" class="hidden" ref="receiptFileInput" />
                    <div class="border-2 border-dashed border-black/20 rounded-lg p-6 text-center cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 mx-auto text-muted mb-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                      </svg>
                          <div class="font-medium text-sm mb-1">{{ t('applicationDetail.waiting.uploadPrompt') }}</div>
                          <div class="text-xs text-muted">{{ t('applicationDetail.waiting.uploadHint') }}</div>
                    </div>
                  </label>
                </div>
                <Button 
                  v-if="newReceiptUrl" 
                  @click="handleResubmitPayment" 
                  :disabled="resubmittingPayment"
                  class="w-full"
                  variant="primary"
                >
                      {{ resubmittingPayment ? t('applicationDetail.waiting.resubmitting') : t('applicationDetail.waiting.resubmitButton') }}
                </Button>
              </div>
            </div>
          </div>
          <template #footer>
            <div class="flex justify-center">
              <button @click="$router.push(backPath)" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                {{ t('applicationDetail.waiting.backButton') }}
              </button>
            </div>
          </template>
        </Card>
      </div>
    </Teleport>
    
    <div v-if="application && !(application.status === 'draft' && isStudent)" class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
      <div class="lg:col-span-2 space-y-3 sm:space-y-4">
      
      <!-- Payment Review Card for Staff -->
      <Card v-if="isStaff && (application.payment_status === 'pending' || application.payment_status === 'rejected')" :class="['border-warning/20 bg-warning/5', application.payment_status === 'rejected' ? 'border-danger/20 bg-danger/5' : '']">
        <template #header>
          <div :class="['font-medium', application.payment_status === 'rejected' ? 'text-danger' : 'text-warning']">
            {{ application.payment_status === 'rejected' ? t('applicationDetail.paymentReview.titleRejected') : t('applicationDetail.paymentReview.titlePending') }}
          </div>
          <div class="text-xs text-muted mt-1">
            {{ application.payment_status === 'rejected' ? t('applicationDetail.paymentReview.descriptionRejected') : t('applicationDetail.paymentReview.descriptionPending') }}
          </div>
        </template>
        <div class="space-y-4">
          <div class="p-4 rounded-lg bg-white border border-black/10">
            <div class="text-sm font-medium mb-2">{{ t('applicationDetail.paymentReview.receiptTitle') }}</div>
            <div v-if="application.payment_receipt_url" class="space-y-2">
              <button @click="openPaymentReceiptPreview" class="inline-flex items-center gap-2 text-primary hover:underline text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                  <path fill-rule="evenodd" d="M4.5 3.75a3 3 0 00-3 3v.75h21v-.75a3 3 0 00-3-3h-15z" clip-rule="evenodd" />
                  <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 003 3h15a3 3 0 003-3v-7.5zm-18 3.75a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" clip-rule="evenodd" />
                </svg>
                {{ t('applicationDetail.paymentReview.preview') }}
              </button>
              <div class="text-xs text-muted">{{ t('applicationDetail.paymentReview.amount') }}</div>
              <div v-if="application.payment_status === 'rejected'" class="text-xs text-danger mt-2">
                {{ t('applicationDetail.paymentReview.resubmitted') }}
              </div>
            </div>
            <div v-else class="text-sm text-muted">{{ t('applicationDetail.paymentReview.noReceipt') }}</div>
          </div>
          <div class="flex gap-2">
            <Button variant="success" size="sm" class="flex-1" @click="handleApprovePayment" :disabled="approvingPayment">{{ t('applicationDetail.paymentReview.approve') }}</Button>
            <Button variant="danger" size="sm" class="flex-1" @click="showRejectModal = true" :disabled="approvingPayment">
              {{ application.payment_status === 'rejected' ? t('applicationDetail.paymentReview.rejectAgain') : t('applicationDetail.paymentReview.reject') }}
            </Button>
          </div>
        </div>
      </Card>

      <Card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <div class="text-sm text-muted">{{ t('applicationDetail.summary.applicationId', { id: application.id }) }}</div>
            <div class="font-medium mt-1">{{ application.university?.name || t('applicationDetail.summary.fallbackUniversity') }} • {{ application.course?.name || t('applicationDetail.summary.fallbackCourse') }}</div>
            <div class="mt-2 flex items-center gap-2 flex-wrap">
              <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(application.status)]">
                {{ getStatusLabel(application.status) }}
              </span>
              <span v-if="application.payment_status" :class="['px-2 py-1 rounded text-xs font-medium', getPaymentStatusBadgeClass(application.payment_status)]">
                {{ t('applicationDetail.summary.payment', { status: getPaymentStatusLabel(application.payment_status) }) }}
              </span>
              <span class="text-xs text-muted">{{ t('applicationDetail.summary.stage', { current: application.stage_index || 1 }) }}</span>
            </div>
            <div class="mt-2">
              <div class="text-xs text-muted">{{ t('applicationDetail.summary.passport') }}</div>
              <div class="text-sm font-medium">{{ application.passport_no }}</div>
            </div>
            <div v-if="!isStaff && application.staff" class="mt-2">
              <div class="text-xs text-muted">{{ t('applicationDetail.summary.assignedStaff') }}</div>
              <div class="text-sm font-medium">{{ application.staff?.name || '—' }}</div>
            </div>
            <div class="mt-2">
              <div class="text-xs text-muted">{{ t('applicationDetail.summary.currentStage') }}</div>
              <div class="text-sm font-medium">{{ currentStageLabel }}</div>
            </div>
          </div>
          <div v-if="isStaff">
            <div class="text-sm text-muted">{{ t('applicationDetail.summary.student') }}</div>
            <div class="font-medium mt-1">{{ application.student?.name || '—' }}</div>
            <div class="text-xs text-muted mt-1">{{ application.student?.email || '—' }}</div>
          </div>
        </div>
      </Card>

      <div v-if="application.status === 'arriving' && stage4Approved" class="space-y-4">
        <Card>
          <template #header>
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-success">
                  <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.004l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/>
                </svg>
              </div>
              <div>
                <div class="font-medium text-lg">{{ t('applicationDetail.arrival.title') }}</div>
                <div class="text-xs text-muted mt-1">{{ t('applicationDetail.arrival.subtitle') }}</div>
              </div>
            </div>
          </template>
          <div v-if="stage4Data" class="space-y-6">
            <div class="p-4 rounded-lg bg-primary/5 border border-primary/20">
              <div class="flex items-start gap-3 mb-3">
                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-primary">
                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-sm text-text mb-2">{{ t('applicationDetail.arrival.pickupTitle') }}</div>
                  <div class="space-y-2 text-sm">
                    <div class="flex items-start gap-2">
                      <span class="text-muted min-w-[100px]">{{ t('applicationDetail.arrival.contactName') }}</span>
                      <span class="text-text font-medium">{{ stage4Data.airport_contact_name }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                      <span class="text-muted min-w-[100px]">{{ t('applicationDetail.arrival.phone') }}</span>
                      <a :href="`tel:${stage4Data.airport_contact_phone}`" class="text-primary hover:underline">{{ stage4Data.airport_contact_phone }}</a>
                    </div>
                    <div v-if="stage4Data.airport_contact_whatsapp" class="flex items-start gap-2">
                      <span class="text-muted min-w-[100px]">{{ t('applicationDetail.arrival.whatsapp') }}</span>
                      <a :href="`https://wa.me/${stage4Data.airport_contact_whatsapp.replace(/[^0-9]/g, '')}`" target="_blank" class="text-primary hover:underline">{{ stage4Data.airport_contact_whatsapp }}</a>
                    </div>
                    <div v-if="stage4Data.arrival_date" class="flex items-start gap-2">
                      <span class="text-muted min-w-[100px]">{{ t('applicationDetail.arrival.arrivalDate') }}</span>
                      <span class="text-text">{{ new Date(stage4Data.arrival_date).toLocaleDateString() }}</span>
                    </div>
                    <div v-if="stage4Data.arrival_flight" class="flex items-start gap-2">
                      <span class="text-muted min-w-[100px]">{{ t('applicationDetail.arrival.flight') }}</span>
                      <span class="text-text">{{ stage4Data.arrival_flight }}</span>
                    </div>
                  </div>
                  <div class="mt-3 p-3 rounded bg-primary/10 border border-primary/20">
                    <p class="text-xs text-text">
                      <strong>{{ t('applicationDetail.arrival.importantTitle') }}</strong> {{ t('applicationDetail.arrival.importantNote') }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="font-medium text-lg">{{ t('applicationDetail.arrival.nextStepsTitle') }}</div>
              
              <div class="space-y-3">
                <div class="border border-black/5 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <span class="text-primary font-bold text-lg">1</span>
                    </div>
                    <div class="flex-1">
                      <div class="font-medium text-text mb-2">{{ arrivalSteps.hostel?.title }}</div>
                      <div class="text-sm text-muted space-y-2">
                        <p>{{ arrivalSteps.hostel?.body }}</p>
                        <p><strong>{{ arrivalSteps.hostel?.listTitle }}</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                          <li v-for="(item, idx) in arrivalSteps.hostel?.list || []" :key="`hostel-${idx}`">{{ item }}</li>
                        </ul>
                        <p class="mt-2"><strong>{{ t('applicationDetail.arrival.importantTitle') }}</strong> {{ arrivalSteps.hostel?.important }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="border border-black/5 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <span class="text-primary font-bold text-lg">2</span>
                    </div>
                    <div class="flex-1">
                      <div class="font-medium text-text mb-2">{{ arrivalSteps.activation?.title }}</div>
                      <div class="text-sm text-muted space-y-2">
                        <p>{{ arrivalSteps.activation?.body }}</p>
                        <p><strong>{{ arrivalSteps.activation?.listTitle }}</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                          <li v-for="(item, idx) in arrivalSteps.activation?.list || []" :key="`activation-${idx}`">{{ item }}</li>
                        </ul>
                        <p class="mt-2"><strong>{{ t('applicationDetail.arrival.importantTitle') }}</strong> {{ arrivalSteps.activation?.important }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="border border-black/5 rounded-lg p-4">
                  <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <span class="text-primary font-bold text-lg">3</span>
                    </div>
                    <div class="flex-1">
                      <div class="font-medium text-text mb-2">{{ arrivalSteps.englishTest?.title }}</div>
                      <div class="text-sm text-muted space-y-2">
                        <p>{{ arrivalSteps.englishTest?.body }}</p>
                        <p><strong>{{ arrivalSteps.englishTest?.listTitle }}</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                          <li v-for="(item, idx) in arrivalSteps.englishTest?.list || []" :key="`english-${idx}`">{{ item }}</li>
                        </ul>
                        <p class="mt-2"><strong>{{ t('applicationDetail.arrival.importantTitle') }}</strong> {{ arrivalSteps.englishTest?.important }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-muted text-center py-8">
            {{ t('applicationDetail.arrival.pendingInfo') }}
          </div>
        </Card>
      </div>

      <div v-else-if="application.status !== 'draft' && !(application.status === 'arriving' && stage4Approved)" class="space-y-4">
        <Card v-for="(stage, idx) in stages" :key="stage.key">
          <template #header>
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium">{{ stage.label }}</div>
                <div class="text-xs text-muted mt-1">{{ stage.description }}</div>
              </div>
              <span :class="['px-2 py-1 rounded text-xs font-medium', getStageStatusBadgeClass(stage.status)]">
                {{ getStageStatusLabel(stage.status) }}
              </span>
            </div>
          </template>
          <div class="space-y-4">
            <div class="space-y-3">
              <div class="text-sm font-medium">{{ t('applicationDetail.documents.requiredTitle') }}</div>
              <div v-for="docType in stage.requiredDocs" :key="docType" class="border border-black/5 rounded-lg p-3">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1">
                    <div class="text-sm font-medium">{{ getDocTypeLabel(docType) }}</div>
                    <div class="text-xs text-muted mt-1">{{ getDocTypeDescription(docType) }}</div>
                  </div>
                  <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <template v-if="getUploadedDoc(stage.key, docType)">
                      <span class="text-xs text-muted max-w-[150px] truncate" :title="getFileName(getUploadedDoc(stage.key, docType)!.file_url)">
                        {{ getFileName(getUploadedDoc(stage.key, docType)!.file_url) }}
                      </span>
                      <button @click="openPreview(getUploadedDoc(stage.key, docType)!)" class="text-primary text-sm hover:underline whitespace-nowrap">{{ t('applicationDetail.documents.view') }}</button>
                      <span :class="['px-2 py-1 rounded text-xs font-medium whitespace-nowrap', getDocStatusBadgeClass(getUploadedDoc(stage.key, docType)!.status)]">
                        {{ getDocStatusLabel(getUploadedDoc(stage.key, docType)!.status) }}
                      </span>
                      <div v-if="getUploadedDoc(stage.key, docType)!.comment" class="text-xs text-muted italic max-w-xs">
                        {{ getUploadedDoc(stage.key, docType)!.comment }}
                      </div>
                      <select 
                        v-if="isStaff && (stage.status !== 'locked' && (stage.isCurrent || stage.status === 'approved'))"
                        :value="getUploadedDoc(stage.key, docType)!.status"
                        @change="updateDocStatus(getUploadedDoc(stage.key, docType)!.id, ($event.target as HTMLSelectElement).value)"
                        :disabled="application.payment_status !== 'approved' && docType !== 'payment_receipt'"
                        :class="['text-xs border rounded px-2 py-1 bg-white', application.payment_status !== 'approved' && docType !== 'payment_receipt' ? 'opacity-50 cursor-not-allowed' : '']"
                        :title="application.payment_status !== 'approved' && docType !== 'payment_receipt' ? t('applicationDetail.documents.paymentApprovalHint') : ''"
                      >
                        <option value="uploaded">{{ t('applicationDetail.documents.status.uploaded') }}</option>
                        <option value="under_review">{{ t('applicationDetail.documents.status.under_review') }}</option>
                        <option value="approved" :disabled="application.payment_status !== 'approved' && docType !== 'payment_receipt'">{{ t('applicationDetail.documents.status.approved') }}</option>
                        <option value="needs_reupload">{{ t('applicationDetail.documents.status.needs_reupload') }}</option>
                        <option value="rejected">{{ t('applicationDetail.documents.status.rejected') }}</option>
                      </select>
                      <FileUploader 
                        v-if="isStudent && stage.status === 'draft' && stage.isCurrent && (getUploadedDoc(stage.key, docType)!.status === 'needs_reupload' || getUploadedDoc(stage.key, docType)!.status === 'rejected' || getUploadedDoc(stage.key, docType)!.status === 'uploaded')"
                        :label="getUploadedDoc(stage.key, docType)!.status === 'needs_reupload' ? t('applicationDetail.documents.reupload') : t('applicationDetail.documents.replace')"
                        :accept="getDocTypeAccept(docType)"
                        :maxSizeMB="docType === 'full_passport_pdf' ? 50 : 10"
                        @select="(file: File) => uploadDocument(stage.key, docType, file)"
                      />
                    </template>
                    <template v-else>
                      <span v-if="!isStudent || stage.status !== 'draft' || !stage.isCurrent" class="text-xs text-muted">{{ t('applicationDetail.documents.notUploaded') }}</span>
                      <FileUploader 
                        v-if="isStudent && stage.status === 'draft' && stage.isCurrent"
                        :label="t('applicationDetail.documents.upload')"
                        :accept="getDocTypeAccept(docType)"
                        :maxSizeMB="docType === 'full_passport_pdf' ? 50 : 10"
                        @select="(file: File) => uploadDocument(stage.key, docType, file)"
                      />
                    </template>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="isStudent && stage.status === 'draft' && stage.isCurrent" class="border-t pt-3">
              <div v-if="stage.uploadedDocs === stage.requiredDocs.length" class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <div class="flex items-start gap-3">
                  <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-primary">
                      <path fill-rule="evenodd" d="M12 2.25a.75.75 0 01.75.75v16.5a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM6.166 5.106a.75.75 0 010 1.06 8.25 8.25 0 1011.668 0 .75.75 0 111.06-1.06 9.75 9.75 0 11-13.788 0 .75.75 0 011.06 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-sm text-text mb-1">{{ t('applicationDetail.documents.documentsUploadedTitle') }}</div>
                    <div class="text-sm text-muted" v-html="t('applicationDetail.documents.documentsUploadedBody')"></div>
                  </div>
                </div>
              </div>
              <div v-else class="text-xs text-muted text-center py-2">
                {{ t('applicationDetail.documents.uploadReminder', { required: stage.requiredDocs.length, uploaded: stage.uploadedDocs || 0 }) }}
              </div>
            </div>


            <div v-if="isStudent && stage.status === 'approved' && stage.isCurrent && !getStageLetter(stage.key) && stage.key !== 'stage4'" class="border-t pt-4 mt-4">
              <div class="p-4 rounded-lg bg-success/5 border border-success/20">
                <div class="flex items-start gap-3">
                  <div class="h-8 w-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-success">
                      <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-sm text-text mb-1">{{ t('applicationDetail.stage.approvedTitle') }}</div>
                    <div class="text-sm text-muted space-y-1">
                      <p v-html="t('applicationDetail.stage.approvedBody', { letterType: stage.letterType || '' })"></p>
                      <p class="text-xs mt-2" v-html="t('applicationDetail.stage.approvedNote', { letterType: stage.letterType || '' })"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="isStaff && (stage.status === 'draft' || stage.status === 'submitted') && stage.isCurrent && stage.allDocsApproved && !getStageLetter(stage.key)" class="border-t pt-4 mt-4">
              <div class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <div class="flex items-start gap-3 mb-3">
                  <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-primary">
                      <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-sm text-text mb-1">{{ t('applicationDetail.stage.approveTitle') }}</div>
                    <div class="text-sm text-muted mb-3">
                      {{ t('applicationDetail.stage.approveBody', { letterType: stage.letterType || '' }) }}
                    </div>
                    <Button 
                      @click="approveStageAction(stage.key)" 
                      :disabled="approvingStage"
                      variant="primary"
                    >
                      {{ approvingStage ? t('applicationDetail.stage.approving') : t('applicationDetail.stage.approveButton') }}
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="isStaff && stage.status === 'approved' && stage.isCurrent && stage.letterType && !getStageLetter(stage.key)" class="border-t pt-4 mt-4">
              <div class="p-4 rounded-lg bg-success/5 border border-success/20">
                <div class="flex items-start gap-3 mb-3">
                  <div class="h-8 w-8 rounded-full flex items-center justify-center shrink-0 bg-success/10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-success">
                      <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-sm text-text mb-1">{{ t('applicationDetail.stage.issueTitle', { letterType: stage.letterType }) }}</div>
                    <div class="text-sm text-muted mb-3">
                      {{ t('applicationDetail.stage.issueBody', { letterType: stage.letterType }) }}
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <input 
                        type="file" 
                        accept=".pdf" 
                        @change="(e) => handleLetterFile(e, stage.key)"
                        class="text-sm border rounded-lg px-3 py-2 bg-white cursor-pointer"
                        :ref="el => { if (el) letterFileInputs[stage.key] = el as HTMLInputElement }"
                      />
                      <Button 
                        @click="issueLetter(stage.key, stage.letterType)" 
                        :disabled="!letterFiles[stage.key] || issuingLetter"
                        variant="primary"
                      >
                        {{ issuingLetter ? t('applicationDetail.stage.issuing') : t('applicationDetail.stage.issueButton', { letterType: stage.letterType }) }}
                      </Button>
                    </div>
                    <div v-if="letterFiles[stage.key]" class="text-xs text-muted mt-2">
                      {{ t('applicationDetail.stage.selectedFile', { name: letterFiles[stage.key].name }) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="getStageLetter(stage.key)" class="border-t pt-4 mt-4">
              <div class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-primary">
                        <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                      </svg>
                    </div>
                    <div>
                      <div class="font-medium text-sm text-text">{{ t('applicationDetail.stage.issuedTitle', { letterType: stage.letterType }) }}</div>
                      <div class="text-xs text-muted mt-1">{{ t('applicationDetail.stage.issuedSubtitle', { date: formatDate(getStageLetter(stage.key)?.issued_at) }) }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button @click="openPreview({ file_url: getStageLetter(stage.key)?.file_url, file_type: 'pdf', doc_type: `${stage.letterType}_letter` })" class="text-primary text-sm hover:underline">{{ t('applicationDetail.stage.preview') }}</button>
                    <a :href="getStageLetter(stage.key)?.file_url" target="_blank" download class="text-primary text-sm hover:underline">{{ t('applicationDetail.stage.download') }}</a>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="isStaff && stage.key === 'stage4' && stage.status === 'approved' && !stage4Data" class="border-t pt-4 mt-4">
              <div class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <div class="flex items-start gap-3 mb-4">
                  <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-primary">
                      <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-sm text-text mb-1">{{ t('applicationDetail.stage.finalizeTitle') }}</div>
                    <div class="text-sm text-muted mb-4">
                      {{ t('applicationDetail.stage.finalizeBody') }}
                    </div>
                    <div class="space-y-3">
                      <div>
                        <label class="block text-xs font-medium text-text mb-1">{{ t('applicationDetail.stage.fields.airportContactName') }}</label>
                        <input v-model="arrivalForm.airport_contact_name" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-white" :placeholder="t('applicationDetail.stage.fields.airportContactNamePlaceholder')" />
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-text mb-1">{{ t('applicationDetail.stage.fields.airportContactPhone') }}</label>
                        <input v-model="arrivalForm.airport_contact_phone" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-white" :placeholder="t('applicationDetail.stage.fields.airportContactPhonePlaceholder')" />
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-text mb-1">{{ t('applicationDetail.stage.fields.airportContactWhatsapp') }}</label>
                        <input v-model="arrivalForm.airport_contact_whatsapp" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-white" :placeholder="t('applicationDetail.stage.fields.airportContactWhatsappPlaceholder')" />
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-text mb-1">{{ t('applicationDetail.stage.fields.arrivalDate') }}</label>
                        <input v-model="arrivalForm.arrival_date" type="date" class="w-full px-3 py-2 text-sm border rounded-lg bg-white" />
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-text mb-1">{{ t('applicationDetail.stage.fields.arrivalFlight') }}</label>
                        <input v-model="arrivalForm.arrival_flight" type="text" class="w-full px-3 py-2 text-sm border rounded-lg bg-white" :placeholder="t('applicationDetail.stage.fields.arrivalFlightPlaceholder')" />
                      </div>
                      <Button 
                        @click="finalizeArrival" 
                        :disabled="finalizingArrival || !arrivalForm.airport_contact_name || !arrivalForm.airport_contact_phone"
                        variant="primary"
                      >
                        {{ finalizingArrival ? t('applicationDetail.stage.finalizing') : t('applicationDetail.stage.finalizeButton') }}
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>
      </div>

      <div class="lg:col-span-1">
        <Card class="sticky top-4 border-2 border-primary/10 shadow-lg">
          <template #header>
            <div class="flex items-center justify-between pb-2 border-b border-primary/10">
              <div class="flex items-center gap-2">
                <div class="font-semibold text-base">{{ t('applicationDetail.messages.title') }}</div>
                <span v-if="unreadMessagesCount > 0" class="h-5 w-5 rounded-full bg-linear-to-r from-danger to-danger/80 text-white text-xs flex items-center justify-center font-bold shadow-sm">
                  {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="refreshMessages"
                  :disabled="refreshingMessages"
                  class="p-1.5 rounded-lg hover:bg-primary/10 transition-colors text-muted hover:text-primary"
                  :title="t('applicationDetail.messages.refresh')"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" :class="['h-4 w-4 transition-transform', refreshingMessages ? 'animate-spin' : '']">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                </button>
                <div v-if="unreadMessagesCount > 0" class="flex items-center gap-1.5 text-xs text-primary animate-pulse">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5">
                    <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.5a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-medium hidden sm:inline">{{ t('applicationDetail.messages.new') }}</span>
                </div>
              </div>
            </div>
          </template>
          <div class="space-y-3 max-h-[calc(100vh-250px)] overflow-y-auto pr-2 -mr-2" ref="messagesContainer">
            <div v-for="msg in messages" :key="msg.id" :class="['group p-3.5 rounded-xl transition-all duration-200 relative', msg.from_user_id === auth.user?.id ? 'bg-linear-to-br from-primary/10 to-primary/5 ml-4' : 'bg-linear-to-br from-muted/10 to-muted/5 mr-4', !msg.read && msg.to_user_id === auth.user?.id ? 'shadow-md bg-linear-to-br from-primary/15 to-primary/10' : '']">
              <div class="flex items-center gap-2 text-xs text-muted mb-1.5">
                <span class="font-medium text-text">{{ msg.from_user?.name || t('applicationDetail.messages.userFallback') }}</span>
                <span>•</span>
                <span>{{ formatDate(msg.created_at) }}</span>
                <span v-if="msg.edited_at" class="text-xs italic">{{ t('applicationDetail.messages.edited') }}</span>
                <div class="ml-auto flex items-center gap-1">
                  <span v-if="!msg.read && msg.to_user_id === auth.user?.id" class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-sm"></span>
                  <div v-if="msg.from_user_id === auth.user?.id && !editingMessageId" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                      @click="startEditMessage(msg.id, msg.body)"
                      class="p-1 rounded hover:bg-primary/10 text-muted hover:text-primary transition-colors"
                      :title="t('applicationDetail.messages.editTooltip')"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteMessage(msg.id)"
                      class="p-1 rounded hover:bg-danger/10 text-muted hover:text-danger transition-colors"
                      :title="t('applicationDetail.messages.deleteTooltip')"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="editingMessageId === msg.id" class="space-y-2">
                <textarea
                  v-model="editingMessageText"
                  @keydown.esc="cancelEdit"
                  @keydown.ctrl.enter="saveEditMessage(msg.id)"
                  class="w-full border-2 border-primary/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 resize-none"
                  rows="3"
                  :ref="(el) => handleEditTextareaRef(el, msg.id)"
                ></textarea>
                <div class="flex items-center gap-2">
                  <Button
                    @click="saveEditMessage(msg.id)"
                    :disabled="!editingMessageText.trim() || savingEdit"
                    variant="primary"
                    size="sm"
                    class="px-3 py-1.5 text-xs"
                  >
                    {{ savingEdit ? t('applicationDetail.messages.saving') : t('applicationDetail.messages.save') }}
                  </Button>
                  <Button
                    @click="cancelEdit"
                    :disabled="savingEdit"
                    variant="ghost"
                    size="sm"
                    class="px-3 py-1.5 text-xs"
                  >
                    {{ t('applicationDetail.messages.cancel') }}
                  </Button>
                  <span class="text-xs text-muted ml-auto">{{ t('applicationDetail.messages.saveHint') }}</span>
                </div>
              </div>
              <div v-else class="text-sm leading-relaxed text-text">{{ msg.body }}</div>
            </div>
            <div v-if="messages.length === 0" class="text-sm text-muted text-center py-8">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 mx-auto mb-2 opacity-50">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
              </svg>
              <p>{{ t('applicationDetail.messages.noMessages') }}</p>
              <p class="text-xs mt-1">{{ t('applicationDetail.messages.startConversation') }}</p>
            </div>
          </div>
          <div class="border-t-2 border-primary/10 mt-4 pt-4 bg-linear-to-r from-primary/5 to-transparent -mx-4 px-4 pb-4">
            <div class="flex gap-2">
              <input 
                v-model="newMessage" 
                @keyup.enter="sendMessage"
                :placeholder="t('applicationDetail.messages.placeholder')"
                class="flex-1 border-2 border-primary/20 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/40 transition-all bg-white/80 backdrop-blur-sm"
              />
              <Button 
                @click="sendMessage" 
                :disabled="!newMessage.trim() || sendingMessage"
                variant="primary"
                class="px-4 py-2.5 rounded-xl shadow-sm hover:shadow-md transition-all"
              >
                <svg v-if="!sendingMessage" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                <span v-else class="animate-spin">⏳</span>
              </Button>
            </div>
          </div>
        </Card>
      </div>
    </div>
    
    <DocumentPreviewModal :open="showPreview" :document="previewDoc" @close="showPreview = false" />
    
    <Modal :open="showRejectModal" @close="showRejectModal = false">
      <template #header>
        <h3 class="text-lg font-semibold">{{ t('applicationDetail.modals.rejectTitle') }}</h3>
      </template>
      <div class="space-y-4">
        <div>
          <label class="text-sm font-medium mb-2 block">{{ t('applicationDetail.modals.rejectReason') }}</label>
          <textarea 
            v-model="rejectReason" 
            rows="4" 
            class="w-full px-3 py-2 rounded-lg border border-black/10 bg-white text-sm" 
            :placeholder="t('applicationDetail.modals.rejectPlaceholder')"
          ></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <Button variant="ghost" class="flex-1" @click="showRejectModal = false">{{ t('applicationDetail.modals.cancel') }}</Button>
          <Button variant="danger" class="flex-1" @click="handleRejectPayment" :disabled="approvingPayment">
            {{ approvingPayment ? t('applicationDetail.modals.rejecting') : t('applicationDetail.modals.rejectButton') }}
          </Button>
        </div>
      </template>
    </Modal>
    
    <ConfirmModal
      :open="showDeleteConfirm"
      :title="t('applicationDetail.modals.deleteTitle')"
      :message="t('applicationDetail.modals.deleteMessage')"
      :confirmText="t('applicationDetail.modals.deleteConfirm')"
      :cancelText="t('applicationDetail.modals.deleteCancel')"
      variant="danger"
      @close="showDeleteConfirm = false"
      @confirm="executeDeleteMessage"
    />
  </div>
</template>

<script setup lang="ts">
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import FileUploader from '@/components/ui/FileUploader.vue'
import DocumentPreviewModal from '@/components/applications/DocumentPreviewModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import Modal from '@/components/ui/Modal.vue'
import { useApplicationDetail } from '@/composables/useApplicationDetail'

const {
  t,
  auth,
  isRtl,
  application,
  documents,
  messages,
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
  formatDate,
  getFileName,
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
} = useApplicationDetail()
</script>

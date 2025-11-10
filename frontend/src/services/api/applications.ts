import { request } from '../http'

export const applicationsApi = {
  checkLimit: () =>
    request<{ active_count: number; max_allowed: number; can_create: boolean; limit_reached: boolean }>(
      'GET',
      '/api/applications/check-limit'
    ),
  create: (payload: { university_id: number; course_id: number; passport_no: string; payment_receipt_url?: string }) =>
    request('POST', '/api/applications', payload),
  list: () => request('GET', '/api/applications'),
  show: (id: number) => request('GET', `/api/applications/${id}`),
  approve: (id: number) => request('PATCH', `/api/applications/${id}/approve`),
  approvePayment: (id: number) => request('PATCH', `/api/applications/${id}/payment/approve`),
  rejectPayment: (id: number, reason?: string) =>
    request('PATCH', `/api/applications/${id}/payment/reject`, reason ? { reason } : {}),
  updatePaymentReceipt: (id: number, payment_receipt_url: string) =>
    request('PATCH', `/api/applications/${id}/payment/receipt`, { payment_receipt_url }),
  softDelete: (id: number) => request('PATCH', `/api/applications/${id}/soft-delete`),
  uploadDoc: (
    id: number,
    payload: { stage_key: string; doc_type: string; file_url: string; file_type: string; size_bytes?: number }
  ) => request('POST', `/api/applications/${id}/documents`, payload),
  getDocuments: (id: number) => request('GET', `/api/applications/${id}/documents`),
  submitStage: (id: number, stageKey: string) => request('PATCH', `/api/applications/${id}/stages/${stageKey}/submit`),
  approveStage: (id: number, stageKey: string) => request('PATCH', `/api/applications/${id}/stages/${stageKey}/approve`),
  getMessages: (id: number) => request('GET', `/api/applications/${id}/messages`),
  sendMessage: (id: number, payload: { body: string; attachments?: any[] }) =>
    request('POST', `/api/applications/${id}/messages`, payload),
  updateMessage: (id: number, messageId: number, payload: { body: string }) =>
    request('PATCH', `/api/applications/${id}/messages/${messageId}`, payload),
  deleteMessage: (id: number, messageId: number) =>
    request('DELETE', `/api/applications/${id}/messages/${messageId}`),
  markMessageRead: (id: number, messageId: number) =>
    request('PATCH', `/api/applications/${id}/messages/${messageId}/read`),
  updateDocStatus: (id: number, docId: number, payload: { status: string; comment?: string }) =>
    request('PATCH', `/api/applications/${id}/documents/${docId}`, payload),
  issueLetter: (id: number, payload: { type: string; file_url: string }) =>
    request('POST', `/api/applications/${id}/letters`, payload),
  finalizeArrival: (
    id: number,
    payload: {
      airport_contact_name: string
      airport_contact_phone: string
      airport_contact_whatsapp?: string
      arrival_date?: string
      arrival_flight?: string
    }
  ) => request('POST', `/api/applications/${id}/finalize-arrival`, payload)
}


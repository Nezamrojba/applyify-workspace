import { request } from '../http'

export const publicHelpApi = {
  submitInquiry: (payload: { name?: string; email?: string; whatsapp?: string; question: string }) =>
    request('POST', '/api/pre-application-inquiries', payload)
}


import { request } from '../http'

export const staffApi = {
  dashboard: () => request('GET', '/api/staff/dashboard'),
  faqs: {
    list: () => request('GET', '/api/staff/faqs'),
    show: (id: number) => request('GET', `/api/staff/faqs/${id}`),
    update: (
      id: number,
      payload: {
        i18n: {
          question: { en: string; ar: string }
          answer: { en: string; ar: string }
        }
        order?: number
        is_active?: boolean
      }
    ) => request('PATCH', `/api/staff/faqs/${id}`, payload)
  }
}


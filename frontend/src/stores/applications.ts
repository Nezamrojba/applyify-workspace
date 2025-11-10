import { defineStore } from 'pinia'
import { api } from '@/services/api'

type AppItem = { id: number; university_id: number; course_id: number; stage_index: number; status: string }

export const useApplicationStore = defineStore('apps', {
  state: () => ({ items: [] as AppItem[], creating: false }),
  actions: {
    async fetch() {
      const r = await api.applications.list() as any
      this.items = r.data || r
    },
    async create(university_id: number, course_id: number, passport_no: string, payment_receipt_url?: string) {
      // Ensure payment receipt URL is provided
      if (!payment_receipt_url || !payment_receipt_url.trim()) {
        throw new Error('Payment receipt is required to create an application')
      }
      
      this.creating = true
      try {
        const r = await api.applications.create({ university_id, course_id, passport_no, payment_receipt_url }) as any
        // Refresh the applications list to include the new one
        await this.fetch()
        // Return the created application (API returns it directly with id, stages, staff)
        return r?.data || r
      } finally {
        this.creating = false
      }
    },
    async uploadDoc(id: number, payload: { stage_key: string; doc_type: string; file_url: string; file_type: string; size_bytes?: number }) {
      return api.applications.uploadDoc(id, payload)
    }
  }
})


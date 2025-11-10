import { defineStore } from 'pinia'
import { api } from '@/services/api'

type Uni = { id: number; name: string; acceptance_percent?: number }
type Course = { id: number; name: string; code?: string }

export const useUniversityStore = defineStore('unis', {
  state: () => ({ items: [] as Uni[], courses: [] as Course[], loading: false }),
  actions: {
    async fetch(filter: Record<string,any> = {}) {
      this.loading = true
      try {
        const r = await api.universities.list(filter) as any
        this.items = r.data || r
      } finally {
        this.loading = false
      }
    }
  }
})


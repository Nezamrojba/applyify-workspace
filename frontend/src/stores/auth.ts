import { defineStore } from 'pinia'
import { api } from '@/services/api'

type User = { id: number; name: string; email: string; role: string; passport_no?: string }

export const useAuthStore = defineStore('auth', {
  state: () => ({ user: null as User|null, token: localStorage.getItem('token') || '' }),
  actions: {
    async login(email: string, password: string) {
      const r = await api.auth.login({ email, password }) as any
      this.token = r.token
      localStorage.setItem('token', r.token)
      this.user = r.user
    },
    async register(payload: { name: string; email: string; password: string; password_confirmation: string; whatsapp: string; passport_no: string }) {
      const r = await api.auth.register(payload) as any
      this.token = r.token
      localStorage.setItem('token', r.token)
      this.user = r.user
    },
    async me() {
      if (!this.token) return
      try {
        this.user = await api.auth.me() as any
      } catch (e: any) {
        if (e?.message?.includes('Unauthenticated') || e?.message?.includes('401') || (e as any)?.status === 401) {
          this.user = null
          this.token = ''
          localStorage.removeItem('token')
        }
        throw e
      }
    },
    async logout() {
      try {
        await api.auth.logout()
      } catch {}
      this.user = null
      this.token = ''
      localStorage.removeItem('token')
    },
    clearAuth() {
      this.user = null
      this.token = ''
      localStorage.removeItem('token')
    }
  }
})


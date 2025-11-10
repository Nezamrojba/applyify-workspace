import { defineStore } from 'pinia'

type ToastType = 'success'|'error'|'info'|'warning'
type Toast = { id: number; type: ToastType; message: string; duration?: number }

export const useToastStore = defineStore('toast', {
  state: () => ({ items: [] as Toast[] , seq: 1 }),
  actions: {
    push(type: ToastType, message: string, duration = 3000) {
      const id = this.seq++
      this.items.push({ id, type, message, duration })
      if (duration > 0) setTimeout(() => this.remove(id), duration)
      return id
    },
    remove(id: number) {
      this.items = this.items.filter(t => t.id !== id)
    },
    success(msg: string, d?: number) { return this.push('success', msg, d) },
    error(msg: string, d?: number) { return this.push('error', msg, d) },
    info(msg: string, d?: number) { return this.push('info', msg, d) },
    warning(msg: string, d?: number) { return this.push('warning', msg, d) }
  }
})


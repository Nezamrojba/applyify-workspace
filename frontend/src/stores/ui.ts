import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({ authOpen: false, sidebarOpen: false }),
  actions: {
    openAuth() { this.authOpen = true },
    closeAuth() { this.authOpen = false }
    ,openSidebar(){ this.sidebarOpen = true }
    ,closeSidebar(){ this.sidebarOpen = false }
    ,toggleSidebar(){ this.sidebarOpen = !this.sidebarOpen }
  }
})

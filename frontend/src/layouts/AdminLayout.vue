<template>
  <div class="min-h-[70vh] grid grid-cols-1 md:grid-cols-[240px_minmax(0,1fr)] gap-0">
    <aside class="hidden md:block border-r border-black/5 bg-white">
      <div class="h-[calc(100vh-56px)] overflow-y-auto">
        <div class="px-4 py-4 border-b border-black/5">
          <div class="text-sm font-medium truncate">{{ auth.user?.name || '—' }}</div>
          <span class="mt-1 inline-flex items-center text-xs px-2 py-0.5 rounded bg-primary/5 text-primary">{{ roleLabel }}</span>
        </div>
        <SideNav :items="items" :hideBrand="true" />
      </div>
    </aside>
    <div>
      <div class="p-4 lg:p-6 w-full max-w-7xl mx-auto">
        <router-view />
      </div>
    </div>

    <!-- no in-main header; mobile sidebar can be added later if needed -->
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import SideNav from '@/components/layout/SideNav.vue'
import { useSidebar } from '@/composables/useSidebar'
import { useAuthStore } from '@/stores/auth'

const { items: baseItems } = useSidebar('/admin')
const auth = useAuthStore()

const initials = computed(() => {
  const n = (auth.user?.name || 'NA').trim()
  const parts = n.split(/\s+/)
  const s = (parts[0]?.[0] || '') + (parts[1]?.[0] || '')
  return s.slice(0,2).toUpperCase() || 'NA'
})
function hashColor(str: string){
  let h = 0; for (let i=0;i<str.length;i++) h = (h<<5)-h + str.charCodeAt(i)
  const c = (h >>> 0).toString(16).padStart(6, '0').slice(0,6)
  return `#${c}`
}
const avatarColor = computed(() => hashColor(auth.user?.name || 'NA'))
const roleLabel = computed(() => {
  const r = auth.user?.role || ''
  if (r === 'super_admin') return 'Super Admin'
  if (r === 'staff') return 'Staff'
  if (r === 'student') return 'Student'
  return r
})

const items = computed(() => {
  const src = (baseItems.value || []) as any[]
  const others = src.filter(i => !!i.to && !['/admin','/admin/users','/admin/roles','/admin/permissions'].includes(i.to as string) && i.label !== 'User Management')
  const group = {
    label: 'User Management',
    children: [
      { to: '/admin/users', label: 'Users' },
      { to: '/admin/roles', label: 'Roles' },
      { to: '/admin/permissions', label: 'Permissions' }
    ]
  }
  return [
    { to: '/admin', label: 'Overview' },
    group,
    ...others
  ]
})

async function logout(){ try { await auth.logout() } catch {}; location.href = '/' }
</script>

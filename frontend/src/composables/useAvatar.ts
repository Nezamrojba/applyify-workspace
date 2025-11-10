import { computed, type Ref } from 'vue'

function hashColor(str: string) {
  let h = 0
  for (let i = 0; i < str.length; i++) h = (h << 5) - h + str.charCodeAt(i)
  const c = (h >>> 0).toString(16).padStart(6, '0').slice(0, 6)
  return `#${c}`
}

export function useAvatar(name: Ref<string>) {
  const initials = computed(() => {
    const n = (name.value || 'NA').trim()
    const parts = n.split(/\s+/)
    const s = (parts[0]?.[0] || '') + (parts[1]?.[0] || '')
    return s.slice(0, 2).toUpperCase() || 'NA'
  })
  const color = computed(() => hashColor(name.value || 'NA'))
  return { initials, color }
}


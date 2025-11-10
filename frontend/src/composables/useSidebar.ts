import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

type Item = { to: string; label: string; badge?: string | number }

export function useSidebar(base: '/admin' | '/staff' | '/dashboard') {
  const router = useRouter()
  const { t } = useI18n()
  const items = computed<Item[]>(() => {
    const routes = router.getRoutes()
    const list: Item[] = []
    for (const r of routes) {
      if (!r.path.startsWith(base)) continue
      const depth = r.path.split('/').filter(Boolean).length
      if (r.path === base || depth === (base === '/dashboard' ? 1 : 1)) {
        // index handled separately below
        continue
      }
      // Only immediate children like /admin/xxx
      if (r.path.replace(base, '').split('/').filter(Boolean).length !== 1) continue
      const sidebarMeta = (r.meta as any)?.sidebar
      if (sidebarMeta === false) continue
      let label = ''
      if (sidebarMeta?.i18nKey) {
        label = t(sidebarMeta.i18nKey)
      } else {
        label = sidebarMeta?.label || (r.meta as any)?.title || r.name || r.path.split('/').pop() || ''
      }
      list.push({ to: r.path, label })
    }
    // Always ensure index route appears first
    const baseLabel = base === '/dashboard'
      ? t('student.sidebar.overview')
      : base === '/staff'
        ? t('staff.sidebar.overview')
        : 'Overview'
    list.unshift({ to: base, label: baseLabel })
    return list
  })
  return { items }
}


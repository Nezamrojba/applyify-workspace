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
        const translated = t(sidebarMeta.i18nKey) as unknown as string
        label = translated && translated !== sidebarMeta.i18nKey
          ? translated
          : (sidebarMeta?.label || (r.meta as any)?.title || r.name || r.path.split('/').pop() || '')
      } else {
        label = sidebarMeta?.label || (r.meta as any)?.title || r.name || r.path.split('/').pop() || ''
      }
      list.push({ to: r.path, label })
    }
    // Always ensure index route appears first
    let baseLabel: string | any = 'Overview'
    if (base === '/dashboard') {
      const translated = t('student.sidebar.overview') as unknown as string
      baseLabel = translated && translated !== 'student.sidebar.overview' ? translated : 'Overview'
    } else if (base === '/staff') {
      const translated = t('staff.sidebar.overview') as unknown as string
      baseLabel = translated && translated !== 'staff.sidebar.overview' ? translated : 'Overview'
    }
    list.unshift({ to: base, label: baseLabel })
    return list
  })
  return { items }
}


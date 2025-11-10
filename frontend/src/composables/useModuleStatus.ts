import { ref, computed } from 'vue'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const MODULE_DEFAULTS: Record<string, boolean> = {
  'modules.universities.enabled': true,
  'modules.courses.enabled': true,
  'modules.countries.enabled': true,
  'modules.applications.enabled': true,
  'modules.users.enabled': true,
  'modules.roles.enabled': true,
  'modules.permissions.enabled': true,
  'modules.notifications.enabled': true,
  'modules.commission.enabled': true,
  'modules.payments.enabled': false,
  'modules.audits.enabled': false,
}

const moduleSettings = ref<Record<string, boolean>>({ ...MODULE_DEFAULTS })
const loaded = ref(false)
const loading = ref(false)

export function useModuleStatus() {
  const auth = useAuthStore()

  async function loadModuleSettings(requireAuth = false) {
    if (loaded.value || loading.value) return
    
    if (requireAuth && (!auth.token || !auth.user)) {
      return
    }
    
    if (!auth.token || !auth.user) {
      return
    }
    
    loading.value = true
    try {
      const settings = await api.admin.settings.list() as any
      const newSettings: Record<string, boolean> = { ...MODULE_DEFAULTS }
      
      if (settings && typeof settings === 'object') {
        Object.keys(settings).forEach(key => {
          if (key.startsWith('modules.') && key.endsWith('.enabled')) {
            const value = settings[key]
            if (value === true || value === '1' || value === 1 || value === 'true') {
              newSettings[key] = true
            } else if (value === false || value === '0' || value === 0 || value === 'false') {
              newSettings[key] = false
            }
          }
        })
      }
      
      moduleSettings.value = newSettings
      loaded.value = true
    } catch (e: any) {
      if (e?.status === 401 || e?.status === 403 || e?.message?.includes('Unauthenticated') || e?.message?.includes('Forbidden')) {
        loaded.value = true
        moduleSettings.value = { ...MODULE_DEFAULTS }
        return
      }
      console.error('Failed to load module settings', e)
      loaded.value = true
      moduleSettings.value = { ...MODULE_DEFAULTS }
    } finally {
      loading.value = false
    }
  }

  function isModuleEnabled(module: string): boolean {
    const key = `modules.${module}.enabled`
    if (!loaded.value) {
      return MODULE_DEFAULTS[key] ?? false
    }
    return moduleSettings.value[key] !== false
  }

  function refreshSettings() {
    loaded.value = false
    return loadModuleSettings()
  }

  return {
    moduleSettings: computed(() => moduleSettings.value),
    loaded: computed(() => loaded.value),
    loading: computed(() => loading.value),
    loadModuleSettings,
    isModuleEnabled,
    refreshSettings
  }
}


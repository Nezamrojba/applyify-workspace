<template>
  <div class="stack">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h3 class="text-xl font-semibold">System Settings</h3>
      <Button :disabled="submitting" @click="handleSave">Save Settings</Button>
    </div>

    <Card>
      <template #header>
        <div class="text-sm text-muted">Module Control</div>
        <div class="text-xs text-muted mt-1">Enable or disable modules. When disabled, all related features will be hidden and inaccessible.</div>
      </template>
      <div class="space-y-6">
        <div v-for="module in modules" :key="module.key" class="flex items-center justify-between p-4 rounded-lg border border-black/5 hover:bg-black/2 transition-colors">
          <div class="flex-1">
            <div class="font-medium text-sm">{{ module.label }}</div>
            <div class="text-xs text-muted mt-1">{{ module.description }}</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="form[module.key]"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-muted rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
            <span class="ml-3 text-sm font-medium" :class="form[module.key] ? 'text-success' : 'text-muted'">
              {{ form[module.key] ? 'Enabled' : 'Disabled' }}
            </span>
          </label>
        </div>
      </div>
    </Card>

    <Card v-if="generalError" class="border-danger/20 bg-danger/5">
      <div class="text-sm text-danger">{{ generalError }}</div>
    </Card>

    <Card v-if="successMessage" class="border-success/20 bg-success/5">
      <div class="text-sm text-success">{{ successMessage }}</div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { api } from '@/services/api'

const toast = useToast()

const modules = [
  {
    key: 'modules.universities.enabled',
    label: 'Universities',
    description: 'Manage universities, courses, and country listings'
  },
  {
    key: 'modules.courses.enabled',
    label: 'Courses',
    description: 'Manage course catalog and course details'
  },
  {
    key: 'modules.countries.enabled',
    label: 'Countries',
    description: 'Manage country listings for universities'
  },
  {
    key: 'modules.applications.enabled',
    label: 'Applications',
    description: 'Student application submission and management'
  },
  {
    key: 'modules.users.enabled',
    label: 'User Management',
    description: 'Manage users, roles, and permissions'
  },
  {
    key: 'modules.roles.enabled',
    label: 'Roles',
    description: 'Manage user roles and role assignments'
  },
  {
    key: 'modules.permissions.enabled',
    label: 'Permissions',
    description: 'Manage system permissions'
  },
  {
    key: 'modules.commission.enabled',
    label: 'Commission',
    description: 'Staff commission tracking and payment management'
  },
  {
    key: 'modules.notifications.enabled',
    label: 'Notifications',
    description: 'System notifications and alerts'
  },
  {
    key: 'modules.payments.enabled',
    label: 'Payments',
    description: 'Payment processing for applications'
  },
  {
    key: 'modules.audits.enabled',
    label: 'Audit Logs',
    description: 'System audit trail and activity logs'
  }
]

const moduleDefaults: Record<string, boolean> = {
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
  'modules.audits.enabled': false
}

const form = ref<Record<string, boolean>>({ ...moduleDefaults })
const submitting = ref(false)
const generalError = ref('')
const successMessage = ref('')

async function loadSettings() {
  try {
    const settings = await api.admin.settings.list() as any
    const newForm: Record<string, boolean> = { ...moduleDefaults }
    modules.forEach(module => {
      const value = settings?.[module.key]
      if (value === true || value === '1' || value === 1 || value === 'true') {
        newForm[module.key] = true
      } else if (value === false || value === '0' || value === 0 || value === 'false') {
        newForm[module.key] = false
      }
    })
    form.value = newForm
  } catch (e: any) {
    toast.error('Failed to load settings')
    console.error(e)
  }
}

async function handleSave() {
  submitting.value = true
  generalError.value = ''
  successMessage.value = ''
  
  try {
    const payload: Record<string, boolean> = {}
    modules.forEach(module => {
      const key = module.key
      payload[key] = form.value[key] ?? moduleDefaults[key] ?? false
    })
    
    await api.admin.settings.update(payload)
    toast.success('Settings saved successfully')
    successMessage.value = 'Settings saved successfully. Changes will take effect immediately.'
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (e: any) {
    generalError.value = e?.message || 'Failed to save settings'
    toast.error(generalError.value)
  } finally {
    submitting.value = false
  }
}

onMounted(loadSettings)
</script>

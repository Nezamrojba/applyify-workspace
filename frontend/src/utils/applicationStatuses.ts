import { i18n } from '@/i18n'

export function getStatusLabel(status: string): string {
  const translation = i18n.global.t(`statuses.${status}`)
  if (typeof translation === 'string') {
    return translation
  }
  return translation as unknown as string
}

export function getStatusBadgeClass(status: string): string {
  const classes: Record<string, string> = {
    draft: 'bg-warning/10 text-warning',
    active: 'bg-success/10 text-success',
    completed: 'bg-primary/10 text-primary',
    arriving: 'bg-warning/10 text-warning',
    soft_deleted: 'bg-danger/10 text-danger'
  }
  return classes[status] || 'bg-muted/10 text-muted'
}


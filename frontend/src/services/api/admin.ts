import { request } from '../http'

type Query = Record<string, any>

function buildSearchParams(params?: Query) {
  const search = new URLSearchParams()
  if (!params) return search
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      search.append(key, String(value))
    }
  })
  return search
}

export const adminApi = {
  metrics: () => request('GET', '/api/admin/metrics'),
  settings: {
    list: () => request('GET', '/api/admin/settings'),
    update: (payload: Record<string, any>) => request('POST', '/api/admin/settings', payload)
  },
  users: {
    list: (q: Query = {}) => request('GET', `/api/admin/users?${new URLSearchParams(q as any).toString()}`),
    show: (id: number) => request('GET', `/api/admin/users/${id}`),
    create: (payload: {
      name: string
      email: string
      password: string
      whatsapp?: string
      role?: string
    }) => request('POST', '/api/admin/users', payload),
    update: (
      id: number,
      payload: Partial<{
        name: string
        email: string
        password: string
        whatsapp: string
        passport_no: string
        role: string
        is_active: boolean
      }>
    ) => request('PATCH', `/api/admin/users/${id}`, payload),
    delete: (id: number) => request('DELETE', `/api/admin/users/${id}`),
    roles: {
      list: (userId: number) => request('GET', `/api/admin/users/${userId}/roles`),
      assign: (userId: number, role: string) => request('POST', `/api/admin/users/${userId}/roles`, { role }),
      revoke: (userId: number, role: string) => request('DELETE', `/api/admin/users/${userId}/roles/${encodeURIComponent(role)}`)
    }
  },
  roles: {
    list: () => request('GET', '/api/admin/roles'),
    create: (name: string) => request('POST', '/api/admin/roles', { name }),
    update: (name: string, newName: string) =>
      request('PATCH', `/api/admin/roles/${encodeURIComponent(name)}`, { name: newName }),
    syncPermissions: (role: string, permissions: string[]) =>
      request('POST', `/api/admin/roles/${encodeURIComponent(role)}/permissions`, { permissions }),
    delete: (name: string) => request('DELETE', `/api/admin/roles/${encodeURIComponent(name)}`)
  },
  permissions: {
    list: () => request('GET', '/api/admin/permissions'),
    create: (name: string) => request('POST', '/api/admin/permissions', { name }),
    update: (name: string, newName: string) =>
      request('PATCH', `/api/admin/permissions/${encodeURIComponent(name)}`, { name: newName }),
    delete: (name: string) => request('DELETE', `/api/admin/permissions/${encodeURIComponent(name)}`)
  },
  countries: {
    list: (activeOnly?: boolean) =>
      request('GET', '/api/admin/countries' + (activeOnly ? '?active_only=1' : '')),
    show: (id: number) => request('GET', `/api/admin/countries/${id}`),
    create: (payload: { name: string; code?: string; is_active?: boolean }) =>
      request('POST', '/api/admin/countries', payload),
    update: (
      id: number,
      payload: Partial<{ name: string; code: string; is_active: boolean }>
    ) => request('PATCH', `/api/admin/countries/${id}`, payload),
    delete: (id: number) => request('DELETE', `/api/admin/countries/${id}`)
  },
  staffPoints: {
    summary: (params?: Query) => {
      const search = buildSearchParams(params)
      const query = search.toString()
      return request('GET', `/api/admin/staff/points/summary${query ? `?${query}` : ''}`)
    },
    list: (params?: Query) => {
      const search = buildSearchParams(params)
      const query = search.toString()
      return request('GET', `/api/admin/staff/points${query ? `?${query}` : ''}`)
    },
    release: (id: number) => request('POST', `/api/admin/staff/points/${id}/release`)
  },
  feeStructures: {
    list: (courseId: number) => request('GET', `/api/admin/courses/${courseId}/fee-structures`),
    show: (courseId: number, feeStructureId: number) =>
      request('GET', `/api/admin/courses/${courseId}/fee-structures/${feeStructureId}`),
    create: (courseId: number, payload: any) =>
      request('POST', `/api/admin/courses/${courseId}/fee-structures`, payload),
    update: (courseId: number, feeStructureId: number, payload: any) =>
      request('PATCH', `/api/admin/courses/${courseId}/fee-structures/${feeStructureId}`, payload),
    delete: (courseId: number, feeStructureId: number) =>
      request('DELETE', `/api/admin/courses/${courseId}/fee-structures/${feeStructureId}`)
  },
  universities: {
    list: () => request('GET', '/api/admin/universities'),
    show: (id: number) => request('GET', `/api/admin/universities/${id}`),
    create: (payload: {
      name: string
      country_id?: number
      is_active?: boolean
      i18n?: any
    }) => request('POST', '/api/admin/universities', payload),
    update: (
      id: number,
      payload: Partial<{
        name: string
        country_id: number
        is_active: boolean
        i18n: any
      }>
    ) => request('PATCH', `/api/admin/universities/${id}`, payload),
    delete: (id: number) => request('DELETE', `/api/admin/universities/${id}`)
  },
  courses: {
    list: (university_id?: number, min_accept?: number, max_accept?: number) => {
      const params = new URLSearchParams()
      if (university_id) params.append('university_id', String(university_id))
      if (min_accept) params.append('min_accept', String(min_accept))
      if (max_accept) params.append('max_accept', String(max_accept))
      const query = params.toString()
      return request('GET', `/api/admin/courses${query ? `?${query}` : ''}`)
    },
    show: (id: number) => request('GET', `/api/admin/courses/${id}`),
    detail: (id: number) => request('GET', `/api/admin/courses/${id}`),
    create: (payload: {
      university_id: number
      name: string
      code?: string
      level?: string
      acceptance_percent?: number
      duration_months?: number
      is_active?: boolean
      details?: string
      total_tuition_fees?: number
      procedure_fees?: number
      payment_method?: string
      allow_installments?: boolean
      total_years?: number
      i18n?: any
    }) => request('POST', '/api/admin/courses', payload),
    update: (
      id: number,
      payload: Partial<{
        name: string
        code: string
        level: string
        acceptance_percent: number
        duration_months: number
        is_active: boolean
        details: string
        total_tuition_fees: number
        procedure_fees: number
        payment_method: string
        allow_installments: boolean
        total_years: number
        i18n: any
      }>
    ) => request('PATCH', `/api/admin/courses/${id}`, payload),
    delete: (id: number) => request('DELETE', `/api/admin/courses/${id}`)
  },
  faqs: {
    list: () => request('GET', '/api/admin/faqs'),
    create: (payload: {
      i18n: {
        question: { en: string; ar: string }
        answer: { en: string; ar: string }
      }
      order?: number
      is_active?: boolean
    }) => request('POST', '/api/admin/faqs', payload),
    update: (
      id: number,
      payload: {
        i18n: {
          question: { en: string; ar: string }
          answer: { en: string; ar: string }
        }
        order?: number
        is_active?: boolean
      }
    ) => request('PATCH', `/api/admin/faqs/${id}`, payload),
    delete: (id: number) => request('DELETE', `/api/admin/faqs/${id}`),
    assignStaff: (id: number, staffId: number) =>
      request('POST', `/api/admin/faqs/${id}/assign-staff`, { staff_id: staffId }),
    unassignStaff: (id: number) => request('POST', `/api/admin/faqs/${id}/unassign-staff`)
  }
}


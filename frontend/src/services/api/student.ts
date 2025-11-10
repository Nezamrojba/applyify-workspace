import { request } from '../http'

export const studentApi = {
  dashboard: () => request('GET', '/api/student/dashboard'),
  journeyUniversities: (params: Record<string, any> = {}) => request('GET', `/api/student/journey/universities?${new URLSearchParams(params as any).toString()}`),
  journeyCourses: (params: Record<string, any> = {}) => request('GET', `/api/student/journey/courses?${new URLSearchParams(params as any).toString()}`)
}


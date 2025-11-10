import { request } from '../http'

export const staffApi = {
  dashboard: () => request('GET', '/api/staff/dashboard')
}


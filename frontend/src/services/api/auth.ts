import { request } from '../http'

export const authApi = {
  login: (payload: { email: string; password: string }) => request('POST', '/api/auth/login', payload),
  register: (payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    whatsapp: string
    passport_no: string
  }) => request('POST', '/api/auth/register', payload),
  me: () => request('GET', '/api/auth/me'),
  logout: () => request('POST', '/api/auth/logout')
}


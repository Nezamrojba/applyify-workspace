import { request } from '../http'

export const universitiesApi = {
  list: (q: Record<string, any>) => request('GET', `/api/universities?${new URLSearchParams(q as any).toString()}`),
  detail: (id: number) => request('GET', `/api/universities/${id}`)
}


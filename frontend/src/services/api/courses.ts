import { request } from '../http'

export const coursesApi = {
  list: (university_id: number) =>
    request('GET', `/api/courses?${new URLSearchParams({ university_id: String(university_id) }).toString()}`),
  detail: (id: number) => request('GET', `/api/courses/${id}`)
}


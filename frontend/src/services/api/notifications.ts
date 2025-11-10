import { request } from '../http'

export const notificationsApi = {
  list: () => request('GET', '/api/notifications'),
  unread: () => request('GET', '/api/notifications/unread'),
  count: () => request('GET', '/api/notifications/count'),
  markAsRead: (id: string) => request('PATCH', `/api/notifications/${id}/read`),
  markAllAsRead: () => request('PATCH', '/api/notifications/read-all')
}


import { authApi } from './auth'
import { adminApi } from './admin'
import { universitiesApi } from './universities'
import { coursesApi } from './courses'
import { applicationsApi } from './applications'
import { uploadsApi } from './uploads'
import { notificationsApi } from './notifications'
import { staffApi } from './staff'
import { studentApi } from './student'
import { publicHelpApi } from './publicHelp'
export { setAuthStore } from '../http'

export const api = {
  auth: authApi,
  admin: adminApi,
  universities: universitiesApi,
  courses: coursesApi,
  applications: applicationsApi,
  uploads: uploadsApi,
  notifications: notificationsApi,
  staff: staffApi,
  student: studentApi,
  publicHelp: publicHelpApi
}


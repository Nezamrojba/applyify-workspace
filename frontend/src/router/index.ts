import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useModuleStatus } from '@/composables/useModuleStatus'
import Landing from '@/views/Landing.vue'
import CourseDetail from '@/views/courses/CourseDetail.vue'
import AdminDashboard from '@/views/admin/Dashboard.vue'
import StaffDashboard from '@/views/staff/Dashboard.vue'
import StudentDashboard from '@/views/student/Dashboard.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import StaffLayout from '@/layouts/StaffLayout.vue'
import StudentLayout from '@/layouts/StudentLayout.vue'

const routes = [
  { 
    path: '/', 
    component: Landing
  },
  { 
    path: '/courses/:id', 
    component: CourseDetail
  },
  {
    path: '/ask-us',
    component: () => import('@/views/PreApplyHelp.vue')
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { admin: true },
    children: [
      { path: '', component: AdminDashboard, meta: { sidebar: { label: 'Overview' }, title: 'Admin' } },
      { path: 'users', component: () => import('@/views/admin/Users.vue'), meta: { admin: true, sidebar: { label: 'Users' } } },
      { path: 'roles', component: () => import('@/views/admin/Roles.vue'), meta: { admin: true, sidebar: { label: 'Roles' } } },
      { path: 'permissions', component: () => import('@/views/admin/Permissions.vue'), meta: { admin: true, sidebar: { label: 'Permissions' } } },
      { path: 'countries', component: () => import('@/views/admin/Countries.vue'), meta: { admin: true, sidebar: { label: 'Countries' } } },
      { path: 'universities', component: () => import('@/views/admin/Universities.vue'), meta: { admin: true, sidebar: { label: 'Universities' } } },
      { path: 'courses', component: () => import('@/views/admin/Courses.vue'), meta: { admin: true, sidebar: { label: 'Courses' } } },
      { path: 'courses/new', component: () => import('@/views/admin/courses/CourseCreate.vue'), meta: { admin: true, sidebar: false } },
      { path: 'courses/:id/edit', component: () => import('@/views/admin/courses/CourseEdit.vue'), meta: { admin: true, sidebar: false } },
      { path: 'courses/:id/fee-structures', component: () => import('@/views/admin/courses/FeeStructures.vue'), meta: { admin: true, sidebar: false } },
      { path: 'commission', component: () => import('@/views/admin/Commission.vue'), meta: { admin: true, sidebar: { label: 'Commission' } } },
      { path: 'settings', component: () => import('@/views/admin/Settings.vue'), meta: { admin: true, sidebar: { label: 'Settings' } } }
    ]
  },
  {
    path: '/staff',
    component: StaffLayout,
    meta: { staff: true },
    children: [
      { path: '', component: StaffDashboard, meta: { sidebar: { i18nKey: 'staff.sidebar.overview' }, title: 'Staff' } },
      { path: 'assignments', component: () => import('@/views/staff/Assignments.vue'), meta: { staff: true, sidebar: { i18nKey: 'staff.sidebar.assignments' } } },
      { path: 'assignments/:id', component: () => import('@/views/applications/ApplicationDetail.vue'), meta: { staff: true } }
    ]
  },
  {
    path: '/dashboard',
    component: StudentLayout,
    meta: { student: true },
    children: [
      { path: '', component: StudentDashboard, meta: { sidebar: { i18nKey: 'student.sidebar.overview' }, title: 'Dashboard' } },
      { path: 'applications', component: () => import('@/views/student/Applications.vue'), meta: { student: true, sidebar: { i18nKey: 'student.sidebar.applications' } } },
      { path: 'applications/:id', component: () => import('@/views/applications/ApplicationDetail.vue'), meta: { student: true } },
      { path: 'messages', component: () => import('@/views/student/Messages.vue'), meta: { student: true, sidebar: { i18nKey: 'student.sidebar.messages' } } },
      { path: 'journey', component: () => import('@/views/student/Journey.vue'), meta: { student: true, sidebar: { i18nKey: 'student.sidebar.journey' } } }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  const { loadModuleSettings, isModuleEnabled } = useModuleStatus()
  
  try {
    if (!auth.user && auth.token) {
      await auth.me()
    }
  } catch (e: any) {
    if (e?.message?.includes('Unauthenticated') || e?.message?.includes('401') || e?.status === 401) {
      auth.clearAuth()
    }
  }

  try {
    await loadModuleSettings()
    
    if (to.path === '/' || to.path.startsWith('/courses')) {
      if (!isModuleEnabled('universities') || !isModuleEnabled('courses')) {
        return true
      }
    }
  } catch (e) {
    console.error('Failed to check module status', e)
  }

  if (to.meta?.admin) {
    if (!auth.token || !auth.user || auth.user?.role !== 'super_admin') return { path: '/' }
    
    try {
      if (to.path.startsWith('/admin/universities') && !isModuleEnabled('universities')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/courses') && !isModuleEnabled('courses')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/countries') && !isModuleEnabled('countries')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/users') && !isModuleEnabled('users')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/roles') && !isModuleEnabled('roles')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/permissions') && !isModuleEnabled('permissions')) {
        return { path: '/admin', replace: true }
      }
      if (to.path.startsWith('/admin/commission') && !isModuleEnabled('commission')) {
        return { path: '/admin', replace: true }
      }
    } catch (e) {
      console.error('Failed to check module status', e)
    }
  }
  if (to.meta?.staff) {
    if (!auth.token || !auth.user || auth.user?.role !== 'staff') return { path: '/' }
  }
  if (to.meta?.student) {
    if (!auth.token || !auth.user || auth.user?.role !== 'student') return { path: '/' }
  }

  if (to.path === '/' && auth.token && auth.user) {
    if (auth.user.role === 'super_admin') return { path: '/admin' }
    if (auth.user.role === 'staff') return { path: '/staff' }
    if (auth.user.role === 'student') {
      const hasVisitedDashboard = sessionStorage.getItem('student_visited_dashboard')
      if (!hasVisitedDashboard) {
        sessionStorage.setItem('student_visited_dashboard', 'true')
        return { path: '/dashboard' }
      }
    }
  }

  if (auth.token && auth.user?.role === 'staff' && !to.path.startsWith('/staff') && !to.path.startsWith('/courses') && to.path !== '/') {
    return { path: '/staff' }
  }
  if (auth.token && auth.user?.role === 'student' && !to.path.startsWith('/dashboard') && !to.path.startsWith('/courses') && to.path !== '/') {
    return { path: '/dashboard' }
  }
})

export default router

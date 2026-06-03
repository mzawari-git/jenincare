import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/scans/monitor',
    name: 'ScanMonitor',
    component: () => import('@/views/ScanMonitorView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/scans/history',
    name: 'ScanHistory',
    component: () => import('@/views/ScanHistoryView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/scans/:id',
    name: 'ScanDetail',
    component: () => import('@/views/ScanDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/ai-providers',
    name: 'AIProviders',
    component: () => import('@/views/AIProvidersView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/prompts',
    name: 'PromptManager',
    component: () => import('@/views/PromptManagerView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/white-label',
    name: 'WhiteLabel',
    component: () => import('@/views/WhiteLabelView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/products',
    name: 'ProductManager',
    component: () => import('@/views/ProductManagerView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'UserManagement',
    component: () => import('@/views/UserManagementView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'AdminSettings',
    component: () => import('@/views/AdminSettingsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.guest) {
    if (authStore.isAuthenticated) {
      return next('/')
    }
    return next()
  }

  if (to.meta.requiresAuth) {
    if (!authStore.token) {
      return next('/login')
    }

    if (!authStore.user) {
      const isAuth = await authStore.checkAuth()
      if (!isAuth) {
        return next('/login')
      }
    }
    return next()
  }

  next()
})

export default router

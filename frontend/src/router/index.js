import { createRouter, createWebHistory } from 'vue-router'
import api from '../services/api'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'storefront',
      component: { render: () => null },
    },
    {
      path: '/admin',
      redirect: '/admin/login',
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../views/admin/AdminLogin.vue'),
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      meta: { requiresAdmin: true },
      component: () => import('../views/admin/AdminDashboard.vue'),
    },
  ],
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAdmin) {
    return true
  }

  const token = window.localStorage.getItem('admin_token')

  if (!token) {
    window.localStorage.removeItem('admin_user')
    return { name: 'admin-login' }
  }

  try {
    const response = await api.get('/admin/me')
    const user = response.data?.data

    if (!response.data?.success || user?.role !== 'admin') {
      window.localStorage.removeItem('admin_token')
      window.localStorage.removeItem('admin_user')
      return { name: 'admin-login' }
    }

    window.localStorage.setItem('admin_user', JSON.stringify(user))
    return true
  } catch (error) {
    if ([401, 403].includes(error.response?.status)) {
      window.localStorage.removeItem('admin_token')
      window.localStorage.removeItem('admin_user')
      return { name: 'admin-login' }
    }

    console.error('Unable to verify admin session:', error)
    return {
      name: 'admin-login',
      query: { authError: 'verification' },
    }
  }
})

export default router
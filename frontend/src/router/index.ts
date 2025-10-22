import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../components/AppLayout.vue'

const router = createRouter({
  history: createWebHistory('/'),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('../views/DashboardView.vue'),
        },
        {
          path: '/sellers',
          name: 'sellers',
          component: () => import('../views/sellers/SellerListView.vue'),
          meta: { permission: 'view_sellers' },
        },
        {
          path: '/sellers/create',
          name: 'seller-create',
          component: () => import('../views/sellers/SellerFormView.vue'),
          meta: { permission: 'create_sellers' },
        },
        {
          path: '/sales',
          name: 'sales',
          component: () => import('../views/sales/SaleListView.vue'),
          meta: { permission: 'view_sales' },
        },
        {
          path: '/sales/create',
          name: 'sale-create',
          component: () => import('../views/sales/SaleFormView.vue'),
          meta: { permission: 'create_sales' },
        },
      ],
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth) {
    if (!authStore.token) {
      return next('/login')
    }

    if (!authStore.user) {
      try {
        await authStore.getCurrentUser()
      } catch {
        return next('/login')
      }
    }
  }

  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return next('/')
  }

  if (to.meta.permission && !authStore.hasPermission(to.meta.permission as string)) {
    return next('/')
  }

  next()
})

export default router

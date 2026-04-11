import { createRouter, createWebHistory } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import RegisterView from '../views/RegisterView.vue'
import LoginView from '../views/LoginView.vue'
import BuyerDashboardView from '../views/BuyerDashboardView.vue'
import CartView from '../views/CartView.vue'
import OrdersView from '../views/OrdersView.vue'
import SellerView from '../views/SellerView.vue'
import CourierView from '../views/CourierView.vue'
import AdminView from '../views/AdminView.vue'
import { session } from '../stores/session'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: BuyerDashboardView,
    meta: {
      requiresAuth: true,
      roles: ['user'],
    },
  },
  {
    path: '/cart',
    name: 'cart',
    component: CartView,
    meta: {
      requiresAuth: true,
      roles: ['user'],
    },
  },
  {
    path: '/orders',
    name: 'orders',
    component: OrdersView,
    meta: {
      requiresAuth: true,
      roles: ['user'],
    },
  },
  {
    path: '/seller',
    name: 'seller',
    component: SellerView,
    meta: {
      requiresAuth: true,
      roles: ['user'],
    },
  },
  {
    path: '/courier',
    name: 'courier',
    component: CourierView,
    meta: {
      requiresAuth: true,
      roles: ['courier'],
    },
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminView,
    meta: {
      requiresAuth: true,
      roles: ['admin'],
    },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) {
    return true
  }

  if (!session.token.value) {
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  if (!session.me.value) {
    try {
      await session.loadMe()
    } catch {
      session.logout()
      return {
        name: 'login',
        query: { redirect: to.fullPath },
      }
    }
  }

  const roles = to.meta.roles || []
  if (roles.length > 0 && !roles.includes(session.me.value?.role)) {
    session.setFlash('Role akun kamu tidak punya akses ke halaman ini.', 'error')
    return { name: 'home' }
  }

  return true
})

export default router

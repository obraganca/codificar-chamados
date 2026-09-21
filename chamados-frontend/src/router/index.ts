import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/registrar',
    name: 'registrar',
    component: () => import('@/views/Auth/Register.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/',
    redirect: { name: 'dashboard' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/chamados',
    name: 'chamados.index',
    component: () => import('@/views/Tickets/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/chamados/novo',
    name: 'chamados.create',
    component: () => import('@/views/Tickets/Create.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/chamados/:id',
    name: 'chamados.show',
    component: () => import('@/views/Tickets/Show.vue'),
    meta: { requiresAuth: true },
    props: true,
  },
  {
    path: '/chamados/:id/editar',
    name: 'chamados.edit',
    component: () => import('@/views/Tickets/Edit.vue'),
    meta: { requiresAuth: true },
    props: true,
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: { name: 'dashboard' },
  },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.estaAutenticado) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.estaAutenticado) {
    return { name: 'dashboard' }
  }

  return true
})

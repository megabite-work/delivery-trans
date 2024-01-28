import {createRouter, createWebHistory} from 'vue-router'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            meta: { requiresAuth: false },
            component: () => import('../views/auth/LoginView.vue')
        },
        {
            path: '/',
            name: 'dashboard',
            meta: { requiresAuth: true },
            component: () => import('../views/DashboardView.vue')
        },
        {
            path: '/orders',
            name: 'orders',
            meta: { requiresAuth: true },
            component: () => import('../views/OrdersView.vue')
        },
        {
            path: '/carriers',
            name: 'carriers',
            meta: { requiresAuth: true },
            component: () => import('../views/CarriersView.vue')
        },
        {
            path: '/clients',
            name: 'clients',
            meta: { requiresAuth: true },
            component: () => import('../views/ClientsView.vue')
        },
    ]
})

export default router

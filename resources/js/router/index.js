import {createRouter, createWebHistory} from 'vue-router'
import NProgress from 'nprogress';

NProgress.configure({ showSpinner: false });

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
router.beforeResolve((to, from, next) => {
    // If this isn't an initial page load.
    if (to.name) {
        // Start the route progress bar.
        NProgress.start()
    }
    next()
})

router.afterEach(() => {
    // Complete the animation of the route progress bar.
    NProgress.done()
})

export default router

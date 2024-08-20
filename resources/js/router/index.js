import {createRouter, createWebHistory} from 'vue-router'
import NProgress from 'nprogress';

NProgress.configure({ showSpinner: false });

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            meta: { requiresAuth: false },
            component: () => import('../views/auth/LoginView.vue')
        },
        {
            path: '/orders',
            alias: '/',
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
        {
            path: '/prices',
            name: 'prices',
            meta: { requiresAuth: true },
            component: () => import('../views/PricesView.vue')
        },
        {
            path: '/body-types',
            name: 'body-types',
            meta: { requiresAuth: true },
            component: () => import('../views/BodyTypesView.vue')
        },
        {
            path: '/car-capacities',
            name: 'car-capacities',
            meta: { requiresAuth: true },
            component: () => import('../views/CarCapacitiesView.vue')
        },
        {
            path: '/tconditions',
            name: 'tconditions',
            meta: { requiresAuth: true },
            component: () => import('../views/TConditionsView.vue')
        },
        {
            path: '/tonnages',
            name: 'tonnages',
            meta: { requiresAuth: true },
            component: () => import('../views/TonnagesView.vue')
        },
        {
            path: '/users',
            name: 'users',
            meta: { requiresAuth: true },
            component: () => import('../views/UsersView.vue')
        },
        {
            path: '/roles',
            name: 'roles',
            meta: { requiresAuth: true },
            component: () => import('../views/RolesView.vue')
        },
        {
            path: '/404',
            name: '404',
            component: () => import('../views/404.vue')
        },
        {
            path: '/403',
            name: '403',
            component: () => import('../views/403.vue')
        },
        {
            path: '/:catchAll(.*)',
            redirect: '404',
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

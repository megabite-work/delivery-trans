import '@/assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { useAuthStore } from "./stores/auth.js";

import App from './App.vue'
import router from './router'
import Antd from 'ant-design-vue';
import axios from 'axios';

import 'ant-design-vue/dist/reset.css';
import 'nprogress/nprogress.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(Antd)

if (import.meta.env.PROD) {
    axios.defaults.baseURL = ''
} else {
    axios.defaults.baseURL = 'http://dtrans.local'
}

axios.defaults.withCredentials = true
axios.defaults.withXSRFToken = true
axios.defaults.headers.common['Accept'] = 'application/json'


const authStore = useAuthStore()
const pagesPermissions = {
    orders: 'ORDERS_SECTION',
    carriers: 'CARRIERS_SECTION',
    clients: 'CLIENTS_SECTION',
    prices: 'PRICES_DIR',
    'body-types': 'BODY_TYPES_DIR',
    'car-capacities' : 'CAPACITIES_DIR',
    tconditions: 'T_CONDITIONS_DIR',
    tonnages: 'TONNAGES_DIR',
    users: 'USERS_DIR',
    roles: 'ROLES_DIR'
}
router.beforeEach(async (to, from) => {
    await authStore.refreshState()

    if (to.name === 'login' && authStore.isLoggedIn) {
        return {
            path: '/'
        }
    }
    if (to.meta.requiresAuth && !authStore.isLoggedIn) {
        return {
            path: '/login',
            query: { redirect: to.fullPath }
        }
    }
    if (authStore.isLoggedIn && !!pagesPermissions[to.name] && !authStore.userCan(pagesPermissions[to.name])) {
        if (to.path === '/') {
            const plist = Object.keys(pagesPermissions)
            for (let i = 0; i < plist.length; i++) {
                if (authStore.userCan(pagesPermissions[plist[i]])) {
                    return {
                        name: plist[i]
                    }
                }
            }
        }
        return { path: '/403' }
    }
})


app.mount('#app')

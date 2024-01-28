import '@/assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { useAuthStore } from "./stores/auth.js";

import App from './App.vue'
import router from './router'
import Antd from 'ant-design-vue';
import axios from 'axios';

import 'ant-design-vue/dist/reset.css';

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

router.beforeEach(async (to, from) => {
    await authStore.refreshState()
    if (to.name === 'login' && authStore.isLoggedIn) {
        return {
            name: 'dashboard'
        }
    }
    if (to.meta.requiresAuth && !authStore.isLoggedIn) {
        return {
            path: '/login',
            query: { redirect: to.fullPath }
        }
    }
})

app.mount('#app')

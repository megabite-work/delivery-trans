import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const isLoggedIn = computed(() => !(user.value == null))

    async function refreshState() {
        try {
            const res = await axios.get('api/user')
            user.value = res.data
        } catch (e) {
            user.value = null
        }
    }

    async function logout() {
        try {
            return await axios.post('logout')
        } catch (e) {
            throw e
        } finally {
            await refreshState()
        }
    }

    return { user, isLoggedIn, refreshState, logout }
})

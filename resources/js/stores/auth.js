import {computed, ref} from 'vue'
import {defineStore} from 'pinia'
import axios from "axios";
import {isArray} from "radash";
import {message} from "ant-design-vue";

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const isLoggedIn = computed(() => !(user.value == null))
    const currentRoleId = ref(0)
    const currentRole = computed(() => {
        if (user.value && isArray(user.value.roles) && user.value.roles.length > 0) {
            const role = user.value.roles.find(r => r.id === currentRoleId.value)
            if (role) {
                return role
            }
            return user.value.roles[0]
        }
        return {
            id: -1,
            name: 'Без роли',
            permissions: []
        }
    })
    async function refreshState() {
        await axios.get('sanctum/csrf-cookie').catch(() => {
            message.error("Ошибка получения CSRF-токена")
        })
        try {
            const res = await axios.get('api/user')
            user.value = res.data
            currentRoleId.value = parseInt(localStorage.getItem("auth__currentRoleId"))
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

    function userCan(permission) {
        return currentRole.value.permissions.includes("ALL") || currentRole.value.permissions.includes(permission)
    }

    async function switchRole(roleId) {
        currentRoleId.value = roleId
        localStorage.setItem("auth__currentRoleId", currentRole.value.id.toString())
    }

    return { user, isLoggedIn, refreshState, logout, userCan, switchRole, currentRole }
})

import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import {isArray} from "radash";
import {message} from "ant-design-vue";

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const currentRole = ref(null)
    const isLoggedIn = computed(() => !(user.value == null))

    async function refreshState() {
        try {
            const res = await axios.get('api/user')
            user.value = res.data
            let roleId = localStorage.getItem("auth__currentRoleId")
            if (roleId && isArray(user.value.roles)) {
                const cr = user.value.roles.find(role => role.id === parseInt(roleId))
                currentRole.value = cr ?? null
            }
            if (roleId === null && isArray(user.value.roles) && user.value.roles.length > 0) {
                currentRole.value = user.value.roles[0]
                localStorage.setItem("auth__currentRoleId", currentRole.value.id)
            }
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
        try {
            const res = await axios.get('api/user')
            user.value = res.data
            const cr = user.value.roles.find(role => role.id === roleId)
            if (cr) {
                currentRole.value = cr
                localStorage.setItem("auth__currentRoleId", currentRole.value.id)
            }
        } catch (e) {
            user.value = null
            message.error('Не удалось сменить роль. Обновите страницу и попробуйте еще раз.')
        }
    }

    return { user, isLoggedIn, refreshState, logout, userCan, switchRole, currentRole }
})

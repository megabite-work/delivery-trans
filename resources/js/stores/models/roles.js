import {defineStore} from "pinia";
import {ref} from "vue";
import axios from "axios";

export const useRolesStore = defineStore('roles', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const dataList = ref([])

    async function refreshDataList(){
        try {
            listLoading.value = true
            const res = await axios.get('api/roles')
            dataList.value = res.data.data
        } catch (e) {
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getRoles() {
        try {
            listLoading.value = true
            const { data } = await axios.get('api/suggest/roles')
            return data.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getRole(roleId) {
        try {
            const { data } = await axios.get(`api/roles/${roleId}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createRole(role) {
        try {
            const res = await axios.post('api/roles', role)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeRole(role) {
        try {
            const res = await axios.put(`api/roles/${role.id}`, role)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteRole(roleId) {
        try {
            await axios.delete(`api/roles/${roleId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        dataList, err, listLoading,
        refreshDataList, getRoles, getRole, createRole, storeRole, deleteRole,
    }
})

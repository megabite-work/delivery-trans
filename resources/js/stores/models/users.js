import {defineStore} from "pinia";
import {ref} from "vue";
import axios from "axios";

export const useUsersStore = defineStore('users', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const dataList = ref([])

    async function refreshDataList(){
        try {
            listLoading.value = true
            const res = await axios.get('api/users')
            dataList.value = res.data.data
        } catch (e) {
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getUser(userId) {
        try {
            const { data } = await axios.get(`api/users/${userId}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createUser(user) {
        try {
            const res = await axios.post('api/users', user)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeUser(user) {
        try {
            const res = await axios.put(`api/users/${user.id}`, user)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteUser(userId) {
        try {
            await axios.delete(`api/users/${userId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        dataList, err, listLoading,
        refreshDataList, getUser, createUser, storeUser, deleteUser,
    }
})

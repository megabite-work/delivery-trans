import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useClientsStore = defineStore('clients', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const paginator = ref({
        pageSize: localStorage.getItem('clientsTable__clientsPerPage') ?? 15,
        current: 1,
        total: 0,
    })

    const filter = ref('')
    const applyFilter = async () => {
        if (filter.value.trim() === '') {
            await resetFilter()
            return
        }
        paginator.value.current = 1
        await refreshDataList()
    }

    const resetFilter = async () => {
        filter.value = ''
        paginator.value.current = 1
        await refreshDataList()
    }

    async function setPage(page) {
        paginator.value.current = page
        await refreshDataList()
    }

    async function setPageSize(page, pageSize) {
        paginator.value.current = page
        paginator.value.pageSize = pageSize
        localStorage.setItem('clientsTable__clientsPerPage', String(pageSize))
        await refreshDataList()
    }

    const dataList = ref([])

    async function refreshDataList(){
        try {
            listLoading.value = true
            const res = await axios.get('api/clients', {
                params: {
                    page: paginator.value.current,
                    per_page: paginator.value.pageSize,
                    filter: filter.value,
                }
            })
            dataList.value = res.data.data
            paginator.value.pageSize = res.data.meta.per_page
            paginator.value.current = res.data.meta.current_page
            paginator.value.total = res.data.meta.total
        } catch (e) {
            paginator.value = {
                current: 1,
                total: 0,
            }
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getClient(clientId) {
        try {
            const client = await axios.get(`api/clients/${clientId}`)
            return client.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createClient(client) {
        try {
            const res = await axios.post('api/clients', client)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeClient(client) {
        try {
            const res = await axios.put(`api/clients/${client.id}`, client)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteClient(id) {
        try {
            await axios.delete(`api/clients/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        err, paginator, dataList, setPage, setPageSize, listLoading,
        refreshDataList, getClient, createClient, storeClient, deleteClient,
        filter, applyFilter, resetFilter,
    }
})

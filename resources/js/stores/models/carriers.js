import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useCarriersStore = defineStore('carriers', () => {
    const carrierErr = ref(null)
    const listLoading = ref(false)

    const paginator = ref({
        pageSize: localStorage.getItem('carriersTable__carriersPerPage') ?? 15,
        current: 1,
        total: 0,
    })

    async function setPage(page) {
        paginator.value.current = page
        await refreshDataList()
    }

    async function setPageSize(page, pageSize) {
        paginator.value.current = page
        paginator.value.pageSize = pageSize
        localStorage.setItem('carriersTable__carriersPerPage', String(pageSize))
        await refreshDataList()
    }

    const dataList = ref([])

    async function refreshDataList() {
        try {
            listLoading.value = true
            const res = await axios.get('api/carriers', {
                params: { page: paginator.value.current, per_page: paginator.value.pageSize }
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

    async function getCarrier(carrierId) {
        try {
            const carrier = await axios.get(`api/carriers/${carrierId}`)
            return carrier.data
        } catch (e) {
            if (e.response) {
                carrierErr.value = e.response.data
            }
            throw e
        }
    }

    async function createCarrier(carrier) {
        try {
            const res = await axios.post('api/carriers', carrier)
            return res.data
        } catch (e) {
            if (e.response) {
                carrierErr.value = e.response.data
            }
            throw e
        }
    }

    async function storeCarrier(carrier) {
        try {
            const res = await axios.put(`api/carrier/${carrier.id}`, carrier)
            return res.data
        } catch (e) {
            if (e.response) {
                carrierErr.value = e.response.data
            }
            throw e
        }
    }

    async function deleteCarrier(id) {
        try {
            await axios.delete(`api/carriers/${id}`)
        } catch (e) {
            if (e.response) {
                carrierErr.value = e.response.data
            }
            throw e
        }
    }

    return {
        carrierErr, paginator, dataList, setPage, setPageSize, listLoading,
        refreshDataList,
        getCarrier, createCarrier, storeCarrier, deleteCarrier,
    }
})

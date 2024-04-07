import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useOrdersStore = defineStore('orders', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const dataList = ref([])


    const paginator = ref({
        pageSize: localStorage.getItem('ordersTable__ordersPerPage') ?? 15,
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
        localStorage.setItem('ordersTable__ordersPerPage', String(pageSize))
        await refreshDataList()
    }
    async function refreshDataList() {
        try {
            listLoading.value = true
            const res = await axios.get('api/orders', {
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
    return {
        err, paginator, dataList, setPage, setPageSize, listLoading,
        refreshDataList,
    }
})

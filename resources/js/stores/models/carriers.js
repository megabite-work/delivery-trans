import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import {isArray} from "radash";
import dayjs from "dayjs";

export const useCarriersStore = defineStore('carriers', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const paginator = ref({
        pageSize: localStorage.getItem('carriersTable__carriersPerPage') ?? 15,
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
        localStorage.setItem('carriersTable__carriersPerPage', String(pageSize))
        await refreshDataList()
    }

    const dataList = ref([])

    async function refreshDataList() {
        try {
            listLoading.value = true
            const res = await axios.get('api/carriers', {
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

    async function getCarrier(carrierId) {
        try {
            const { data } = await axios.get(`api/carriers/${carrierId}`)
            if (isArray(data.cars)) {
                data.cars = data.cars.map(el => ({...el, sts_date: el.sts_date ? dayjs (el.sts_date) : null }))
            }
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createCarrier(carrier) {
        try {
            const {data} = await axios.post('api/carriers', carrier)
            if (isArray(data.cars)) {
                data.cars = data.cars.map(el => ({...el, sts_date: el.sts_date ? dayjs (el.sts_date) : null }))
            }
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeCarrier(carrier) {
        try {
            const {data} = await axios.put(`api/carriers/${carrier.id}`, carrier)
            if (isArray(data.cars)) {
                data.cars = data.cars.map(el => ({...el, sts_date: el.sts_date ? dayjs (el.sts_date) : null }))
            }
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteCarrier(id) {
        try {
            await axios.delete(`api/carriers/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        err, paginator, dataList, setPage, setPageSize, listLoading, refreshDataList,
        getCarrier, createCarrier, storeCarrier, deleteCarrier,
        filter, applyFilter, resetFilter,
    }
})

import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import dayjs from "dayjs";
import {clone} from "radash";

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

    async function createOrder(order) {
        try {
            const res = await axios.post('api/orders', stringifyOrder(order))
            return parseOrder(res.data)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeOrder(order) {
        try {
            const res = await axios.put(`api/orders/${order.id}`, stringifyOrder(order))
            return parseOrder(res.data)
        } catch {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getOrder(orderId) {
        try {
            const res = await axios.get(`api/orders/${orderId}`)
            return parseOrder(res.data)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    function parseOrder(order) {
        const res = clone(order)
        if (res.from_locations) {
            res.from_locations = res.from_locations.map(item => {
                return {
                    ...item,
                    arrive_date: dayjs(item.arrive_date),
                    arrive_time: item.arrive_time.map(t => dayjs(t))
                }
            })
        }

        if (res.to_locations) {
            res.to_locations = res.to_locations.map(item => {
                return {
                    ...item,
                    arrive_date: dayjs(item.arrive_date),
                    arrive_time: item.arrive_time.map(t => dayjs(t))
                }
            })
        }
        return res
    }

    function stringifyOrder(order) {
        return {
            ...order,
            client_expenses: order.client_expenses ? JSON.stringify(order.client_expenses) : null,
            client_discounts: order.client_discounts ? JSON.stringify(order.client_discounts) : null,
            carrier_expenses: order.carrier_expenses ? JSON.stringify(order.carrier_expenses) : null,
            carrier_fines: order.carrier_fines ? JSON.stringify(order.carrier_fines) : null,
            from_locations: order.from_locations ? JSON.stringify(order.from_locations) : null,
            to_locations: order.to_locations ? JSON.stringify(order.to_locations) : null,
            additional_service: order.additional_service? JSON.stringify(order.additional_service) : null,
        }
    }

    async function deleteOrder(orderId) {
        try {
            await axios.delete(`api/orders/${orderId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        err, paginator, dataList, setPage, setPageSize, listLoading,
        refreshDataList,
        createOrder, storeOrder, deleteOrder, getOrder,
    }
})

import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import dayjs from "dayjs";
import {clone, isArray} from "radash";
import {message} from "ant-design-vue";

export const useOrdersStore = defineStore('orders', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const dataList = ref([])
    const paginator = ref({
        pageSize: localStorage.getItem('ordersTable__ordersPerPage') ?? 15,
        current: 1,
        total: 0,
    })
    const sorter = ref({
        columnKey: localStorage.getItem('orders_table__sorterColumnKey'),
        order: localStorage.getItem('orders_table__sorterOrder'),
    })
    const filter = ref({isFiltered: false})
    const columnsOrders = ref([])


    const applyFilter = async () => {
        filter.value.isFiltered = true
        paginator.value.current = 1
        await refreshDataList()
    }

    const resetFilter = async () => {
        filter.value = {isFiltered: false}
        paginator.value.current = 1
        await refreshDataList()
    }



    async function setSorter (key = undefined, order = undefined) {
        sorter.value = {
            columnKey: key,
            order: order
        }
        localStorage.setItem('orders_table__sorterColumnKey', key)
        localStorage.setItem('orders_table__sorterOrder', order)
        await refreshDataList()
    }

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
            const f = clone(filter.value)
            Object.keys(f).forEach(k => {
                if (k === 'date' || k === 'status_logist_date' || k === 'status_manager_date') {
                    f[k] = f[k].map(v => dayjs(v).format('DD-MM-YYYY'))
                }
            })
            const res = await axios.get('api/orders', {
                params: {
                    page: paginator.value.current,
                    per_page: paginator.value.pageSize,
                    sorter_key: sorter.value.columnKey,
                    sorter_order: sorter.value.order,
                    filter: filter.value.isFiltered ? f : undefined
                }
            })
            dataList.value = res.data.data
            paginator.value.pageSize = parseInt(res.data.meta.per_page)
            paginator.value.current = parseInt(res.data.meta.current_page)
            paginator.value.total = parseInt(res.data.meta.total)
            columnsOrders.value = res.data.meta.listColumns
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

    async function copyOrder(order_id) {
        try {
            const sourceOrder = await getOrder(order_id)
            delete sourceOrder.id
            await createOrder(sourceOrder)
            await refreshDataList()
        } catch (e) {
            message.error("Не удалось скопировать заказ")
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
        if (res.ended_at) {
            res.ended_at = dayjs(res.ended_at)
        }
        if (isArray(res.from_locations)) {
            res.from_locations = res.from_locations.map(item => {
                return {
                    ...item,
                    arrive_date: dayjs(item.arrive_date),
                    arrive_time: isArray(item.arrive_time) ? item.arrive_time.map(t => dayjs(t)) : []
                }
            })
        }

        if (isArray(res.to_locations)) {
            res.to_locations = res.to_locations.map(item => {
                return {
                    ...item,
                    arrive_date: dayjs(item.arrive_date),
                    arrive_time: isArray(item.arrive_time) ? item.arrive_time.map(t => dayjs(t)) : []
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

    async function setOrderStatus(orderId, statusType, status) {
        try {
            const {data} = await axios.post(`api/orders/${orderId}/status`, {
                type: statusType,
                status
            })
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function setOrderStatusDate(statusId, statusDate) {
        try {
            const { data } = await axios.post(`api/order-status/${statusId}/date`, {
                date: statusDate
            })
            return data
        } catch (e) {
            throw e
        }
    }

    return {
        err, paginator, dataList, setPage, setPageSize, setSorter, listLoading,
        refreshDataList, columnsOrders,
        createOrder, storeOrder, deleteOrder, copyOrder, getOrder, setOrderStatus, setOrderStatusDate, filter, applyFilter, resetFilter
    }
})

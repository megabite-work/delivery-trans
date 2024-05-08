import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const usePricesStore = defineStore('prices', () => {
    const err = ref(null)
    const dataList = ref([])
    const listLoading = ref(false)

    const paginator = ref({
        pageSize: localStorage.getItem('pricesTable__pricesPerPage') ?? 15,
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
        localStorage.setItem('pricesTable__pricesPerPage', String(pageSize))
        await refreshDataList()
    }

    async function refreshDataList(){
        try {
            listLoading.value = true
            const res = await axios.get('api/default-prices', {
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

    async function getDefaultPriceById(priceId){
        try {
            const { data } = await axios.get(`api/default-prices/${priceId}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createPriceForDefault(price) {
        try {
            const { data } = await axios.post(`/api/default-prices/${price.price_id}/price`, price)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createDefaultPrice(defaultPrice) {
        try {
            const { data } = await axios.post('/api/default-prices', defaultPrice)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeDefaultPrice(defaultPrice) {
        try {
            const { data } = await axios.patch(`/api/default-prices/${defaultPrice.id}`, defaultPrice)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteDefaultPrice(defaultPriceId) {
        try {
            await axios.delete(`/api/default-prices/${defaultPriceId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }


    async function createPriceForClient(price) {
        try {
            const { data } = await axios.post(`/api/clients/${price.client_id}/price`, price)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storePrice(price) {
        try {
            const { data } = await axios.put(`api/prices/${price.id}`, price)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deletePrice(priceId) {
        try {
            await axios.delete(`api/prices/${priceId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getPriceById(priceId){
        try {
           const { data } = await axios.get(`api/prices/${priceId}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        err, listLoading, dataList, paginator,
        setPage, setPageSize,
        createPriceForClient, storePrice, deletePrice, getPriceById, refreshDataList,
        getDefaultPriceById, createPriceForDefault,
        createDefaultPrice, storeDefaultPrice, deleteDefaultPrice,
    }

})

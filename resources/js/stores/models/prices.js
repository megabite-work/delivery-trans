import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const usePricesStore = defineStore('prices', () => {
    const err = ref(null)
    const dataList = ref([])
    const listLoading = ref(false)

    async function refreshDataList(){
        try {
            listLoading.value = true
            const {data} = await axios.get('api/default-prices')
            dataList.value = data.data
        } catch (e) {
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getDefaultPrices() {
        try {
            const { data } = await axios.get('api/default-prices')
            return data.data
        } catch (e) {
            throw e
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

    async function createAdditionalServiceClient(clientId, svc) {
        try {
            const { data } = await axios.post(`/api/clients/${clientId}/additional-service`, svc)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createAdditionalServiceDefault(priceId, price) {
        try {
            const { data } = await axios.post(`/api/default-prices/${priceId}/price`, price)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeAdditionalService(svc) {
        try {
            const { data } = await axios.put(`api/additional-service/${svc.id}`, svc)
            return data
        } catch(e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteAdditionalService(asId) {
        try {
            await axios.delete(`api/additional-service/${asId}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        err, listLoading, dataList,
        createPriceForClient, storePrice, deletePrice, getPriceById, refreshDataList,
        getDefaultPriceById, createPriceForDefault,
        getDefaultPrices, createDefaultPrice, storeDefaultPrice, deleteDefaultPrice,
        createAdditionalServiceClient, createAdditionalServiceDefault,
        storeAdditionalService, deleteAdditionalService,
    }

})

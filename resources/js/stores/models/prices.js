import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const usePricesStore = defineStore('prices', () => {
    const err = ref(null)

    async function createPriceForClient(price) {
        try {
            const { data } = axios.post(`/api/clients/${price.client_id}/price`, price)
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
            const { data } = axios.put(`api/prices/${price.id}`, price)
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

    return { err, createPriceForClient, storePrice, deletePrice, getPriceById}

})

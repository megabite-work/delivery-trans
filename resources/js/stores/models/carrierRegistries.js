import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import dayjs from "dayjs";

export const useCarrierRegistriesStore = defineStore('carrier-registries', () => {
    const err = ref(null)

    async function createRegistry(registry) {
        try {
            const res = await axios.post('api/carrier-registries', {
                ...registry,
                date: registry.date.format("YYYY-MM-DD"),
            })
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeRegistry(registry) {
        try {
            const res = await axios.put(`api/carrier-registries/${registry.id}`, {
                ...registry,
                date: registry.date.format("YYYY-MM-DD"),
            })
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteRegistry(id) {
        try {
            await axios.delete(`api/carrier-registries/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getRegistry(registryId) {
        try {
            const { data } = await axios.get(`api/carrier-registries/${registryId}`)
            return {
                ...data,
                carrier_sum: parseFloat(data.carrier_sum),
                carrier_paid: parseFloat(data.carrier_paid),
                date: dayjs(data.date),
            }
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createRegistry, storeRegistry, deleteRegistry, getRegistry }
})

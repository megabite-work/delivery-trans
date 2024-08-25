import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import dayjs from "dayjs";

export const useRegistriesStore = defineStore('registries', () => {
    const err = ref(null)

    async function createRegistry(registry) {
        try {
            const res = await axios.post('api/registries', {
                ...registry,
                date: registry.date.format("YYYY-MM-DD"),
                bill_date: registry.bill_date ? registry.bill_date.format("YYYY-MM-DD") : undefined,
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
            const res = await axios.put(`api/registries/${registry.id}`, {
                ...registry,
                date: registry.date.format("YYYY-MM-DD"),
                bill_date: registry.bill_date ? registry.bill_date.format("YYYY-MM-DD") : undefined,

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
            await axios.delete(`api/registries/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getRegistry(registryId) {
        try {
            const { data } = await axios.get(`api/registries/${registryId}`)
            return {
                ...data,
                client_sum: parseFloat(data.client_sum),
                client_paid: parseFloat(data.client_paid),
                date: dayjs(data.date),
                bill_date: data.bill_date ? dayjs(data.bill_date) : undefined
            }
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function downloadRegistry(registryId) {
        window.open(`/download/client-registry/${registryId}`, '_blank')
        // await axios.get(`/download/client-registry/${registryId}`)
    }

    return { err, createRegistry, storeRegistry, deleteRegistry, getRegistry, downloadRegistry }
})

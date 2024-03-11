import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useDriversStore = defineStore('drivers', () => {
    const err = ref(null)

    async function createDriver(driver) {
        console.log(driver)
        try {
            const { data } = await axios.post('api/drivers', driver)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeDriver(driver) {
        console.log(driver)
        try {
            const { data } = await axios.put(`api/drivers/${driver.id}`, driver)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteDriver(id) {
        try {
            await axios.delete(`api/drivers/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getDrivers(carrierId) {
        try {
            const { data } = await axios.get(`api/carriers/${carrierId}/drivers`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createDriver, storeDriver, deleteDriver, getDrivers }
})

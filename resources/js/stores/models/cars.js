import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useCarsStore = defineStore('cars', () => {
    const err = ref(null)

    async function createCar(car) {
        try {
            const res = await axios.post('api/cars', car)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeCar(car) {
        try {
            const res = await axios.put(`api/cars/${car.id}`, car)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteCar(id) {
        try {
            await axios.delete(`api/cars/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getCars(carrierId) {
        try {
            const { data } = await axios.get(`api/carriers/${carrierId}/cars`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createCar, storeCar, deleteCar, getCars }
})

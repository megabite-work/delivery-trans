import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useCarsStore = defineStore('cars', () => {
    const err = ref(null)

    async function createCar(car) {
        try {
            const res = await axios.post('api/cars', car)
            return { ...res.data, sts_date: res.data.sts_date ? dayjs(res.data.sts_date) : null }
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
            return { ...res.data, sts_date: res.data.sts_date ? dayjs(res.data.sts_date) : null }
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
            return data.map(el => ({...el, sts_date: el.sts_date ? dayjs(el.sts_date) : null }))
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createCar, storeCar, deleteCar, getCars }
})

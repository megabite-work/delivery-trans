import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import {message} from "ant-design-vue";

export const useSuggests = defineStore('suggests', () => {
    const err = ref(null)
    const isLoading = ref(false)

    const getCargoNameSuggest = async q => {
        try {
            isLoading.value = true
            const res = await axios.get('api/suggest/cargo-name', {params: { q }})
            return res.data
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getTonnages = async () => {
        try {
            isLoading.value = false
            const { data } = await axios.get('api/suggest/tonnages')
            return data.map(v => ({value: v, label: `${v}т.`}))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getCarBodyTypes = async () => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/car/body-types')
            return data.map(el => ({value: el.type, label: el.type}))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getTConditions = async () => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/t-conditions')
            return data.map(el => ({
                value: `${el.min} ... ${el.max} ℃`,
                label: `${el.min} ... ${el.max} ℃`,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const searchClient = async (q) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/client-search', {params: { q }})
            return data.map(el => ({
                value: el.id,
                label: el.name_short,
                inn: el.inn,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }
    const searchCarrier = async (q) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/carrier-search', {params: { q }})
            return data.map(el => ({
                value: el.id,
                label: el.name_short,
                inn: el.inn,
                disabled: !el.is_active,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getDriversByCarrier = async carrier_id => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/driver-by-carrier', {params: { carrier_id }})
            return data.map(el => ({
                value: el.id,
                label: `${el.surname} ${el.name} ${el.patronymic}`,
                phone: el.phone,
                disabled: !el.is_active,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    return {
        err, isLoading,
        getCargoNameSuggest, getTonnages, getCarBodyTypes, getTConditions, searchClient, searchCarrier,
        getDriversByCarrier,
    }
})

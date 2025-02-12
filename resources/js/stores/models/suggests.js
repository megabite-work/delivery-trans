import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";
import {message} from "ant-design-vue";
import { trim } from "radash";

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

    const getCarCapacities = async () => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/car_capacities')
            return data.map(el => ({
                value: el.id,
                label: `${el.tonnage}т. – ${el.volume}м³. – ${el.pallets_count}п.`,
                ...el,
            }))
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
            return data.map(el => {
                if (Math.abs(el.min) === Math.abs(el.max)) {
                    return {
                        value: `${el.min} ... ${el.max} ℃`,
                        label: `${el.min} ... ${el.max} ℃`,
                    }
                }
                const t = {
                    [Math.abs(el.min)]: el.min,
                    [Math.abs(el.max)]: el.max
                }
                return {
                    value: `${t[Math.min(Math.abs(el.min), Math.abs(el.max))]} ... ${t[Math.max(Math.abs(el.min), Math.abs(el.max))]} ℃`,
                    label: `${t[Math.min(Math.abs(el.min), Math.abs(el.max))]} ... ${t[Math.max(Math.abs(el.min), Math.abs(el.max))]} ℃`,
                }
            })
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
                vat: el.vat,
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
                vat: el.vat,
                is_resident: el.is_resident,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const firmSuggest = async (q) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/firms', {params: { q }})
            return data
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const bankSuggest = async (q) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/bank', {params: { q }})
            return data
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const addressSuggest = async (q) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/address', {params: { q }})
            return data
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const expensesSuggest = async q => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/expenses', {params: { q }})
            return data
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getDriversByCarrier = async carrier_id => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/drivers-by-carrier', {params: { carrier_id }})
            return data.map(el => {
                const name = trim(trim(el.surname).concat(' ', trim(el.name), ' ', trim(el.patronymic)))
                const label = !!el.phone ? el.phone : (!!el.email ? el.email : '')
                return {
                    value: el.id,
                    name,
                    label: label !== '' ? `${name} (${label})` : name,
                    phone: el.phone,
                    inn: el.inn,
                    disabled: !el.is_active,
                }
            })
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getCarsByCarrier = async carrier_id => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/cars-by-carrier', {params: { carrier_id }})
            return data.map(el => ({
                value: el.id,
                label: `${el.name} – ${el.plate_number}`,
                ...el,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getAdditionalServices = async (q='', client_id= 0) => {
        try {
            isLoading.value = true
            const {data} = await axios.get('api/suggest/additional-services/price', {params: {q, client_id}})
            return data.map(el => ({
                value: el.name,
                label: `${el.name}${el.price || el.carrier_price ? ' - ' : ''}${el.price ? `${el.price}₽` : ' – ' } / ${el.carrier_price ? `${el.carrier_price}₽` : ' – ' }`,
                v: el.price,
                vp: el.carrier_price,
            }))
        } catch {
            message.error('Ошибка загрузки списка')
        } finally {
            isLoading.value = false
        }
    }

    const getLastOrderDriverCar = async (driver_id, capacity_id) => {
        try {
            isLoading.value = true
            const res = await axios.get('/api/suggest/order-driver-car', {params: { driver_id, capacity_id }})
            if (res.status === 204) {
                return null
            }
            return res.data
        } catch {
            message.error("Ошибка получения последнего заказа")
        } finally {
            isLoading.value = false
        }
    }

    const getLastOrderFromLocation = async (client_id) => {
        try {
            isLoading.value = true
            const res = await axios.get('/api/suggest/order-from-location', {params: { client_id }})
            if (res.status === 204) {
                return null
            }
            return res.data
        } catch {
            message.error("Ошибка получения последнего заказа")
        } finally {
            isLoading.value = false
        }
    }


    return {
        err, isLoading,
        getCargoNameSuggest, getTonnages, getCarBodyTypes, getTConditions, searchClient, searchCarrier,
        getDriversByCarrier, getCarsByCarrier, getAdditionalServices, getCarCapacities, firmSuggest,
        bankSuggest, addressSuggest, getLastOrderDriverCar, getLastOrderFromLocation, expensesSuggest
    }
})

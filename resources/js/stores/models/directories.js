import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useDirectoriesStore = defineStore('directories', () => {
    const err = ref(null)

    async function getAllElements(directoryName) {
        try {
            const { data } = await axios.get(`api/${directoryName}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function createElement(directoryName, el) {
        try {
            const res = await axios.post(`api/${directoryName}`, el)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeElement(directoryName, el) {
        try {
            const res = await axios.put(`api/${directoryName}/${el.id}`, el)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteElement(directoryName, id) {
        try {
            await axios.delete(`api/${directoryName}/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getElement(directoryName, id) {
        try {
            const { data } = await axios.get(`api/${directoryName}/${id}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createElement, storeElement, deleteElement, getElement, getAllElements }
})

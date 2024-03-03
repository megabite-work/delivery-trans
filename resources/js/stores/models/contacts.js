import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useContactsStore = defineStore('contacts', () => {
    const err = ref(null)

    async function createContact(contact) {
        try {
            const res = await axios.post('api/contacts', contact)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeContact(contact) {
        try {
            const res = await axios.put(`api/contacts/${contact.id}`, contact)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteContact(id) {
        try {
            await axios.delete(`api/contacts/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getContacts(ownerId, ownerType) {
        try {
            const contacts = await axios.get(`api/${ownerType}/${ownerId}/contacts`)
            return contacts.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createContact, storeContact, deleteContact, getContacts }
})

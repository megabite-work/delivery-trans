import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useClientsStore = defineStore('clients', () => {

    const paginator = {
        currentPage: 1,
        from: 0,
        to: 0,
        total: 0,
        lastPage: 0,
        perPage: 15,
    }

    const dataList = ref([])

    async function refreshDataList() {
        try {
            const res = await axios.get('api/clients')
            dataList.value = res.data.data
        } catch (e) {
            dataList.value = []
        }
    }

    async function getClient(clientId) {
        try {
            const client = await axios.get(`api/clients/${clientId}`)
            return client.data
        } catch (e) {
            throw e
        }
    }

    async function createClient(client) {
        try {
            const res = await axios.post('api/clients', client)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function storeClient(client) {
        try {
            const res = await axios.put(`api/clients/${client.id}`, client)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function deleteClient(id) {
        try {
            await axios.delete(`api/clients/${id}`)
        } catch (e) {
            throw e
        }
    }

    async function createClientContact(clientContact) {
        try {
            const res = await axios.post('api/contacts', clientContact)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function storeClientContact(clientContact) {
        try {
            const res = await axios.put(`api/contacts/${clientContact.id}`, clientContact)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function deleteClientContact(id) {
        try {
            await axios.delete(`api/contacts/${id}`)
        } catch (e) {
            throw e
        }
    }

    async function getClientContacts(clientId) {
        try {
            const clientContacts = await axios.get(`api/clients/${clientId}/contacts`)
            return clientContacts.data
        } catch (e) {
            throw e
        }
    }

    async function createClientBankAccount(clientBankAccount) {
        try {
            const res = await axios.post('api/bank-accounts', clientBankAccount)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function storeClientBankAccount(clientBankAccount) {
        try {
            const res = await axios.put(`api/bank-accounts/${clientBankAccount.id}`, clientBankAccount)
            return res.data
        } catch (e) {
            throw e
        }
    }

    async function deleteClientBankAccount(id) {
        try {
            await axios.delete(`api/bank-accounts/${id}`)
        } catch (e) {
            throw e
        }
    }

    async function getClientBankAccounts(clientId) {
        try {
            const clientBankAccounts = await axios.get(`api/clients/${clientId}/bank-accounts`)
            return clientBankAccounts.data
        } catch (e) {
            throw e
        }
    }

    return {
        paginator, dataList,
        refreshDataList,
        getClient, createClient, storeClient, deleteClient,
        createClientContact, storeClientContact, deleteClientContact, getClientContacts,
        createClientBankAccount, storeClientBankAccount, deleteClientBankAccount, getClientBankAccounts,
    }
})

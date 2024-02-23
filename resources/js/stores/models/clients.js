import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useClientsStore = defineStore('clients', () => {
    const clientErr = ref(null)
    const clientAccountErr = ref(null)
    const clientContactErr = ref(null)
    const listLoading = ref(false)

    const paginator = ref({
        pageSize: localStorage.getItem('clientsTable__clientsPerPage') ?? 15,
        current: 1,
        total: 0,
    })

    async function setPage(page) {
        paginator.value.current = page
        await refreshDataList()
    }

    async function setPageSize(page, pageSize) {
        paginator.value.current = page
        paginator.value.pageSize = pageSize
        localStorage.setItem('clientsTable__clientsPerPage', String(pageSize))
        await refreshDataList()
    }

    const dataList = ref([])

    function flushE() {
        err.value = null
    }

    async function refreshDataList() {
        try {
            listLoading.value = true
            const res = await axios.get('api/clients', {
                params: { page: paginator.value.current, per_page: paginator.value.pageSize }
            })
            dataList.value = res.data.data
            paginator.value.pageSize = res.data.meta.per_page
            paginator.value.current = res.data.meta.current_page
            paginator.value.total = res.data.meta.total
        } catch (e) {
            paginator.value = {
                current: 1,
                total: 0,
            }
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getClient(clientId) {
        try {
            const client = await axios.get(`api/clients/${clientId}`)
            return client.data
        } catch (e) {
            if (e.response) {
                clientErr.value = e.response.data
            }
            throw e
        }
    }

    async function createClient(client) {
        try {
            const res = await axios.post('api/clients', client)
            return res.data
        } catch (e) {
            if (e.response) {
                clientErr.value = e.response.data
            }
            throw e
        }
    }

    async function storeClient(client) {
        try {
            const res = await axios.put(`api/clients/${client.id}`, client)
            return res.data
        } catch (e) {
            if (e.response) {
                clientErr.value = e.response.data
            }
            throw e
        }
    }

    async function deleteClient(id) {
        try {
            await axios.delete(`api/clients/${id}`)
        } catch (e) {
            if (e.response) {
                clientErr.value = e.response.data
            }
            throw e
        }
    }

    async function createClientContact(clientContact) {
        try {
            const res = await axios.post('api/contacts', clientContact)
            return res.data
        } catch (e) {
            if (e.response) {
                clientContactErr.value = e.response.data
            }
            throw e
        }
    }

    async function storeClientContact(clientContact) {
        try {
            const res = await axios.put(`api/contacts/${clientContact.id}`, clientContact)
            return res.data
        } catch (e) {
            if (e.response) {
                clientContactErr.value = e.response.data
            }
            throw e
        }
    }

    async function deleteClientContact(id) {
        try {
            await axios.delete(`api/contacts/${id}`)
        } catch (e) {
            if (e.response) {
                clientContactErr.value = e.response.data
            }
            throw e
        }
    }

    async function getClientContacts(clientId) {
        try {
            const clientContacts = await axios.get(`api/clients/${clientId}/contacts`)
            return clientContacts.data
        } catch (e) {
            if (e.response) {
                clientContactErr.value = e.response.data
            }
            throw e
        }
    }

    async function createClientBankAccount(clientBankAccount) {
        try {
            const res = await axios.post('api/bank-accounts', clientBankAccount)
            return res.data
        } catch (e) {
            if (e.response) {
                clientAccountErr.value = e.response.data
            }
            throw e
        }
    }

    async function storeClientBankAccount(clientBankAccount) {
        try {
            const res = await axios.put(`api/bank-accounts/${clientBankAccount.id}`, clientBankAccount)
            return res.data
        } catch (e) {
            if (e.response) {
                clientAccountErr.value = e.response.data
            }
            throw e
        }
    }

    async function deleteClientBankAccount(id) {
        try {
            await axios.delete(`api/bank-accounts/${id}`)
        } catch (e) {
            if (e.response) {
                clientAccountErr.value = e.response.data
            }
            throw e
        }
    }

    async function getClientBankAccounts(clientId) {
        try {
            const clientBankAccounts = await axios.get(`api/clients/${clientId}/bank-accounts`)
            return clientBankAccounts.data
        } catch (e) {
            if (e.response) {
                clientAccountErr.value = e.response.data
            }
            throw e
        }
    }

    return {
        clientErr, clientContactErr, clientAccountErr, paginator, dataList, setPage, setPageSize, listLoading,
        flushE, refreshDataList,
        getClient, createClient, storeClient, deleteClient,
        createClientContact, storeClientContact, deleteClientContact, getClientContacts,
        createClientBankAccount, storeClientBankAccount, deleteClientBankAccount, getClientBankAccounts,
    }
})

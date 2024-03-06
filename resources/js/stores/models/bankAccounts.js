import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from "axios";

export const useBankAccountsStore = defineStore('bank-accounts', () => {
    const err = ref(null)

    async function createBankAccount(bankAccount) {
        try {
            const res = await axios.post('api/bank-accounts', bankAccount)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeBankAccount(bankAccount) {
        try {
            const res = await axios.put(`api/bank-accounts/${bankAccount.id}`, bankAccount)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function deleteBankAccount(id) {
        try {
            await axios.delete(`api/bank-accounts/${id}`)
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function getBankAccounts(ownerId, ownerType) {
        try {
            const bankAccounts = await axios.get(`api/${ownerType}/${ownerId}/bank-accounts`)
            return bankAccounts.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return { err, createBankAccount, storeBankAccount, deleteBankAccount, getBankAccounts }
})

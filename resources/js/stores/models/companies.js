import {defineStore} from "pinia";
import {ref} from "vue";
import axios from "axios";

export const useCompaniesStore = defineStore('companies', () => {
    const err = ref(null)
    const listLoading = ref(false)

    const dataList = ref([])

    async function refreshDataList(){
        try {
            listLoading.value = true
            const { data } = await axios.get('api/companies')
            dataList.value = data
        } catch (e) {
            dataList.value = []
        } finally {
            listLoading.value = false
        }
    }

    async function getCompany(companyId) {
        try {
            const { data } = await axios.get(`api/companies/${companyId}`)
            return data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    async function storeCompany(company) {
        try {
            const res = await axios.put(`api/companies/${company.id}`, company)
            return res.data
        } catch (e) {
            if (e.response) {
                err.value = e.response.data
            }
            throw e
        }
    }

    return {
        dataList, err, listLoading,
        refreshDataList, getCompany, storeCompany
    }
})

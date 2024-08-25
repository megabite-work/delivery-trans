<script setup>
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import Drawer from "../components/Drawer.vue";
import Layout from "../layouts/AppLayout.vue";
import {message} from "ant-design-vue";
import {useAuthStore} from "../stores/auth.js";
import { BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import {useCompaniesStore} from "../stores/models/companies.js";
import Company from "../components/models/Company.vue";


const columnsCompanies = [
    { key: '__icon', width: 50 },
    { title: 'Наименование', dataIndex: 'name_short' },
    { title: 'НДС', key: 'vat' },
    { title: 'ИНН', dataIndex: 'inn' },
    { title: 'КПП', dataIndex: 'kpp' },
];

const vatArr = ['Без НДС', 'НДС', 'Нал'];

const companiesStore = useCompaniesStore()
const authStore = useAuthStore()
const currentCompany = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (companyId) => {
    currentCompany.data = { id: null }
    currentCompany.modified = false
    mainDrawer.isOpen = true
    try {
        mainDrawer.isLoading = true
        currentCompany.data = await companiesStore.getCompany(companyId)
    } catch (e) {
        mainDrawer.isOpen = false
        message.error('Ошибка загрузки')
    } finally {
        mainDrawer.isLoading = false
    }
}

const closeMainDrawer = () => {
    if (mainDrawer.isSaving) {
        return
    }
    mainDrawer.isOpen = false
    currentCompany.data = { id: null }
}

const saveCompany = async () => {
    mainDrawer.isSaving = true
    try {
        if(authStore.userCan('COMPANIES_DIR')) {
            currentCompany.data = await companiesStore.storeCompany(currentCompany.data)
            currentCompany.modified = false
            message.success('Данные записаны')
        }
        mainDrawer.isSaving = false
        closeMainDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось сохранить данные компании`)
    } finally {
        mainDrawer.isSaving = false
        await companiesStore.refreshDataList()
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })
onMounted(() => {
    companiesStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})

</script>

<template>
    <Layout title="Компании">
        <a-table
            :loading="companiesStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsCompanies"
            :data-source="companiesStore.dataList"
            :pagination="false"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === '__icon'">
                    <BuildingOfficeIcon />
                </template>
                <template v-if="column.key === 'vat'">
                    {{ vatArr[record.vat] }}
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveCompany"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`Компания ${currentCompany.data.vat === 0 ? 'без' : 'с'} НДС`"
            ok-text="Сохранить и закрыть"
            :need-delete="false"
        >
            <Company
                v-model="currentCompany.data"
                :loading="mainDrawer.isLoading"
                :errors="companiesStore.err?.errors"
            />
        </drawer>
    </Layout>
</template>

<style scoped>

</style>

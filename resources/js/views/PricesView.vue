<script setup>
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";

import Layout from '../layouts/AppLayout.vue';
import Drawer from "../components/Drawer.vue";
import Price from "../components/Price.vue";
import AdditionalServicesEditorTable from "../components/AdditionalServicesEditorTable.vue";

import {usePricesStore} from "../stores/models/prices.js";
import {UnorderedListOutlined} from "@ant-design/icons-vue";
import {message} from "ant-design-vue";

const pricesStore = usePricesStore()

const columnsPrices = [
    { key: 'icon', width: 50 },
    { title: 'Наименование', dataIndex: 'name' },
    { title: 'По умолчанию', dataIndex: 'is_default', key: 'is_default' },
];

const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
const currentPrice = reactive({ data:{ id: null }, modified: false })
const priceLoading = ref(false)

const openMainDrawer = async (priceId = null) => {
    currentPrice.data = { id: null }
    currentPrice.modified = false
    mainDrawer.isOpen = true
    if (priceId !== null) {
        try {
            mainDrawer.isLoading = true
            currentPrice.data = await pricesStore.getDefaultPriceById(priceId)
        } catch (e) {
            mainDrawer.isOpen = false
            message.error('Ошибка загрузки')
        } finally {
            mainDrawer.isLoading = false
        }
    }
}

const reloadDefaultPrice = async () => {
    try {
        priceLoading.value = true
        const dp = await pricesStore.getDefaultPriceById(currentPrice.data.id)
        currentPrice.data = { ...currentPrice.data, prices: dp.prices }
    } catch {
        message.error('Ошибка получения прайс-листа')
    } finally {
        priceLoading.value = false
    }
}
const saveDefaultPrice = async () => {
    try {
        mainDrawer.isSaving = true
        if (currentPrice.data.id === null) {
            currentPrice.data = await pricesStore.createDefaultPrice(currentPrice.data)
            currentPrice.modified = false
            message.info('Прайс-лист создан')
        } else {
            currentPrice.data = await pricesStore.storeDefaultPrice(currentPrice.data)
            currentPrice.modified = false
            message.info('Прайс-лист записан')
            mainDrawer.isOpen = false
        }
    } catch {
        message.error('Ошибка сохранения прайс-листа')
    } finally {
        mainDrawer.isSaving = false
        await pricesStore.refreshDataList()
    }
}
const deleteDefaultPrice = async () => {
    try {
        mainDrawer.isSaving = true
        await pricesStore.deleteDefaultPrice(currentPrice.data.id)
        mainDrawer.isOpen = false
    } catch {
        message.error('При удалении прайс-листа возникла ошибка')
    } finally {
        mainDrawer.isSaving = false
        await pricesStore.refreshDataList()
    }
}

const addAdditionalService = async e => {
    const res = await pricesStore.createAdditionalServiceDefault(currentPrice.data.id, e)
    if (currentPrice.data.additional_services[0]) {
        currentPrice.data.additional_services[0] = res
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })
onMounted(() => {
    pricesStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})
</script>

<template>
    <Layout title="Прайс-листы">
        <template #headerExtra><a-button type="primary" @click="() => openMainDrawer(null)">Новый прайс-лист</a-button></template>
        <a-table
            :loading="pricesStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsPrices"
            :data-source="pricesStore.dataList"
            :pagination="false"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'is_default'">
                    <div v-if="record.is_default">Да</div>
                </template>
                <template v-if="column.key === 'icon'">
                    <UnorderedListOutlined />
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveDefaultPrice"
            @delete="deleteDefaultPrice"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentPrice.data.id === null ? 'Новый прайс-лист' : `Прайс #${currentPrice.data.id}`}${currentPrice.modified ? '*' : ''}`"
            :ok-text="currentPrice.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-delete="currentPrice.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Прайс будет удален!"
            delete-text="Удалить"
        >
            <a-form layout="vertical" :model="currentPrice.data">
                <a-form-item label="Наименование" name="name">
                    <a-input
                        v-model:value="currentPrice.data.name"
                        placeholder="Наименование прайс-листа"
                    />
                </a-form-item>
                <a-form-item label="Прайс-лист по умолчанию" name="is_default">
                    <a-switch v-model:checked="currentPrice.data.is_default" />
                </a-form-item>
            </a-form>
            <template v-if="currentPrice.data.id !== null">
                <Price
                    :prices="currentPrice.data.prices"
                    @price-change="reloadDefaultPrice"
                    :owner-id="currentPrice.data.id"
                    :loading="priceLoading"
                    :is-default-price="true"
                />
                <a-divider orientation="left">Цены на допуслуги</a-divider>
                <AdditionalServicesEditorTable
                    v-model="currentPrice.data.additional_services"
                    @edit="e => pricesStore.storeAdditionalService(e)"
                    @add="e => addAdditionalService(e)"
                    @delete="id => pricesStore.deleteAdditionalService(id)"
                />
            </template>
            <a-alert v-if="currentPrice.data.id === null && !mainDrawer.isLoading"
                     description="Редактирование цен доступно после сохранения нового прайса."
                     type="info"
            />
        </drawer>
    </Layout>

</template>

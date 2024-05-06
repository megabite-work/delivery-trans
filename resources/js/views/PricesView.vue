<script setup>
import Layout from '../layouts/AppLayout.vue';
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {usePricesStore} from "../stores/models/prices.js";
import { UnorderedListOutlined } from "@ant-design/icons-vue";
import {message} from "ant-design-vue";
import Drawer from "../components/Drawer.vue";
import Price from "../components/Price.vue";

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

const saveDefaultPrice = () => {}
const deleteDefaultPrice = () => {}

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
        <template #headerExtra><a-button type="primary" @click="() => {}">Новый прайс-лист</a-button></template>
        <a-table
            :loading="pricesStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsPrices"
            :data-source="pricesStore.dataList"
            :pagination="{
                ...pricesStore.paginator,
                showSizeChanger: true,
                pageSizeOptions: ['15', '30', '50', '100'],
                style: {marginRight: '10px'},
                buildOptionText: size => `${size.value} / стр.`,
                onChange: page => pricesStore.setPage(page),
                onShowSizeChange: (page, size) => pricesStore.setPageSize(page, size)
            }"
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
            <Price
                :prices="currentPrice.data.prices"
                @price-change="() => {}"
                :owner-id="currentPrice.data.id"
                :loading="priceLoading"
            />
        </drawer>
    </Layout>

</template>

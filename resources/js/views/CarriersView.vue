<script setup>
import Layout from '../layouts/AppLayout.vue';
import {useCarriersStore} from "../stores/models/carriers.js";
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import Drawer from "../components/Drawer.vue";
import {message} from "ant-design-vue";
import Carrier from "../components/models/Carrier.vue";

const columnsCarriers = [
    { key: 'type', width: 50 },
    { title: 'ИНН', dataIndex: 'inn', width: 150 },
    { title: 'Наименование', dataIndex: 'name_short', width: '100%' },
];

const carriersStore = useCarriersStore()
const currentCarrier = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (carrierId = null) => {
    currentCarrier.data = { id: null }
    currentCarrier.modified = false
    mainDrawer.isOpen = true
    if (carrierId !== null) {
        try {
            mainDrawer.isLoading = true
            currentCarrier.data = await carriersStore.getCarrier(carrierId)
        } catch (e) {
            mainDrawer.isOpen = false
            message.error('Ошибка загрузки')
        } finally {
            mainDrawer.isLoading = false
        }
    }
}

const closeMainDrawer = () => {
    if (mainDrawer.isSaving) {
        return
    }
    mainDrawer.isOpen = false
    currentCarrier.data = { id: null }
}

const saveCarrier = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentCarrier.data.id === null) {
            currentCarrier.data  = await carriersStore.createCarrier(currentCarrier.data)
            currentCarrier.modified = false
            message.success('Карточка перевозчика создана')
            return
        }
        currentCarrier.data = await carriersStore.storeCarrier(currentCarrier.data)
        currentCarrier.modified = false
        message.success('Карточка перевозчика записана')
        mainDrawer.isSaving = false
        closeMainDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentCarrier.data.id === null ? 'создать' : 'сохранить'} карточку перевозчика`)
    } finally {
        mainDrawer.isSaving = false
        await carriersStore.refreshDataList()
    }
}

const deleteCarrier = async () => {
    if (currentCarrier.data.id === null) {
        return
    }
    try {
        await carriersStore.deleteCarrier(currentCarrier.data.id)
        message.success('Перевозчик успешно удален')
        await carriersStore.refreshDataList()
        closeMainDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить перевозчика')
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })
onMounted(() => {
    carriersStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})
</script>

<template>
    <Layout title="Перевозчики">
        <template #headerExtra><a-button type="primary" @click="() => openMainDrawer()">Новый перевозчик</a-button></template>
        <a-table
            :loading="carriersStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsCarriers"
            :data-source="carriersStore.dataList"
            :pagination="{
                ...carriersStore.paginator,
                showSizeChanger: true,
                pageSizeOptions: ['15', '30', '50', '100'],
                style: {marginRight: '10px'},
                buildOptionText: size => `${size.value} / стр.`,
                onChange: page => carriersStore.setPage(page),
                onShowSizeChange: (page, size) => carriersStore.setPageSize(page, size)
            }"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'type'">
                    <div :style="{display: 'flex'}">
                        <a-tooltip placement="right">
                            <template v-if="record.type === 'INDIVIDUAL'" #title>Индивидуальный предприниматель</template>
                            <template v-if="record.type === 'LEGAL'" #title>Юридическое лицо</template>
                            <UserIcon v-if="record.type === 'INDIVIDUAL'" :style="{ position: 'relative' }" />
                            <BuildingOfficeIcon v-else-if="record.type === 'LEGAL'" :style="{ position: 'relative' }" />
                        </a-tooltip>
                    </div>
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveCarrier"
            @delete="deleteCarrier"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentCarrier.data.id === null ? 'Новый перевозчик' : `Перевозчик #${currentCarrier.data.id}`}${currentCarrier.modified ? '*' : ''}`"
            :ok-text="currentCarrier.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-delete="currentCarrier.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Перевозчик будет удален!"
            delete-text="Удалить"
        >
            <Carrier v-model="currentCarrier.data" :loading="mainDrawer.isLoading" :errors="carriersStore.carrierErr?.errors"/>
        </drawer>
    </Layout>
</template>
